<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminLoginHistory;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Exporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Who signed in to the admin panel, from where, on what, and for how long.
 */
class AdminLoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = $this->filtered($request)
            ->latest('logged_in_at')
            ->paginate($this->perPage($request, 25, [25, 50, 100]))
            ->withQueryString();

        return view('backend.pages.admin-login-history.index', [
            'histories' => $histories,
            'admins'    => User::admins()->orderBy('name')->get(['id', 'name']),
            'roles'     => AdminLoginHistory::query()
                ->whereNotNull('user_role')
                ->distinct()
                ->orderBy('user_role')
                ->pluck('user_role')
                ->filter(fn ($r) => $r !== '—')
                ->values(),
            'stats'     => [
                'today'   => AdminLoginHistory::whereDate('logged_in_at', today())->count(),
                'success' => AdminLoginHistory::where('successful', true)->count(),
                'failed'  => AdminLoginHistory::where('successful', false)->count(),
                'online'  => AdminLoginHistory::where('successful', true)
                    ->whereNull('logged_out_at')
                    ->whereDate('logged_in_at', today())
                    ->count(),
            ],
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $count = AdminLoginHistory::whereIn('id', $data['ids'])->delete();

        AuditLogger::system(
            AuditLogger::ACTION_DELETED,
            'System',
            "Deleted {$count} admin login history entries",
        );

        return back()->with('success', "{$count} entries deleted.");
    }

    public function export(Request $request)
    {
        $rows = $this->filtered($request)
            ->latest('logged_in_at')
            ->lazy(500)
            ->map(fn (AdminLoginHistory $h) => [
                $h->id,
                $h->user_name ?: '—',
                $h->user_email ?: '—',
                $h->user_role ?: '—',
                $h->statusLabel(),
                $h->failure_reason ?: '—',
                $h->logged_in_at?->format('d M Y'),
                $h->logged_in_at?->format('h:i:s A'),
                $h->logged_out_at?->format('d M Y h:i:s A') ?: '—',
                $h->duration() ?: ($h->isOnline() ? 'Still signed in' : '—'),
                $h->durationSeconds() ?? '—',
                $h->ip_address ?: '—',
                $h->location(),
                $h->browser ?: '—',
                $h->device ?: '—',
                $h->platform ?: '—',
                $h->session_id ?: '—',
                $h->logout_type ?: '—',
            ]);

        AuditLogger::system(AuditLogger::ACTION_EXPORTED, 'System', 'Exported the admin login history');

        return Exporter::make(
            $request->query('format', 'xlsx'),
            'admin_login_history',
            'Admin Login History',
            [
                'ID', 'Admin', 'Email', 'Role', 'Status', 'Failure Reason',
                'Login Date', 'Login Time', 'Logout', 'Session Duration', 'Duration (seconds)',
                'IP Address', 'Location', 'Browser', 'Device', 'Operating System',
                'Session ID', 'Logout Type',
            ],
            $rows,
        );
    }

    private function filtered(Request $request): Builder
    {
        return AdminLoginHistory::query()
            ->search($request->query('q'))
            ->outcome($request->query('outcome'))
            ->forUser($request->query('user') ? (int) $request->query('user') : null)
            ->ofRole($request->query('role'))
            ->betweenDates($request->query('from'), $request->query('to'));
    }
}
