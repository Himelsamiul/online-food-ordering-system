<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Support\AuditLogger;
use App\Support\Exporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Customer sign-in history.
 *
 * Moved off the frontend LoginController so the admin-facing list, its filters
 * and its export live with the rest of the backend.
 */
class CustomerLoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $histories = $this->filtered($request)
            ->with('registration')
            ->latest('logged_in_at')
            ->paginate((int) $request->query('per_page', 20))
            ->withQueryString();

        return view('backend.pages.loginhistory', [
            'histories' => $histories,
            'countries' => LoginHistory::query()
                ->whereNotNull('country')
                ->distinct()
                ->orderBy('country')
                ->pluck('country'),
            'stats'     => [
                'today'  => LoginHistory::whereDate('logged_in_at', today())->count(),
                'total'  => LoginHistory::count(),
                'online' => LoginHistory::whereNull('logged_out_at')
                    ->whereDate('logged_in_at', today())
                    ->count(),
                'unique' => LoginHistory::distinct('registration_id')->count('registration_id'),
            ],
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $count = LoginHistory::whereIn('id', $data['ids'])->delete();

        AuditLogger::system(
            AuditLogger::ACTION_DELETED,
            'System',
            "Deleted {$count} customer login history entries",
        );

        return back()->with('success', "{$count} entries deleted.");
    }

    public function export(Request $request)
    {
        $rows = $this->filtered($request)
            ->with('registration')
            ->latest('logged_in_at')
            ->lazy(500)
            ->map(fn (LoginHistory $h) => [
                $h->id,
                $h->registration?->full_name ?? 'Deleted customer',
                $h->registration?->username ?? '—',
                $h->registration?->email ?? '—',
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
            ]);

        AuditLogger::system(AuditLogger::ACTION_EXPORTED, 'System', 'Exported the customer login history');

        return Exporter::make(
            $request->query('format', 'xlsx'),
            'customer_login_history',
            'Customer Login History',
            [
                'ID', 'Customer', 'Username', 'Email', 'Login Date', 'Login Time',
                'Logout', 'Session Duration', 'Duration (seconds)', 'IP Address',
                'Location', 'Browser', 'Device', 'Operating System', 'Session ID',
            ],
            $rows,
        );
    }

    /**
     * The query keys are the ones the existing filter form already posts —
     * name, country, date — kept so saved links and bookmarks keep working.
     */
    private function filtered(Request $request): Builder
    {
        $query = LoginHistory::query();

        if ($name = $request->query('name')) {
            $query->whereHas('registration', function (Builder $q) use ($name) {
                $q->where('full_name', 'like', "%{$name}%")
                    ->orWhere('username', 'like', "%{$name}%")
                    ->orWhere('email', 'like', "%{$name}%");
            });
        }

        if ($country = $request->query('country')) {
            $query->where('country', 'like', "%{$country}%");
        }

        if ($date = $request->query('date')) {
            $query->whereDate('logged_in_at', $date);
        }

        return $query->betweenDates($request->query('from'), $request->query('to'));
    }
}
