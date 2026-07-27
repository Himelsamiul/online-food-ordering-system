@extends('backend.master')
@section('title', 'Customers')

@push('styles')
<style>
    /* The optional "why are you switching this off" box, revealed per row. */
    .reason-panel {
        margin-top: 8px;
        min-width: 210px;
    }

    .reason-hint {
        font-size: 11.5px;
        color: var(--ar-muted);
        margin: 4px 0 0;
    }

    /* Keep a long deactivation reason from stretching the column. */
    .status-note {
        max-width: 240px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Customers"
        subtitle="Everyone with a storefront account, what they have ordered, and whether they can still sign in."
        icon="feather-users"
        :breadcrumb="['People' => null, 'Customers' => null]" />

    <div class="stat-grid">
        <x-stat-card label="Total Customers" :value="$stats['total']"
                     icon="feather-users" tone="primary" foot="all registered accounts" />

        <x-stat-card label="Active" :value="$stats['active']"
                     icon="feather-user-check" tone="success" foot="can sign in right now" />

        <x-stat-card label="Inactive" :value="$stats['inactive']"
                     icon="feather-user-x" tone="danger" foot="blocked until switched back on" />

        <x-stat-card label="New in 30 Days" :value="$stats['new']"
                     icon="feather-user-plus" tone="info" foot="joined since {{ now()->subDays(30)->format('d M Y') }}" />
    </div>

    {{-- ================= Filters ================= --}}
    <form method="GET" action="{{ route('admin.registrations') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-5">
                <label class="filter-label" for="filter-q">Search</label>
                <input type="text" id="filter-q" name="q" value="{{ request('q') }}"
                       class="form-control" placeholder="Name, username, email or phone">
            </div>

            <div class="col-md-3">
                <label class="filter-label" for="filter-status">Status</label>
                <select name="status" id="filter-status" class="form-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="filter-per-page">Per page</label>
                <select name="per_page" id="filter-per-page" class="form-select">
                    @foreach ([20, 50, 100, 200] as $size)
                        <option value="{{ $size }}"
                            {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary"><i class="feather-filter"></i> Filter</button>
                <a href="{{ route('admin.registrations') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    @php $columnCount = 5; @endphp
    @canany(['customers.edit', 'customers.delete'])
        @php $columnCount = 6; @endphp
    @endcanany

    <div class="card">
        <div class="card-header">
            <h5>Customer Accounts</h5>
            <span class="text-muted fs-13">{{ $registrations->total() }} customers</span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Joined</th>
                        <th class="num">Orders</th>
                        <th>Status</th>
                        @canany(['customers.edit', 'customers.delete'])
                            <th>Actions</th>
                        @endcanany
                    </tr>
                </thead>

                <tbody>
                    @forelse ($registrations as $user)
                        @php
                            $name = trim((string) $user->full_name) !== '' ? $user->full_name : $user->username;

                            $initials = \Illuminate\Support\Str::of((string) $name)
                                ->explode(' ')
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');

                            $hasPhoto = $user->image && file_exists(public_path('storage/' . $user->image));
                        @endphp

                        <tr>
                            <td>
                                <div class="cell-media">
                                    @if ($hasPhoto)
                                        <img src="{{ asset('storage/' . $user->image) }}"
                                             alt="{{ $name }}" width="38" height="38"
                                             style="object-fit:cover;border-radius:11px">
                                    @else
                                        <span class="avatar-initials">{{ $initials !== '' ? $initials : '?' }}</span>
                                    @endif

                                    <div>
                                        <p class="cell-title">{{ $name }}</p>
                                        <p class="cell-sub">&#64;{{ $user->username }}</p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-media">
                                    <div>
                                        <p class="cell-title">{{ $user->email }}</p>
                                        <p class="cell-sub">{{ $user->phone ?: 'No phone on file' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if ($user->created_at)
                                    <div class="cell-media">
                                        <div>
                                            <p class="cell-title">{{ $user->created_at->format('d M Y, h:i A') }}</p>
                                            <p class="cell-sub">{{ $user->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>

                            <td class="num">{{ $user->orders_count }}</td>

                            <td>
                                <div class="cell-media">
                                    <div class="status-note">
                                        @if ($user->isActive())
                                            <span class="status-pill on">Active</span>
                                        @else
                                            <span class="status-pill off">Inactive</span>

                                            @if ($user->deactivation_reason)
                                                <p class="cell-sub">{{ $user->deactivation_reason }}</p>
                                            @endif

                                            @if ($user->deactivated_at)
                                                <p class="cell-sub">Since {{ $user->deactivated_at->format('d M Y, h:i A') }}</p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </td>

                            @canany(['customers.edit', 'customers.delete'])
                                <td>
                                    <div class="action-group">

                                        @can('customers.edit')
                                            @if ($user->isActive())
                                                <form action="{{ route('admin.registrations.status', $user->id) }}"
                                                      method="POST"
                                                      class="delete-form"
                                                      style="display:block"
                                                      data-confirm-title="Deactivate this customer?"
                                                      data-confirm-text="They will be signed out and blocked from logging in until you switch them back on. They will be emailed."
                                                      data-confirm-button="Yes, deactivate">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="button" class="btn btn-icon btn-soft"
                                                            data-ar-toggle="#reason-{{ $user->id }}"
                                                            title="Add a reason (optional)"
                                                            aria-label="Add a reason for deactivating {{ $name }}">
                                                        <i class="feather-message-square"></i>
                                                    </button>

                                                    <button type="submit" class="btn btn-soft-warning btn-sm">
                                                        <i class="feather-user-x"></i> Deactivate
                                                    </button>

                                                    <div id="reason-{{ $user->id }}" class="reason-panel" hidden>
                                                        <input type="text" name="reason" maxlength="255"
                                                               class="form-control form-control-sm"
                                                               placeholder="Reason (optional)">
                                                        <p class="reason-hint">Included in the email we send them.</p>
                                                    </div>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.registrations.status', $user->id) }}"
                                                      method="POST"
                                                      class="delete-form"
                                                      data-confirm-title="Reactivate this customer?"
                                                      data-confirm-text="They will be able to sign in again and will be emailed."
                                                      data-confirm-button="Yes, reactivate">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit" class="btn btn-soft-success btn-sm">
                                                        <i class="feather-user-check"></i> Activate
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('customers.delete')
                                            <form action="{{ route('admin.registrations.delete', $user->id) }}"
                                                  method="POST"
                                                  class="delete-form"
                                                  data-confirm-title="Delete {{ $name }}?"
                                                  data-confirm-text="The account and its profile photo are removed permanently."
                                                  data-confirm-button="Yes, delete customer">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-soft-danger btn-sm">
                                                    <i class="feather-trash-2"></i> Delete
                                                </button>
                                            </form>
                                        @endcan

                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}">
                                <x-empty-state icon="feather-users"
                                               title="No customers match this filter"
                                               message="Try a different search term or set the status back to All." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registrations->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $registrations->firstItem() }}&ndash;{{ $registrations->lastItem() }}
                    of {{ $registrations->total() }}</p>
                {{ $registrations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
