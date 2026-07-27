@extends('backend.master')
@section('title', 'Account Requests')

@push('styles')
<style>
    /* The inbox is a stack of cards — a table cannot hold a paragraph of
       customer text plus three action forms without becoming unreadable. */
    .ar-list { display: grid; gap: 14px; }

    .ar-item {
        position: relative;
        border: 1px solid var(--ar-line);
        border-left: 3px solid transparent;
        border-radius: var(--ar-radius);
        background: var(--ar-surface);
        padding: 16px 18px;
        transition: box-shadow .18s var(--ar-ease), border-color .18s var(--ar-ease);
    }

    .ar-item:hover { box-shadow: var(--ar-shadow); }

    /* Anything still waiting on an admin gets a warning edge. */
    .ar-item.is-pending {
        border-left-color: var(--ar-warning);
        background: linear-gradient(90deg, var(--ar-warning-soft), transparent 320px), var(--ar-surface);
    }

    .ar-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ar-who { display: flex; align-items: flex-start; gap: 12px; min-width: 0; }
    .ar-who-text { min-width: 0; }
    .ar-check { margin-top: 12px; flex-shrink: 0; }
    .ar-name { font-size: 14.5px; font-weight: 700; color: var(--ar-ink); margin: 0; line-height: 1.35; }
    .ar-contact { font-size: 12.5px; color: var(--ar-muted); margin: 3px 0 0; word-break: break-word; }
    .ar-contact .sep { color: var(--ar-faint); margin: 0 6px; }

    .ar-flags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }

    .ar-meta {
        display: flex;
        align-items: center;
        gap: 8px 14px;
        flex-wrap: wrap;
        margin: 12px 0 0;
        font-size: 12px;
        color: var(--ar-muted);
    }

    .ar-meta .mono { font-variant-numeric: tabular-nums; }

    .ar-quote {
        margin: 12px 0 0;
        padding: 11px 14px;
        border-left: 3px solid var(--ar-line);
        border-radius: 0 var(--ar-radius-xs) var(--ar-radius-xs) 0;
        background: var(--ar-surface-2);
        color: var(--ar-ink-2);
        font-size: 13.5px;
        line-height: 1.6;
        white-space: pre-line;
        word-break: break-word;
    }

    .ar-handled {
        margin: 12px 0 0;
        padding: 11px 14px;
        border: 1px dashed var(--ar-line);
        border-radius: var(--ar-radius-xs);
        background: var(--ar-surface-2);
    }

    .ar-handled p { margin: 0; }
    .ar-handled-head { font-size: 12.5px; font-weight: 650; color: var(--ar-ink-2); }
    .ar-handled-note { margin-top: 6px !important; font-size: 13px; color: var(--ar-muted); word-break: break-word; }

    .ar-actions {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--ar-line-soft);
    }

    .ar-actions form { margin: 0; }
    .ar-note-panel { margin-bottom: 10px; max-width: 460px; }
    .ar-note-panel textarea { font-size: 13px; }

    .ar-blocked {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 12.5px;
        color: var(--ar-muted);
        margin: 0;
        max-width: 62ch;
    }

    .ar-blocked i { color: var(--ar-warning); margin-top: 2px; }

    .bulk-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: var(--ar-ink-2);
    }

    .search-form .row { --bs-gutter-y: 12px; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    @php
        $typePassword   = \App\Models\AccountRequest::TYPE_PASSWORD;
        $typeActivation = \App\Models\AccountRequest::TYPE_ACTIVATION;

        $statusTabs = [
            ''                                              => 'All',
            \App\Models\AccountRequest::STATUS_PENDING       => 'Pending',
            \App\Models\AccountRequest::STATUS_RESOLVED      => 'Resolved',
            \App\Models\AccountRequest::STATUS_REJECTED      => 'Rejected',
        ];

        // Status tabs keep whatever search/type filter is already applied.
        $carried = collect(request()->query())->except(['status', 'page'])->all();

        $tabUrl = function (string $status) use ($carried) {
            $params = $carried;

            if ($status !== '') {
                $params['status'] = $status;
            }

            return route('admin.account-requests.index', $params);
        };

        $currentStatus = (string) request('status', '');

        $filtered = filled(request('q')) || filled(request('type')) || filled(request('status'));

        // Two letters out of whatever the customer typed as their name.
        $initials = function (?string $name) {
            $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if (!$parts) {
                return '?';
            }

            $letters = mb_substr($parts[0], 0, 1)
                . (count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '');

            return mb_strtoupper($letters);
        };
    @endphp

    <x-page-header
        title="Account Requests"
        subtitle="Customers who cannot sign in ask for help here. Issue a new password or switch an account back on, and they are emailed either way."
        icon="feather-inbox"
        :breadcrumb="['People' => null, 'Account Requests' => null]">

        @can('customers.view')
            <a href="{{ route('admin.registrations') }}" class="btn btn-soft">
                <i class="feather-users"></i> Customers
            </a>
        @endcan
    </x-page-header>

    {{-- ================= Stats ================= --}}
    <div class="stat-grid">
        <x-stat-card label="Pending" :value="$counts['pending']"
                     icon="feather-clock" tone="warning" foot="waiting for an answer" />

        <x-stat-card label="Resolved" :value="$counts['resolved']"
                     icon="feather-check-circle" tone="success" foot="password issued or account switched on" />

        <x-stat-card label="Rejected" :value="$counts['rejected']"
                     icon="feather-x-circle" tone="danger" foot="turned down with a reason" />

        <x-stat-card label="Total" :value="$counts['total']"
                     icon="feather-inbox" tone="primary" foot="all time" />
    </div>

    {{-- ================= Filters ================= --}}
    <div class="filter-card">
        <div class="chip-row mb-3">
            @foreach ($statusTabs as $value => $label)
                <a href="{{ $tabUrl((string) $value) }}"
                   class="chip {{ $currentStatus === (string) $value ? 'active' : '' }}">
                    {{ $label }}
                    @if ($value !== '' && isset($counts[$value]))
                        <span class="text-muted">{{ $counts[$value] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.account-requests.index') }}" class="search-form">
            {{-- Searching must not throw away the tab the admin is standing on. --}}
            @if (filled(request('status')))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="row g-3 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label class="filter-label" for="filter-q">Search</label>
                    <input type="text" id="filter-q" name="q" value="{{ request('q') }}"
                           class="form-control" placeholder="Name, email or phone">
                </div>

                <div class="col-lg-3 col-md-4">
                    <label class="filter-label" for="filter-type">Request Type</label>
                    <select name="type" id="filter-type" class="form-select">
                        <option value="">All types</option>
                        <option value="{{ $typePassword }}" @selected(request('type') === $typePassword)>Password Reset</option>
                        <option value="{{ $typeActivation }}" @selected(request('type') === $typeActivation)>Reactivation</option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary"><i class="feather-filter"></i> Filter</button>
                    <a href="{{ route('admin.account-requests.index') }}" class="btn btn-soft">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{--
        Bulk delete lives outside the list: each card already carries its own
        forms, and forms cannot be nested. The checkboxes and the bar's button
        join this one through the HTML form attribute.
    --}}
    @can('account_requests.delete')
        <form id="bulkDeleteRequests" method="POST" action="{{ route('admin.account-requests.bulk-delete') }}"
              class="delete-form"
              data-confirm-title="Delete selected requests?"
              data-confirm-text="The selected requests are removed permanently. Customer accounts are not affected."
              data-confirm-button="Yes, delete them">
            @csrf
        </form>
    @endcan

    <div class="card">
        <div class="card-header">
            <h5>Inbox</h5>

            <div class="d-flex align-items-center gap-3 flex-wrap">
                @can('account_requests.delete')
                    <div class="bulk-bar" data-ar-bulk-bar style="display:none">
                        <span><strong data-ar-bulk-count>0</strong> selected</span>
                        <button type="submit" form="bulkDeleteRequests" class="btn btn-soft-danger btn-sm">
                            <i class="feather-trash-2"></i> Delete selected
                        </button>
                    </div>

                    @if ($requests->count())
                        <div class="form-check m-0">
                            <input type="checkbox" class="form-check-input" id="checkAllRequests"
                                   data-ar-check-all="#requestList"
                                   aria-label="Select every request on this page">
                            <label class="form-check-label fs-13" for="checkAllRequests">Select page</label>
                        </div>
                    @endif
                @endcan

                <span class="text-muted fs-13">{{ $requests->total() }} requests</span>
            </div>
        </div>

        <div class="card-body">
            @if ($requests->count())
                <div class="ar-list" id="requestList">
                    @foreach ($requests as $r)
                        @php
                            $customer = $r->registration;
                        @endphp

                        <article class="ar-item {{ $r->isPending() ? 'is-pending' : '' }}">

                            <div class="ar-head">
                                <div class="ar-who">
                                    @can('account_requests.delete')
                                        <input type="checkbox" class="form-check-input ar-check"
                                               name="ids[]" value="{{ $r->id }}"
                                               form="bulkDeleteRequests" data-ar-check
                                               aria-label="Select the request from {{ $r->email }}">
                                    @endcan

                                    <span class="avatar-initials">{{ $initials($r->name) }}</span>

                                    <div class="ar-who-text">
                                        <p class="ar-name">{{ $r->name ?: 'Unnamed customer' }}</p>
                                        <p class="ar-contact">
                                            <i class="feather-mail"></i> {{ $r->email }}
                                            @if (filled($r->phone))
                                                <span class="sep">·</span>
                                                <i class="feather-phone"></i> {{ $r->phone }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="ar-flags">
                                    <span class="badge bg-{{ $r->typeTone() }}">
                                        <i class="{{ $r->typeIcon() }}"></i> {{ $r->typeLabel() }}
                                    </span>

                                    <span class="badge bg-{{ $r->statusTone() }}">{{ $r->statusLabel() }}</span>

                                    @can('account_requests.delete')
                                        <form action="{{ route('admin.account-requests.delete', $r->id) }}" method="POST"
                                              class="delete-form"
                                              data-confirm-title="Delete this request?"
                                              data-confirm-text="The request is removed from the inbox permanently. The customer's account is not touched."
                                              data-confirm-button="Yes, delete it">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-soft-danger"
                                                    title="Delete request"
                                                    aria-label="Delete the request from {{ $r->email }}">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>

                            <p class="ar-meta">
                                @if ($customer)
                                    <span class="status-pill on">Account found</span>

                                    @unless ($customer->isActive())
                                        <span class="status-pill off">Currently switched off</span>
                                    @endunless
                                @else
                                    <span class="status-pill off">No matching account</span>
                                @endif

                                @if ($r->created_at)
                                    <span>
                                        <i class="feather-clock"></i>
                                        Submitted {{ $r->created_at->diffForHumans() }}
                                        <span class="mono">({{ $r->created_at->format('d M Y, h:i A') }})</span>
                                    </span>
                                @endif

                                @if (filled($r->ip_address))
                                    <span title="IP address">
                                        <i class="feather-globe"></i> <span class="mono">{{ $r->ip_address }}</span>
                                    </span>
                                @endif
                            </p>

                            @if (filled($r->message))
                                <blockquote class="ar-quote">{{ $r->message }}</blockquote>
                            @endif

                            @unless ($r->isPending())
                                <div class="ar-handled">
                                    <p class="ar-handled-head">
                                        <i class="feather-{{ $r->statusTone() === 'success' ? 'check' : 'x' }}-circle"></i>
                                        {{ $r->statusLabel() }} by {{ $r->handled_by_name ?: 'an admin' }}@if ($r->handled_at) on {{ $r->handled_at->format('d M Y, h:i A') }}@endif
                                    </p>

                                    @if (filled($r->admin_note))
                                        <p class="ar-handled-note">&ldquo;{{ $r->admin_note }}&rdquo;</p>
                                    @endif
                                </div>
                            @endunless

                            @if ($r->isPending())
                                @can('account_requests.edit')
                                    <div class="ar-actions">

                                        @if (!$customer)
                                            <p class="ar-blocked">
                                                <i class="feather-alert-circle"></i>
                                                <span>
                                                    No customer account uses {{ $r->email }}, so there is nothing to
                                                    reset or switch on. Reject it with a reason so the customer hears
                                                    back, or delete the request.
                                                </span>
                                            </p>
                                        @elseif ($r->isPasswordReset())
                                            <form action="{{ route('admin.account-requests.issue-password', $r->id) }}"
                                                  method="POST"
                                                  class="delete-form"
                                                  data-confirm-title="Issue a new password?"
                                                  data-confirm-text="A new random password will be emailed to the customer and their old one will stop working."
                                                  data-confirm-button="Yes, issue it">
                                                @csrf

                                                <div class="ar-note-panel" id="issueNote{{ $r->id }}" hidden>
                                                    <label class="filter-label" for="issueNoteField{{ $r->id }}">
                                                        Note to the customer (optional)
                                                    </label>
                                                    <textarea class="form-control" id="issueNoteField{{ $r->id }}"
                                                              name="admin_note" rows="2" maxlength="500"
                                                              placeholder="Added to the email above the new password."></textarea>
                                                </div>

                                                <div class="action-group">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="feather-key"></i> Generate &amp; email new password
                                                    </button>
                                                    <button type="button" class="btn btn-soft btn-sm"
                                                            data-ar-toggle="#issueNote{{ $r->id }}">
                                                        <i class="feather-edit-3"></i> Add a note
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.account-requests.activate', $r->id) }}"
                                                  method="POST"
                                                  class="delete-form"
                                                  data-confirm-title="Reactivate this account?"
                                                  data-confirm-text="The customer will be able to sign in again and will be emailed to say so."
                                                  data-confirm-button="Yes, reactivate it">
                                                @csrf

                                                <div class="ar-note-panel" id="activateNote{{ $r->id }}" hidden>
                                                    <label class="filter-label" for="activateNoteField{{ $r->id }}">
                                                        Note to the customer (optional)
                                                    </label>
                                                    <textarea class="form-control" id="activateNoteField{{ $r->id }}"
                                                              name="admin_note" rows="2" maxlength="500"
                                                              placeholder="Added to the email that tells them the account is back on."></textarea>
                                                </div>

                                                <div class="action-group">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="feather-user-check"></i> Reactivate &amp; email
                                                    </button>
                                                    <button type="button" class="btn btn-soft btn-sm"
                                                            data-ar-toggle="#activateNote{{ $r->id }}">
                                                        <i class="feather-edit-3"></i> Add a note
                                                    </button>
                                                </div>
                                            </form>
                                        @endif

                                        {{--
                                            No .delete-form here on purpose: that confirm submits the form
                                            through JS, which would skip the browser's required check on the
                                            reason. The reason is what gets mailed, so it must not be empty.
                                        --}}
                                        <form action="{{ route('admin.account-requests.reject', $r->id) }}" method="POST">
                                            @csrf

                                            <div class="ar-note-panel" id="rejectNote{{ $r->id }}" hidden>
                                                <label class="filter-label" for="rejectNoteField{{ $r->id }}">
                                                    Reason (emailed to the customer)
                                                </label>
                                                <textarea class="form-control" id="rejectNoteField{{ $r->id }}"
                                                          name="admin_note" rows="2" maxlength="500" required
                                                          placeholder="Tell them why this could not be done."></textarea>

                                                <button type="submit" class="btn btn-soft-danger btn-sm mt-2">
                                                    <i class="feather-send"></i> Reject and send the reason
                                                </button>
                                            </div>

                                            <button type="button" class="btn btn-soft btn-sm"
                                                    data-ar-toggle="#rejectNote{{ $r->id }}">
                                                <i class="feather-x-circle"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                @endcan
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="feather-inbox"
                               :title="$filtered ? 'No requests match this filter' : 'No requests waiting'"
                               :message="$filtered
                                    ? 'Nothing in the inbox matches what you searched for. Try a different status or clear the filters.'
                                    : 'Everyone can get into their account. When a customer is locked out and asks for help, their request lands here.'">
                    @if ($filtered)
                        <a href="{{ route('admin.account-requests.index') }}" class="btn btn-soft">Clear filters</a>
                    @endif
                </x-empty-state>
            @endif
        </div>

        @if ($requests->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $requests->firstItem() }}&ndash;{{ $requests->lastItem() }}
                    of {{ $requests->total() }}</p>
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
