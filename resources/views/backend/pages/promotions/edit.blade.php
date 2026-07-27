@extends('backend.master')
@section('title', 'Edit Promo Banner')

@section('content')
<div class="container-fluid">

    @php
        $isFinished  = $promotion->ends_at && $promotion->ends_at->isPast();
        $isScheduled = $promotion->starts_at && $promotion->starts_at->isFuture();

        if ($isFinished) {
            $windowLabel = 'Finished';
            $windowTone  = 'off';
        } elseif ($isScheduled) {
            $windowLabel = 'Scheduled';
            $windowTone  = 'wait';
        } elseif (! $promotion->status) {
            $windowLabel = 'Paused';
            $windowTone  = 'off';
        } else {
            $windowLabel = 'Live';
            $windowTone  = 'on';
        }
    @endphp

    <x-page-header
        title="Edit Promo Banner"
        subtitle="Change the artwork, the schedule, or the coupon this banner advertises."
        icon="feather-image"
        :breadcrumb="['Marketing' => null, 'Promo Banners' => route('admin.promotions.index'), $promotion->title => null]">
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to list
        </a>
    </x-page-header>

    <div class="row g-4">

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Banner Details</h5>
                    <span class="status-pill {{ $windowTone }}">{{ $windowLabel }}</span>
                </div>
                <div class="card-body">

                    <form action="{{ route('admin.promotions.update', $promotion->id) }}"
                          method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @include('backend.pages.promotions._form', ['promotion' => $promotion])

                        <hr class="hr-soft">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Update Banner
                            </button>
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-soft">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card promotion-aside">
                <div class="card-header">
                    <h5>Current Setup</h5>
                </div>
                <div class="card-body">

                    <p class="section-title">Placement</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Right now</span>
                            <span class="kv-val">
                                <span class="status-pill {{ $windowTone }}">{{ $windowLabel }}</span>
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Status</span>
                            <span class="kv-val">
                                <span class="badge {{ $promotion->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $promotion->status ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Sort order</span>
                            <span class="kv-val">{{ $promotion->sort_order }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Link</span>
                            <span class="kv-val">
                                @if (filled($promotion->link_url))
                                    <a href="{{ $promotion->link_url }}" class="cell-link"
                                       target="_blank" rel="noopener noreferrer">
                                        <i class="feather-external-link"></i> Open
                                    </a>
                                @else
                                    None
                                @endif
                            </span>
                        </div>
                    </div>

                    <hr class="hr-soft">

                    <p class="section-title">Schedule</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Starts</span>
                            <span class="kv-val">
                                {{ $promotion->starts_at ? $promotion->starts_at->format('d M Y, h:i A') : 'Immediately' }}
                            </span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Ends</span>
                            <span class="kv-val">
                                {{ $promotion->ends_at ? $promotion->ends_at->format('d M Y, h:i A') : 'No end date' }}
                            </span>
                        </div>
                    </div>

                    <hr class="hr-soft">

                    <p class="section-title">Coupon</p>
                    @if ($promotion->coupon)
                        <div class="kv-list">
                            <div class="kv-row">
                                <span class="kv-key">Code</span>
                                <span class="kv-val"><span class="code-chip">{{ $promotion->coupon->code }}</span></span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Offer</span>
                                <span class="kv-val">{{ $promotion->coupon->offer_label }}</span>
                            </div>
                            <div class="kv-row">
                                <span class="kv-key">Coupon status</span>
                                <span class="kv-val">
                                    <span class="badge {{ $promotion->coupon->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $promotion->coupon->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        @can('coupons.edit')
                            <a href="{{ route('admin.coupons.edit', $promotion->coupon->id) }}"
                               class="btn btn-soft w-100 mt-3">
                                <i class="feather-gift"></i> Edit this coupon
                            </a>
                        @endcan
                    @else
                        <p class="text-muted fs-13 mb-0">
                            This banner is not linked to a coupon, so it only advertises — there is no code to copy.
                        </p>
                    @endif

                    <hr class="hr-soft">

                    <p class="section-title">Record</p>
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Created</span>
                            <span class="kv-val">{{ $promotion->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                        <div class="kv-row">
                            <span class="kv-key">Last updated</span>
                            <span class="kv-val">{{ $promotion->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
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

    .banner-preview {
        display: block;
        width: 100%;
        max-width: 320px;
        aspect-ratio: 3 / 1;
        object-fit: cover;
        border-radius: var(--ar-radius-sm);
        border: 1px solid var(--ar-line);
    }

    .cell-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12.5px;
        color: var(--ar-primary);
        text-decoration: none;
    }

    .cell-link:hover { text-decoration: underline; }

    @media (min-width: 992px) {
        .promotion-aside {
            position: sticky;
            top: 92px;
        }
    }
</style>
@endpush
