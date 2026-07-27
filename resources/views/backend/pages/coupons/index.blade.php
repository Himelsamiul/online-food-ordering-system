@extends('backend.master')
@section('title', 'Coupons')

@section('content')
<div class="container-fluid">

    @php
        $canCreate  = auth()->user()?->can('coupons.create') ?? false;
        $canEdit    = auth()->user()?->can('coupons.edit') ?? false;
        $canDelete  = auth()->user()?->can('coupons.delete') ?? false;
        $hasActions = $canEdit || $canDelete;
        $colspan    = $hasActions ? 8 : 7;
    @endphp

    <x-page-header
        title="Coupons"
        subtitle="Discount codes customers type at checkout, with their own window and limits."
        icon="feather-gift"
        :breadcrumb="['Marketing' => null, 'Coupons' => null]">

        @can('promotions.view')
            <a href="{{ route('admin.promotions.index') }}" class="btn btn-soft">
                <i class="feather-image"></i> Promo Banners
            </a>
        @endcan

        @can('coupons.create')
            <button type="button" class="btn btn-primary" data-ar-toggle="#coupon-create">
                <i class="feather-plus"></i> New Coupon
            </button>
        @endcan
    </x-page-header>

    {{-- ================= Add coupon ================= --}}
    @can('coupons.create')
        <div class="card" id="coupon-create" @if (! $errors->any()) hidden @endif>
            <div class="card-header">
                <h5>Add Coupon</h5>
                <span class="text-muted fs-13">Everything except the code and value is optional</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.coupons.store') }}" method="POST">
                    @csrf

                    @include('backend.pages.coupons._form', ['coupon' => null])

                    <hr class="hr-soft">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-plus"></i> Add Coupon
                        </button>
                        <button type="button" class="btn btn-soft" data-ar-toggle="#coupon-create">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    {{-- ================= Filters ================= --}}
    <form method="GET" action="{{ route('admin.coupons.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-4">
                <label class="filter-label" for="filter-code">Code</label>
                <input type="text" id="filter-code" name="code" value="{{ request('code') }}"
                       class="form-control" placeholder="Search code">
            </div>

            <div class="col-md-3">
                <label class="filter-label" for="filter-type">Discount Type</label>
                <select name="type" id="filter-type" class="form-select">
                    <option value="">All</option>
                    <option value="percent" {{ request('type') === 'percent' ? 'selected' : '' }}>Percentage</option>
                    <option value="fixed"   {{ request('type') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="filter-label" for="filter-status">Status</label>
                <select name="status" id="filter-status" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="feather-filter"></i> Filter
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    {{-- ================= List ================= --}}
    <div class="card">
        <div class="card-header">
            <h5>All Coupons</h5>
            <span class="text-muted fs-13">{{ $coupons->total() }} total</span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width:56px;">SL</th>
                        <th>Code</th>
                        <th style="width:150px;">Discount</th>
                        <th class="num" style="width:150px;">Min Order / Cap</th>
                        <th style="width:210px;">Validity</th>
                        <th class="num" style="width:150px;">Usage</th>
                        <th style="width:110px;">Status</th>
                        @if ($hasActions)
                            <th style="width:110px;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $key => $coupon)

                        @php
                            $isExpired   = $coupon->expires_at && $coupon->expires_at->isPast();
                            $isScheduled = $coupon->starts_at && $coupon->starts_at->isFuture();

                            if ($isExpired) {
                                $windowLabel = 'Expired';
                                $windowTone  = 'off';
                            } elseif ($isScheduled) {
                                $windowLabel = 'Scheduled';
                                $windowTone  = 'wait';
                            } elseif (! $coupon->status) {
                                $windowLabel = 'Paused';
                                $windowTone  = 'off';
                            } else {
                                $windowLabel = 'Running';
                                $windowTone  = 'on';
                            }

                            $used     = (int) $coupon->used_count;
                            $limit    = $coupon->usage_limit;
                            $usedPct  = $limit > 0 ? min(100, (int) round($used / $limit * 100)) : 0;
                        @endphp

                        <tr>
                            <td>{{ $coupons->firstItem() + $key }}</td>

                            <td>
                                <span class="code-chip">{{ $coupon->code }}</span>
                                @if (filled($coupon->description))
                                    <small class="d-block text-muted mt-1">{{ $coupon->description }}</small>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $coupon->offer_label }}</strong>
                                <span class="badge {{ $coupon->type === 'percent' ? 'bg-info' : 'bg-primary' }} d-block-badge">
                                    {{ $coupon->type === 'percent' ? 'Percentage' : 'Fixed amount' }}
                                </span>
                            </td>

                            <td class="num">
                                @if ($coupon->min_order_amount)
                                    ৳{{ number_format($coupon->min_order_amount, 2) }}
                                @else
                                    <span class="text-muted">No minimum</span>
                                @endif

                                <small class="d-block text-muted">
                                    @if ($coupon->type === 'percent' && $coupon->max_discount)
                                        Cap ৳{{ number_format($coupon->max_discount, 2) }}
                                    @else
                                        No cap
                                    @endif
                                </small>
                            </td>

                            <td>
                                <span class="status-pill {{ $windowTone }}">{{ $windowLabel }}</span>
                                <small class="d-block text-muted mt-1">
                                    {{ $coupon->starts_at ? $coupon->starts_at->format('d M Y, h:i A') : 'Starts immediately' }}
                                </small>
                                <small class="d-block text-muted">
                                    &rarr; {{ $coupon->expires_at ? $coupon->expires_at->format('d M Y, h:i A') : 'No expiry' }}
                                </small>
                            </td>

                            <td class="num">
                                <span class="usage-figure">
                                    {{ number_format($used) }}
                                    <span class="usage-sep">/</span>
                                    @if ($limit)
                                        {{ number_format($limit) }}
                                    @else
                                        <span title="No overall limit">&infin;</span>
                                    @endif
                                </span>

                                @if ($limit)
                                    <span class="usage-meter"><span style="width: {{ $usedPct }}%"></span></span>
                                @endif

                                <small class="d-block text-muted">
                                    {{ $coupon->per_user_limit
                                        ? $coupon->per_user_limit . ' per customer'
                                        : 'Unlimited per customer' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge {{ $coupon->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $coupon->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            @if ($hasActions)
                                <td>
                                    <div class="action-group">
                                        @can('coupons.edit')
                                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}"
                                               class="btn btn-icon btn-soft-warning" title="Edit coupon">
                                                <i class="feather-edit-2"></i>
                                            </a>
                                        @endcan

                                        @can('coupons.delete')
                                            @if ($coupon->usages_count > 0)
                                                <button type="button" class="btn btn-icon btn-soft" disabled
                                                        title="Redeemed {{ $coupon->usages_count }} times — deactivate it instead">
                                                    <i class="feather-lock"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.coupons.delete', $coupon->id) }}"
                                                      method="POST" class="delete-form"
                                                      data-confirm-title="Delete {{ $coupon->code }}?"
                                                      data-confirm-text="The code stops working right away. This cannot be undone."
                                                      data-confirm-button="Yes, delete it">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-soft-danger"
                                                            title="Delete coupon">
                                                        <i class="feather-trash-2"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>

                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}">
                                <x-empty-state icon="feather-gift" title="No coupons match this filter"
                                               message="Clear the filters, or create a code customers can use at checkout." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($coupons->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $coupons->firstItem() }}&ndash;{{ $coupons->lastItem() }}
                    of {{ $coupons->total() }}</p>
                {{ $coupons->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('styles')
<style>
    .code-chip {
        display: inline-block;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Courier New", monospace;
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: .05em;
        padding: 4px 10px;
        border-radius: var(--ar-radius-xs);
        background: var(--ar-primary-soft);
        color: var(--ar-primary);
        border: 1px solid var(--ar-line-soft);
    }

    .d-block-badge {
        display: inline-block;
        margin-top: 5px;
    }

    .usage-figure {
        font-variant-numeric: tabular-nums;
        font-weight: 650;
        color: var(--ar-ink);
    }

    .usage-figure .usage-sep { color: var(--ar-faint); margin: 0 2px; }

    .usage-meter {
        display: block;
        width: 100%;
        max-width: 110px;
        height: 5px;
        margin: 6px 0 4px auto;
        border-radius: 999px;
        background: var(--ar-surface-3);
        overflow: hidden;
    }

    .usage-meter > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: var(--ar-primary);
    }
</style>
@endpush
