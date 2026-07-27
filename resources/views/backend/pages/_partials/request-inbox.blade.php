@php
    /**
     * The shared body of every account-request inbox.
     *
     * Three dashboards use it — customer requests, admin password resets and
     * admin activations. They differ only in the slice of rows the controller
     * hands over and the copy around them, so the filtering, the cards, the
     * approve/reject controls and the bulk actions live here once.
     *
     * Expects, from the controller: $requests (paginator), $counts, $routePrefix
     * Expects, from the including page:
     *   $showTypeFilter  bool   — only the mixed customer inbox needs it
     *   $approveLabel    string — wording on the approve button
     *   $approveConfirm  string — wording in the confirm dialog
     *   $canManage       bool   — may approve/reject
     *   $canDelete       bool
     */
    $showTypeFilter = $showTypeFilter ?? false;
    $canManage      = $canManage ?? false;
    $canDelete      = $canDelete ?? false;
    $approveLabel   = $approveLabel ?? 'Approve';
    $approveConfirm = $approveConfirm ?? 'This will be actioned and the requester emailed.';

    $status = request('status');
@endphp

<div class="stat-grid">
    <x-stat-card label="Pending" :value="$counts['pending']" icon="feather-clock" tone="warning"
                 foot="waiting for review" />
    <x-stat-card label="Approved" :value="$counts['resolved']" icon="feather-check-circle" tone="success" />
    <x-stat-card label="Rejected" :value="$counts['rejected']" icon="feather-x-circle" tone="danger" />
    <x-stat-card label="Total" :value="$counts['total']" icon="feather-inbox" tone="primary" />
</div>

{{-- Status tabs. Every other filter is preserved as we move between them. --}}
<div class="chip-row mb-3">
    @foreach ([
        ''         => 'All',
        'pending'  => 'Pending',
        'resolved' => 'Approved',
        'rejected' => 'Rejected',
    ] as $value => $label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $value ?: null, 'page' => null]) }}"
           class="chip {{ (string) $status === (string) $value ? 'active' : '' }}">
            {{ $label }}
            @if ($value === 'pending' && $counts['pending'] > 0)
                <span class="chip-count">{{ $counts['pending'] }}</span>
            @endif
        </a>
    @endforeach
</div>

<form method="GET" class="filter-card">
    @if ($status)
        <input type="hidden" name="status" value="{{ $status }}">
    @endif

    <div class="row g-3 align-items-end">
        <div class="col-lg-4 col-md-6">
            <label class="filter-label" for="q">Search</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control"
                   placeholder="Name, username, email or phone…">
        </div>

        @if ($showTypeFilter)
            <div class="col-lg-2 col-md-6">
                <label class="filter-label" for="type">Type</label>
                <select id="type" name="type" class="form-select">
                    <option value="">All types</option>
                    <option value="password_reset" @selected(request('type') === 'password_reset')>Password Reset</option>
                    <option value="activation" @selected(request('type') === 'activation')>Reactivation</option>
                </select>
            </div>
        @endif

        <div class="col-lg-2 col-md-3 col-6">
            <label class="filter-label" for="from">From</label>
            <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control">
        </div>

        <div class="col-lg-2 col-md-3 col-6">
            <label class="filter-label" for="to">To</label>
            <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control">
        </div>

        <div class="col-lg-2 col-md-auto">
            <button class="btn btn-primary w-100"><i class="feather-filter"></i> Filter</button>
        </div>
    </div>
</form>

{{-- The bulk form is empty and sits outside the cards: each row carries its own
     approve/reject/delete form, and nesting forms is invalid HTML. Checkboxes
     join it with the form="" attribute instead. --}}
@if ($canDelete)
    <form id="bulkRequests" method="POST" action="{{ route($routePrefix . '.bulk-delete') }}"
          class="delete-form" data-confirm-title="Delete the selected requests?"
          data-confirm-text="They will be removed from the inbox. This cannot be undone.">
        @csrf
    </form>
@endif

