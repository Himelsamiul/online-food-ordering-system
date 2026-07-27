@extends('backend.master')
@section('title', 'Customer Sign-ins')

@push('styles')
<style>
    .ip-mono {
        font-variant-numeric: tabular-nums;
        letter-spacing: .01em;
    }

    /* A raw user agent is long; keep it inside its column. */
    .user-agent {
        max-width: 230px;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
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
        title="Customer Sign-ins"
        subtitle="Every storefront sign-in, with the device and place it came from and how long the session lasted."
        icon="feather-log-in"
        :breadcrumb="['Monitoring' => null, 'Customer Sign-ins' => null]" />

    @php
        // Everything below is read off the page already loaded — no extra queries.
        $pageRows       = $histories->getCollection();
        $stillOnline    = $pageRows->whereNull('logged_out_at')->count();
        $uniqueCustomers = $pageRows->pluck('registration_id')->filter()->unique()->count();
        $uniqueCountries = $pageRows->pluck('country')->filter()->unique()->count();
    @endphp

    <div class="stat-grid">
        <x-stat-card label="Records Found" :value="$histories->total()"
                     icon="feather-list" tone="primary" foot="matching the current filter" />

        <x-stat-card label="Still Signed In" :value="$stillOnline"
                     icon="feather-user-check" tone="success" foot="on this page" />

        <x-stat-card label="Customers" :value="$uniqueCustomers"
                     icon="feather-users" tone="info" foot="distinct, on this page" />

        <x-stat-card label="Countries" :value="$uniqueCountries"
                     icon="feather-globe" tone="warning" foot="distinct, on this page" />
    </div>

    {{-- ================= Filters ================= --}}
    <form method="GET" action="{{ route('admin.login.history') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="filter-label" for="filter-name">Customer</label>
                <input type="text" id="filter-name" name="name" value="{{ request('name') }}"
                       class="form-control" placeholder="Name or username">
            </div>

            <div class="col-md-3">
                <label class="filter-label" for="filter-country">Country</label>
                <input type="text" id="filter-country" name="country" value="{{ request('country') }}"
                       class="form-control" placeholder="Country">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter-date">Signed in on</label>
                <input type="date" id="filter-date" name="date" value="{{ request('date') }}"
                       class="form-control">
            </div>

            <div class="col-md-1">
                <label class="filter-label" for="filter-per-page">Per page</label>
                <select name="per_page" id="filter-per-page" class="form-select">
                    @foreach ([20, 50, 100, 200, 500] as $size)
                        <option value="{{ $size }}"
                            {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary"><i class="feather-filter"></i> Filter</button>
                <a href="{{ route('admin.login.history') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    @php $columnCount = 6; @endphp
    @can('login_history.delete')
        @php $columnCount = 7; @endphp
    @endcan

    {{-- The bulk-delete form wraps the card so the row checkboxes post with it. --}}
    @can('login_history.delete')
    <form action="{{ route('admin.login.history.bulk.delete') }}" method="POST"
          class="delete-form"
          data-confirm-title="Delete selected sign-ins?"
          data-confirm-text="The selected sign-in records are removed permanently."
          data-confirm-button="Yes, delete them">
        @csrf
    @endcan

        <div class="card">
            <div class="card-header">
                <h5>Sign-in Records</h5>

                <div class="d-flex align-items-center gap-3">
                    @can('login_history.delete')
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
                <table class="table table-hover align-middle" id="loginHistoryTable">
                    <thead>
                        <tr>
                            @can('login_history.delete')
                                <th style="width:42px">
                                    <input type="checkbox" class="form-check-input"
                                           data-ar-check-all="#loginHistoryTable"
                                           aria-label="Select all records on this page">
                                </th>
                            @endcan
                            <th>Customer</th>
                            <th>Location</th>
                            <th>IP Address</th>
                            <th>Device</th>
                            <th>Signed In</th>
                            <th>Session</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($histories as $history)
                            @php
                                $customer = $history->registration;
                                $name     = $customer->full_name ?? 'Deleted customer';

                                $initials = \Illuminate\Support\Str::of((string) $name)
                                    ->explode(' ')
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                    ->implode('');

                                $agent = (string) $history->user_agent;

                                $browser = match (true) {
                                    str_contains($agent, 'Edg/')     => 'Edge',
                                    str_contains($agent, 'OPR/')     => 'Opera',
                                    str_contains($agent, 'Chrome/')  => 'Chrome',
                                    str_contains($agent, 'Firefox/') => 'Firefox',
                                    str_contains($agent, 'Safari/')  => 'Safari',
                                    default                          => 'Unknown browser',
                                };

                                $platform = match (true) {
                                    str_contains($agent, 'Windows') => 'Windows',
                                    str_contains($agent, 'Android') => 'Android',
                                    str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
                                    str_contains($agent, 'Mac OS')  => 'macOS',
                                    str_contains($agent, 'Linux')   => 'Linux',
                                    default                         => 'Unknown OS',
                                };

                                $inAt  = $history->logged_in_at ? strtotime($history->logged_in_at) : null;
                                $outAt = $history->logged_out_at ? strtotime($history->logged_out_at) : null;

                                $duration = null;

                                if ($inAt && $outAt && $outAt >= $inAt) {
                                    $seconds = $outAt - $inAt;

                                    $duration = match (true) {
                                        $seconds < 60   => $seconds . ' sec',
                                        $seconds < 3600 => intdiv($seconds, 60) . ' min',
                                        default         => intdiv($seconds, 3600) . ' hr '
                                                           . intdiv($seconds % 3600, 60) . ' min',
                                    };
                                }
                            @endphp

                            <tr>
                                @can('login_history.delete')
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
                                            @if ($customer && $customer->username)
                                                <p class="cell-sub">&#64;{{ $customer->username }}</p>
                                            @else
                                                <p class="cell-sub">Account removed</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="cell-media">
                                        <div>
                                            <p class="cell-title">{{ $history->country ?: 'Unknown' }}</p>
                                            <p class="cell-sub">{{ $history->city ?: 'No city recorded' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="ip-mono">{{ $history->ip_address ?: 'Unknown' }}</td>

                                <td>
                                    <div class="cell-media">
                                        <div class="user-agent" title="{{ $agent }}">
                                            <p class="cell-title">{{ $browser }}</p>
                                            <p class="cell-sub">{{ $platform }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($inAt)
                                        <div class="cell-media">
                                            <div>
                                                <p class="cell-title">{{ date('d M Y, h:i A', $inAt) }}</p>
                                                <p class="cell-sub">{{ date('l', $inAt) }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>

                                <td>
                                    @if (!$outAt)
                                        <span class="status-pill on">Still signed in</span>
                                    @else
                                        <div class="cell-media">
                                            <div>
                                                <p class="cell-title">{{ $duration ?: 'Signed out' }}</p>
                                                <p class="cell-sub">Out {{ date('d M Y, h:i A', $outAt) }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $columnCount }}">
                                    <x-empty-state icon="feather-log-in"
                                                   title="No sign-ins match this filter"
                                                   message="Nothing was recorded for the customer, country or date you picked." />
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

    @can('login_history.delete')
    </form>
    @endcan

</div>
@endsection
