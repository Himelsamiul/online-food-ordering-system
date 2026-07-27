@extends('backend.master')
@section('title', 'Edit Coupon')

@section('content')
<div class="container-fluid">

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
    @endphp

    <x-page-header
        title="Edit Coupon"
        subtitle="Changes apply to future checkouts only — orders already placed keep the discount they were given."
        icon="feather-gift"
        :breadcrumb="['Marketing' => null, 'Coupons' => route('admin.coupons.index'), $coupon->code => null]">
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to list
        </a>
    </x-page-header>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Coupon Details</h5>
                    <span class="code-chip">{{ $coupon->code }}</span>
                </div>
                <div class="card-body">

                    @if ($coupon->used_count > 0)
                        <div class="alert alert-info">
                            This coupon has been redeemed <strong>{{ number_format($coupon->used_count) }}</strong>
                            {{ $coupon->used_count === 1 ? 'time' : 'times' }}. Past orders keep the discount they
                            were given — editing only affects future use.
                        </div>
                    @endif

                    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @include('backend.pages.coupons._form', ['coupon' => $coupon])

                        <hr class="hr-soft">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Update Coupon
                            </button>
                            <a href="{{ route('admin.coupons.index') }}" class="btn btn-soft">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card coupon-aside">
                <div class="card-header">
                    <h5>Current Setup</h5>
                </div>
                <div class="card-body">

                    <p class="section-title">Offer</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Discount</span>
                            <span class="kv-val">{{ $coupon->offer_label }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Type</span>
                            <span class="kv-val">
                                <span class="badge {{ $coupon->type === 'percent' ? 'bg-info' : 'bg-primary' }}">
                                    {{ $coupon->type === 'percent' ? 'Percentage' : 'Fixed amount' }}
                                </span>
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Minimum order</span>
                            <span class="kv-val">
                                {{ $coupon->min_order_amount
                                    ? '৳' . number_format($coupon->min_order_amount, 2)
                                    : 'None' }}
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Discount cap</span>
                            <span class="kv-val">
                                {{ $coupon->type === 'percent' && $coupon->max_discount
                                    ? '৳' . number_format($coupon->max_discount, 2)
                                    : 'None' }}
                            </span>
                        </div>
                    </div>

                    <hr class="hr-soft">

                    <p class="section-title">Window</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Right now</span>
                            <span class="kv-val">
                                <span class="status-pill {{ $windowTone }}">{{ $windowLabel }}</span>
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Starts</span>
                            <span class="kv-val">
                                {{ $coupon->starts_at ? $coupon->starts_at->format('d M Y, h:i A') : 'Immediately' }}
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Expires</span>
                            <span class="kv-val">
                                {{ $coupon->expires_at ? $coupon->expires_at->format('d M Y, h:i A') : 'No expiry' }}
                            </span>
                        </div>
                    </div>

                    <hr class="hr-soft">

                    <p class="section-title">Usage</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Redeemed</span>
                            <span class="kv-val">
                                {{ number_format((int) $coupon->used_count) }}
                                @if ($coupon->usage_limit)
                                    of {{ number_format($coupon->usage_limit) }}
                                @else
                                    times
                                @endif
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Total limit</span>
                            <span class="kv-val">
                                {{ $coupon->usage_limit ? number_format($coupon->usage_limit) : 'Unlimited' }}
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Per customer</span>
                            <span class="kv-val">
                                {{ $coupon->per_user_limit ? number_format($coupon->per_user_limit) : 'Unlimited' }}
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Created</span>
                            <span class="kv-val">{{ $coupon->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Last updated</span>
                            <span class="kv-val">{{ $coupon->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

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

    @media (min-width: 992px) {
        .coupon-aside {
            position: sticky;
            top: 92px;
        }
    }
</style>
@endpush