<div class="card">
    <div class="card-header">
        <h5>Requests</h5>

        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if ($canDelete && $requests->count())
                <label class="form-check-label d-flex align-items-center gap-2 mb-0"
                       style="font-size:13px;color:var(--ar-muted)">
                    <input type="checkbox" class="form-check-input mt-0" data-ar-check-all="#requestList">
                    Select page
                </label>
            @endif

            <span class="text-muted" style="font-size:13px">{{ $requests->total() }} total</span>
        </div>
    </div>

    <div class="card-body" id="requestList">
        @forelse ($requests as $req)
            @php
                $account   = $req->account();
                $mismatch  = $req->username && $account && ($account->username ?? null)
                             && $account->username !== $req->username;
            @endphp

            <article class="req-card {{ $req->isPending() ? 'is-pending' : '' }}">

                <header class="req-head">
                    <div class="cell-media">
                        @if ($canDelete)
                            <input type="checkbox" class="form-check-input mt-0" data-ar-check
                                   name="ids[]" value="{{ $req->id }}" form="bulkRequests"
                                   aria-label="Select request {{ $req->id }}">
                        @endif

                        <span class="avatar-initials">{{ Str::of($req->name)->substr(0, 2) }}</span>

                        <div>
                            <p class="cell-title">
                                {{ $req->name }}
                                @if ($req->username)
                                    <span class="text-muted" style="font-weight:500">· {{ $req->username }}</span>
                                    @if ($mismatch)
                                        <span class="badge bg-warning" title="Does not match the username on the account">
                                            <i class="feather-alert-triangle"></i> username mismatch
                                        </span>
                                    @endif
                                @endif
                            </p>
                            <p class="cell-sub">{{ $req->email }}@if ($req->phone) · {{ $req->phone }}@endif</p>
                        </div>
                    </div>

                    <div class="req-badges">
                        <span class="badge bg-{{ $req->typeTone() }}">
                            <i class="{{ $req->typeIcon() }}"></i> {{ $req->typeLabel() }}
                        </span>
                        <span class="badge bg-{{ $req->statusTone() }}">{{ $req->statusLabel() }}</span>
                        @if ($req->requested_role)
                            <span class="chip">{{ Str::headline($req->requested_role) }}</span>
                        @endif
                    </div>
                </header>

                <div class="req-body">
                    @if ($req->reason)
                        <div class="req-quote">
                            <span class="req-quote-label">Reason</span>
                            {{ $req->reason }}
                        </div>
                    @endif

                    @if ($req->message)
                        <div class="req-quote muted">
                            <span class="req-quote-label">Notes</span>
                            {{ $req->message }}
                        </div>
                    @endif

                    <div class="req-meta">
                        <span><i class="feather-clock"></i> {{ $req->created_at->diffForHumans() }}
                            ({{ $req->created_at->format('d M Y, h:i A') }})</span>
                        <span><i class="feather-globe"></i> {{ $req->ip_address ?: 'unknown IP' }}</span>
                        <span><i class="feather-monitor"></i> {{ $req->clientSummary() }}</span>

                        @if ($req->hasAccount())
                            <span class="status-pill on">Account matched</span>
                            @if ($account && !$account->isActive())
                                <span class="status-pill off">Currently deactivated</span>
                            @endif
                        @else
                            <span class="status-pill off">No matching account</span>
                        @endif
                    </div>

                    @if (!$req->isPending())
                        <div class="req-outcome">
                            <i class="feather-{{ $req->status === 'resolved' ? 'check-circle' : 'x-circle' }}"></i>
                            Handled by <strong>{{ $req->handled_by_name ?: 'an administrator' }}</strong>
                            on {{ $req->handled_at?->format('d M Y, h:i A') }}
                            @if ($req->admin_note)
                                <div class="req-outcome-note">{{ $req->admin_note }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($req->isPending() && ($canManage || $canDelete))
                    <footer class="req-actions">
                        @if ($canManage)
                            @if ($req->hasAccount())
                                <button type="button" class="btn btn-soft-success btn-sm"
                                        data-ar-toggle="#approve-{{ $req->id }}">
                                    <i class="feather-check"></i> {{ $approveLabel }}
                                </button>
                            @else
                                <span class="text-muted" style="font-size:12.5px">
                                    <i class="feather-alert-circle"></i>
                                    No account matches this address, so there is nothing to action.
                                </span>
                            @endif

                            <button type="button" class="btn btn-soft-danger btn-sm"
                                    data-ar-toggle="#reject-{{ $req->id }}">
                                <i class="feather-x"></i> Reject
                            </button>
                        @endif

                        @if ($canDelete)
                            <form method="POST" action="{{ route($routePrefix . '.delete', $req->id) }}"
                                  class="delete-form">
                                @csrf @method('DELETE')
                                <button class="btn btn-icon btn-soft" title="Delete this request">
                                    <i class="feather-trash-2"></i>
                                </button>
                            </form>
                        @endif
                    </footer>

                    @if ($canManage && $req->hasAccount())
                        <form method="POST" action="{{ route($routePrefix . '.approve', $req->id) }}"
                              id="approve-{{ $req->id }}" hidden class="req-panel delete-form"
                              data-confirm-title="{{ $approveLabel }}?"
                              data-confirm-text="{{ $approveConfirm }}"
                              data-confirm-button="Yes, {{ Str::lower($approveLabel) }}">
                            @csrf
                            <label class="form-label" for="note-a-{{ $req->id }}">
                                Note to include in the email <span class="text-muted">(optional)</span>
                            </label>
                            <textarea id="note-a-{{ $req->id }}" name="admin_note" rows="2" class="form-control"
                                      placeholder="Anything the requester should know."></textarea>
                            <button class="btn btn-success btn-sm mt-2">
                                <i class="feather-send"></i> {{ $approveLabel }}
                            </button>
                        </form>
                    @endif

                    @if ($canManage)
                        <form method="POST" action="{{ route($routePrefix . '.reject', $req->id) }}"
                              id="reject-{{ $req->id }}" hidden class="req-panel">
                            @csrf
                            <label class="form-label" for="note-r-{{ $req->id }}">
                                Reason for rejection <span class="req-star">*</span>
                            </label>
                            <textarea id="note-r-{{ $req->id }}" name="admin_note" rows="2" class="form-control"
                                      placeholder="This is emailed to the requester." required></textarea>
                            <button class="btn btn-danger btn-sm mt-2">
                                <i class="feather-x-circle"></i> Reject and notify
                            </button>
                        </form>
                    @endif
                @endif
            </article>
        @empty
            <x-empty-state icon="feather-check-circle"
                           title="{{ request()->hasAny(['q', 'status', 'type', 'from', 'to']) ? 'No requests match these filters' : 'Nothing waiting' }}"
                           message="{{ request()->hasAny(['q', 'status', 'type', 'from', 'to'])
                               ? 'Try widening the date range or clearing the search.'
                               : 'When someone asks for help getting back into their account, it will appear here.' }}">
                @if (request()->hasAny(['q', 'status', 'type', 'from', 'to']))
                    <a href="{{ route($routePrefix . '.index') }}" class="btn btn-soft">Clear filters</a>
                @endif
            </x-empty-state>
        @endforelse
    </div>

    @if ($requests->hasPages())
        <div class="table-foot">
            <p class="foot-note">
                Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }}
            </p>
            {{ $requests->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@if ($canDelete)
    <div class="bulk-bar" data-ar-bulk-bar style="display:none">
        <span><strong data-ar-bulk-count>0</strong> selected</span>
        <button type="submit" form="bulkRequests" class="btn btn-danger btn-sm">
            <i class="feather-trash-2"></i> Delete selected
        </button>
    </div>
@endif

@push('styles')
<style>
    .req-card {
        border: 1px solid var(--ar-line);
        border-radius: var(--ar-radius);
        padding: 18px 20px;
        margin-bottom: 14px;
        background: var(--ar-surface);
        transition: border-color .18s var(--ar-ease), box-shadow .18s var(--ar-ease);
    }

    .req-card:last-child { margin-bottom: 0; }
    .req-card:hover { box-shadow: var(--ar-shadow-xs); }

    /* Pending is the only state that needs acting on, so it is the only one
       that shouts. */
    .req-card.is-pending {
        border-left: 3px solid var(--ar-warning);
        background: linear-gradient(90deg, var(--ar-warning-soft), transparent 240px);
    }

    .req-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .req-badges { display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }

    .req-body { display: grid; gap: 10px; }

    .req-quote {
        border-left: 3px solid var(--ar-line);
        padding: 8px 0 8px 12px;
        font-size: 13.5px;
        color: var(--ar-ink-2);
        line-height: 1.55;
    }

    .req-quote.muted { color: var(--ar-muted); font-size: 13px; }

    .req-quote-label {
        display: block;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--ar-faint);
        margin-bottom: 3px;
    }

    .req-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 18px;
        font-size: 12.5px;
        color: var(--ar-muted);
        align-items: center;
    }

    .req-meta > span { display: inline-flex; align-items: center; gap: 5px; }

    .req-outcome {
        font-size: 13px;
        color: var(--ar-ink-2);
        background: var(--ar-surface-2);
        border: 1px solid var(--ar-line);
        border-radius: var(--ar-radius-xs);
        padding: 10px 13px;
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 6px;
    }

    .req-outcome-note {
        width: 100%;
        margin-top: 5px;
        color: var(--ar-muted);
        font-size: 12.5px;
    }

    .req-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--ar-line-soft);
    }

    .req-actions form { display: inline-flex; margin: 0; }

    .req-panel {
        margin-top: 12px;
        padding: 14px;
        border-radius: var(--ar-radius-sm);
        background: var(--ar-surface-2);
        border: 1px solid var(--ar-line);
    }

    .req-star { color: var(--ar-danger); }

    .chip-count {
        background: var(--ar-warning);
        color: #fff;
        border-radius: 20px;
        padding: 1px 7px;
        font-size: 10.5px;
        font-weight: 800;
        margin-left: 4px;
    }

    .bulk-bar {
        position: sticky;
        bottom: 18px;
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 12px 18px;
        border-radius: var(--ar-radius);
        background: var(--ar-surface);
        border: 1px solid var(--ar-line);
        box-shadow: var(--ar-shadow-lg);
        font-size: 13.5px;
        color: var(--ar-ink-2);
    }

    @media (max-width: 575.98px) {
        .req-head { flex-direction: column; }
        .req-actions .btn { flex: 1 1 auto; }
    }
</style>
@endpush
