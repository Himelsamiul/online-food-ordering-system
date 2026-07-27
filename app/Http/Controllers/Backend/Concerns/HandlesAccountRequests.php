<?php

namespace App\Http\Controllers\Backend\Concerns;

use App\Models\AccountRequest;
use App\Services\AccountRequestService;
use App\Support\AuditLogger;
use App\Support\Exporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The behaviour every request inbox shares.
 *
 * The four dashboards (admin/customer × password/activation) differ only in
 * which slice of account_requests they show, which permission guards them and
 * which view renders them. Everything else — filtering, approving, rejecting,
 * deleting, exporting, auditing — is identical and lives here once.
 *
 * A class using this must implement:
 *   baseQuery(): Builder   the slice of requests it owns
 *   viewName(): string     the blade to render
 *   routePrefix(): string  the admin.* route name prefix, e.g. "admin.account-requests"
 *   exportLabel(): string  the filename / sheet title
 */
trait HandlesAccountRequests
{
    abstract protected function baseQuery(): Builder;

    abstract protected function viewName(): string;

    abstract protected function routePrefix(): string;

    abstract protected function exportLabel(): string;

    public function index(Request $request)
    {
        $requests = $this->filtered($request)
            ->with(['registration', 'adminUser', 'handler'])
            ->latest()
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        $counts = [
            'pending'  => (clone $this->baseQuery())->where('status', AccountRequest::STATUS_PENDING)->count(),
            'resolved' => (clone $this->baseQuery())->where('status', AccountRequest::STATUS_RESOLVED)->count(),
            'rejected' => (clone $this->baseQuery())->where('status', AccountRequest::STATUS_REJECTED)->count(),
            'total'    => (clone $this->baseQuery())->count(),
        ];

        // Opening the inbox counts as having seen whatever is in it.
        (clone $this->baseQuery())->whereNull('read_at')->update(['read_at' => now()]);

        return view($this->viewName(), [
            'requests'    => $requests,
            'counts'      => $counts,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    /** Approve: issue a reset link, or switch the account back on. */
    public function approve(Request $request, AccountRequest $accountRequest)
    {
        $this->assertOwned($accountRequest);

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$accountRequest->isPending()) {
            return back()->with('error', 'That request has already been handled.');
        }

        /** @var AccountRequestService $service */
        $service = app(AccountRequestService::class);

        $result = $accountRequest->isPasswordReset()
            ? $service->approvePasswordReset($accountRequest, $data['admin_note'] ?? null, $request->user())
            : $service->approveActivation($accountRequest, $data['admin_note'] ?? null, $request->user());

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function reject(Request $request, AccountRequest $accountRequest)
    {
        $this->assertOwned($accountRequest);

        $data = $request->validate([
            'admin_note' => ['required', 'string', 'min:5', 'max:1000'],
        ], [], ['admin_note' => 'reason']);

        if (!$accountRequest->isPending()) {
            return back()->with('error', 'That request has already been handled.');
        }

        $result = app(AccountRequestService::class)
            ->reject($accountRequest, $data['admin_note'], $request->user());

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function destroy(AccountRequest $accountRequest)
    {
        $this->assertOwned($accountRequest);

        $email = $accountRequest->email;
        $accountRequest->delete();

        AuditLogger::log(
            AuditLogger::ACTION_DELETED,
            'Deleted the account request from ' . $email,
            null,
            null,
            null,
            'Account Requests',
        );

        return back()->with('success', 'Request deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        // Scoped to this inbox so one dashboard cannot delete another's rows.
        $count = (clone $this->baseQuery())->whereIn('id', $data['ids'])->delete();

        AuditLogger::log(
            AuditLogger::ACTION_DELETED,
            "Deleted {$count} account request(s)",
            null,
            null,
            null,
            'Account Requests',
        );

        return back()->with('success', "{$count} request(s) deleted.");
    }

    /** Excel by default; CSV and a print-ready page on request. */
    public function export(Request $request)
    {
        $rows = $this->filtered($request)
            ->with(['handler'])
            ->latest()
            ->lazy(500)
            ->map(fn (AccountRequest $r, $i) => [
                $r->id,
                $r->requesterLabel(),
                $r->typeLabel(),
                $r->name,
                $r->username ?: '—',
                $r->email,
                $r->phone ?: '—',
                $r->requested_role ?: '—',
                $r->reason ?: '—',
                $r->message ?: '—',
                $r->statusLabel(),
                $r->handled_by_name ?: '—',
                $r->handled_at?->format('d M Y h:i A') ?: '—',
                $r->admin_note ?: '—',
                $r->ip_address ?: '—',
                $r->clientSummary(),
                $r->created_at->format('d M Y'),
                $r->created_at->format('h:i A'),
            ]);

        AuditLogger::system(
            AuditLogger::ACTION_EXPORTED,
            'Account Requests',
            'Exported ' . $this->exportLabel(),
        );

        return Exporter::make(
            $request->query('format', 'xlsx'),
            str_replace(' ', '_', strtolower($this->exportLabel())),
            $this->exportLabel(),
            [
                'ID', 'Requester', 'Type', 'Full Name', 'Username', 'Email', 'Phone', 'Role',
                'Reason', 'Notes', 'Status', 'Handled By', 'Handled At', 'Admin Note',
                'IP Address', 'Client', 'Date', 'Time',
            ],
            $rows,
            ['Generated' => now()->format('d M Y h:i A'), 'Rows' => 'see below'],
        );
    }

    /** Shared filter pipeline, so the export always matches what is on screen. */
    protected function filtered(Request $request): Builder
    {
        return $this->baseQuery()
            ->ofStatus($request->query('status'))
            ->ofType($request->query('type'))
            ->search($request->query('q'))
            ->betweenDates($request->query('from'), $request->query('to'));
    }

    /**
     * A row from another inbox must not be actionable here, even with a valid
     * id — route model binding alone would happily hand it over.
     */
    protected function assertOwned(AccountRequest $accountRequest): void
    {
        abort_unless(
            (clone $this->baseQuery())->whereKey($accountRequest->getKey())->exists(),
            404,
        );
    }
}
