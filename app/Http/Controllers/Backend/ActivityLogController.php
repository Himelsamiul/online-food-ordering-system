<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Exporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The audit trail: who did what, when, from where.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->filtered($request)
            ->latest('id')
            ->paginate($this->perPage($request, 25, [25, 50, 100]))
            ->withQueryString();

        return view('backend.pages.activity-log.index', [
            'logs'         => $logs,
            'admins'       => User::admins()->orderBy('name')->get(['id', 'name']),
            'subjectTypes' => ActivityLog::subjectTypes(),
            'modules'      => ActivityLog::moduleOptions(),
            'events'       => ActivityLog::events(),
            'eventGroups'  => ActivityLog::EVENT_GROUPS,
            'stats'        => [
                'today'   => ActivityLog::whereDate('created_at', today())->count(),
                'created' => ActivityLog::where('event', 'created')->count(),
                'updated' => ActivityLog::where('event', 'updated')->count(),
                'deleted' => ActivityLog::whereIn('event', ['deleted', 'force_deleted'])->count(),
            ],
        ]);
    }

    /** Full detail for one entry, rendered into the drawer on the index page. */
    public function show(ActivityLog $activityLog)
    {
        return response()->json([
            'id'          => $activityLog->id,
            'event'       => $activityLog->eventLabel(),
            'module'      => $activityLog->moduleLabel(),
            'description' => $activityLog->description,
            'user'        => $activityLog->user_name,
            'role'        => $activityLog->user_role,
            'userType'    => $activityLog->user_type,
            'subject'     => trim($activityLog->subjectName() . ' ' . ($activityLog->subject_label ?? '')),
            'recordId'    => $activityLog->subject_id,
            'oldValues'   => $activityLog->old_values,
            'newValues'   => $activityLog->new_values,
            'ip'          => $activityLog->ip_address,
            'browser'     => $activityLog->browser,
            'device'      => $activityLog->device,
            'platform'    => $activityLog->platform,
            'sessionId'   => $activityLog->session_id,
            'url'         => $activityLog->url,
            'method'      => $activityLog->method,
            'date'        => $activityLog->created_at?->format('d M Y'),
            'time'        => $activityLog->created_at?->format('h:i:s A'),
            'timestamp'   => $activityLog->created_at?->toIso8601String(),
        ]);
    }

    public function destroy(ActivityLog $activityLog)
    {
        $activityLog->delete();

        return back()->with('success', 'Log entry deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $count = ActivityLog::whereIn('id', $data['ids'])->delete();

        // Deleting audit rows is itself worth auditing.
        AuditLogger::system(AuditLogger::ACTION_DELETED, 'System', "Deleted {$count} audit trail entries");

        return back()->with('success', "{$count} log entries deleted.");
    }

    /** The trail grows without bound; this is how it gets trimmed. */
    public function prune(Request $request)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $count = ActivityLog::where('created_at', '<', now()->subDays($data['days']))->delete();

        AuditLogger::system(
            AuditLogger::ACTION_DELETED,
            'System',
            "Pruned {$count} audit trail entries older than {$data['days']} days",
        );

        return back()->with('success', "{$count} entries older than {$data['days']} days removed.");
    }

    public function export(Request $request)
    {
        // lazy() so a trail with a million rows never has to fit in memory.
        $rows = $this->filtered($request)
            ->latest('id')
            ->lazy(500)
            ->map(fn (ActivityLog $log) => [
                $log->id,
                $log->user_name ?: 'System',
                $log->user_id ?: '—',
                $log->user_role ?: '—',
                $log->user_type ?: '—',
                $log->moduleLabel(),
                $log->eventLabel(),
                $log->subject_id ?: '—',
                $log->subject_label ?: '—',
                $log->description,
                $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_SLASHES) : '—',
                $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_SLASHES) : '—',
                $log->ip_address ?: '—',
                $log->browser ?: '—',
                $log->device ?: '—',
                $log->platform ?: '—',
                $log->session_id ?: '—',
                $log->method ?: '—',
                $log->url ?: '—',
                $log->created_at?->format('d M Y'),
                $log->created_at?->format('h:i:s A'),
                $log->created_at?->toIso8601String(),
            ]);

        AuditLogger::system(AuditLogger::ACTION_EXPORTED, 'System', 'Exported the audit trail');

        return Exporter::make(
            $request->query('format', 'xlsx'),
            'audit_trail',
            'Audit Trail',
            [
                'Log ID', 'User Name', 'User ID', 'Role', 'User Type', 'Module', 'Action Type',
                'Record ID', 'Record', 'Description', 'Old Values', 'New Values',
                'IP Address', 'Browser', 'Device', 'Operating System', 'Session ID',
                'Method', 'URL', 'Date', 'Time', 'Timestamp',
            ],
            $rows,
        );
    }

    /** One filter pipeline, so the export always matches what is on screen. */
    private function filtered(Request $request): Builder
    {
        return ActivityLog::query()
            ->event($request->query('event'))
            ->module($request->query('module'))
            ->byUser($request->query('user') ? (int) $request->query('user') : null)
            ->userType($request->query('user_type'))
            ->subjectType($request->query('subject'))
            ->search($request->query('q'))
            ->betweenDates($request->query('from'), $request->query('to'));
    }
}
