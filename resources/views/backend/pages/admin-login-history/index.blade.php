@extends('backend.master')
@section('title', 'Admin Sign-ins')

@push('styles')
<style>
    /* A failed attempt gets a red edge so a burst of them is visible at a glance. */
    tr.row-failed td:first-child {
        box-shadow: inset 3px 0 0 var(--ar-danger);
    }

    tr.row-failed td {
        background: color-mix(in srgb, var(--ar-danger-soft) 45%, transparent);
    }

    .ip-mono {
        font-variant-numeric: tabular-nums;
        letter-spacing: .01em;
    }

    .bulk-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: var(--ar-ink-2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Admin Sign-ins"
        subtitle="Every attempt to sign in to the admin panel, successful or not, with the device and location it came from."
        icon="feather-shield"
        :breadcrumb="['Monitoring' => null, 'Admin Sign-ins' => null]" />

    <div class="stat-grid">
        <x-stat-card label="Sign-ins today" :value="$stats['today']"
                     icon="feather-log-in" tone="primary" foot="since midnight" />

        <x-stat-card label="Successful" :value="$stats['success']"
                     icon="feather-check-circle" tone="info" foot="all time" />

        <x-stat-card label="Failed attempts" :value="$stats['failed']"
                     icon="feather-alert-triangle" tone="danger" foot="all time" />

        <x-stat-card label="Currently signed in" :value="$stats['online']"
                     icon="feather-user-check" tone="success" foot="today, not yet signed out" />
    </div>

    {{-- ================= Filters ================= --}}
    <form method="GET" action="{{ route('admin.admin-login-history.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="filter-label" for="filter-q">Search</label>
                <input type="text" id="filter-q" name="q" value="{{ request('q') }}"
                       class="form-control" placeholder="Name, email or IP address">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter-outcome">Outcome</label>
                <select name="outcome" id="filter-outcome" class="form-select">
                    <option value="all" {{ in_array(request('outcome'), [null, '', 'all'], true) ? 'selected' : '' }}>All</option>
                    <option value="success" {{ request('outcome') === 'success' ? 'selected' : '' }}>Successful</option>
                    <option value="failed" {{ request('outcome') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter-from">From</label>
                <input type="date" id="filter-from" name="from" value="{{ request('from') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter-to">To</label>
                <input type="date" id="filter-to" name="to" value="{{ request('to') }}" class="form-control">
            </div>

            <div class="col-md-1">
                <label class="filter-label" for="filter-per-page">Per page</label>
                <select name="per_page" id="filter-per-page" class="form-select">
                    @foreach ([25, 50, 100] as $size)
                        <option value="{{ $size }}"
                            {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary"><i class="feather-filter"></i> Filter</button>
                <a href="{{ route('admin.admin-login-history.index') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    @php
        $columnCount = 7;
    @endphp
    @can('admin_login_history.delete')
        @php $columnCount = 8; @endphp
    @endcan

    {{-- The bulk-delete form wraps the card so the row checkboxes are inside it. --}}
    @can('admin_login_history.delete')
    <form method="POST" action="{{ route('admin.admin-login-history.bulk-delete') }}"
          class="delete-form"
          data-confirm-title="Delete selected entries?"
          data-confirm-text="The selected sign-in records will be removed permanently."
          data-confirm-button="Yes, delete them">
        @csrf
    @endcan

        <div class="card">
            <div class="card-header">
                <h5>Sign-in Records</h5>

                <div class="d-flex align-items-center gap-3">
                    @can('admin_login_history.delete')
                        <div class="bulk-bar" data-ar-bulk-bar style="display:none">
                            <span><strong data-ar-bulk-count>0</strong> selected</span>
                            <button type="submit" class="btn btn-soft-danger btn-sm">
                                <i class="feather-trash-2"></i> Delete selected
                            </button>
                        </div>
                    @endcan

                    <span class="text-muted fs-13">{{ $histories->total() }} records</span>
                </div>
            </div>

            <div class="table-scroll">
                <table class="table table-hover align-middle" id="adminSigninTable">
                    <thead>
                        <tr>
                            @can('admin_login_history.delete')
                                <th style="width:42px">
                                    <input type="checkbox" class="form-check-input"
                                           data-ar-check-all="#adminSigninTable"
                                           aria-label="Select all records on this page">
                                </th>
                            @endcan
                            <th>Admin</th>
                            <th>Role</th>
                            <th>Outcome</th>
                            <th>IP &amp; Location</th>
                            <th>Device</th>
                            <th>Signed In</th>
                            <th>Session</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($histories as $history)
                            @php
                                $name = trim((string) $history->user_name) !== '' ? $history->user_name : 'Unknown';

                                $initials = \Illuminate\Support\Str::of($name)
                                    ->explode(' ')
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                    ->implode('');

                                $role = trim((string) $history->user_role);

                                $place = collect([$history->city, $history->country])
                                    ->filter(fn ($value) => trim((string) $value) !== '')
                                    ->implode(', ');
                            @endphp

                            <tr class="{{ $history->successful ? '' : 'row-failed' }}">
                                @can('admin_login_history.delete')
                                    <td>
                                        <input type="checkbox" class="form-check-input"
                                               name="ids[]" value="{{ $history->id }}"
                                               data-ar-check
                                               aria-label="Select record {{ $history->id }}">
                                    </td>
                                @endcan

                                <td>
                                    <div class="cell-media">
                                        <span class="avatar-initials sm">{{ $initials !== '' ? $initials : '?' }}</span>
                                        <div>
                                            <p class="cell-title">{{ $name }}</p>
                                            <p class="cell-sub">{{ $history->user_email ?: 'No email recorded' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($role !== '' && $role !== '—')
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($history->successful)
                                        <span class="badge bg-success">Successful</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="cell-media">
                                        <div>
                                            <p class="cell-title ip-mono">{{ $history->ip_address ?: 'Unknown' }}</p>
                                            <p class="cell-sub">{{ $place !== '' ? $place : 'Unknown' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span title="{{ $history->user_agent }}">{{ $history->device() }}</span>
                                </td>

                                <td>
                                    @if ($history->logged_in_at)
                                        <div class="cell-media">
                                            <div>
                                                <p class="cell-title">{{ $history->logged_in_at->format('d M Y, h:i A') }}</p>
                                                <p class="cell-sub">{{ $history->logged_in_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($history->isOnline())
                                        <span class="status-pill on">Active now</span>
                                    @elseif ($history->duration())
                                        {{ $history->duration() }}
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $columnCount }}">
                                    <x-empty-state icon="feather-shield"
                                                   title="No sign-ins match this filter"
                                                   message="Nothing has been recorded for the search and dates you picked." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($histories->hasPages())
                <div class="table-foot">
                    <p class="foot-note">Showing {{ $histories->firstItem() }}&ndash;{{ $histories->lastItem() }}
                        of {{ $histories->total() }}</p>
                    {{ $histories->links() }}
                </div>
            @endif
        </div>

    @can('admin_login_history.delete')
    </form>
    @endcan

</div>
@endsection
