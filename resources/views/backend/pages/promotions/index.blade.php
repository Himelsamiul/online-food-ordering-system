@extends('backend.master')
@section('title', 'Promo Banners')

@section('content')
<div class="container-fluid">

    @php
        $canCreate  = auth()->user()?->can('promotions.create') ?? false;
        $canEdit    = auth()->user()?->can('promotions.edit') ?? false;
        $canDelete  = auth()->user()?->can('promotions.delete') ?? false;
        $hasActions = $canEdit || $canDelete;
        $colspan    = $hasActions ? 6 : 5;
    @endphp

    <x-page-header
        title="Promo Banners"
        subtitle="The offer strips on the storefront. Each one can carry a coupon and its own run dates."
        icon="feather-image"
        :breadcrumb="['Marketing' => null, 'Promo Banners' => null]">

        @can('coupons.view')
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-soft">
                <i class="feather-gift"></i> Coupons
            </a>
        @endcan

        @can('promotions.create')
            <button type="button" class="btn btn-primary" data-ar-toggle="#promotion-create">
                <i class="feather-plus"></i> New Banner
            </button>
        @endcan
    </x-page-header>

    {{-- ================= Add banner ================= --}}
    @can('promotions.create')
        <div class="card" id="promotion-create" @if (! $errors->any()) hidden @endif>
            <div class="card-header">
                <h5>Add Promo Banner</h5>
                <span class="text-muted fs-13">Only the title is required</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.promotions.store') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf

                    @include('backend.pages.promotions._form', ['promotion' => null])

                    <hr class="hr-soft">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="feather-plus"></i> Add Banner
                        </button>
                        <button type="button" class="btn btn-soft" data-ar-toggle="#promotion-create">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    {{-- ================= Filters ================= --}}
    <form method="GET" action="{{ route('admin.promotions.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-5">
                <label class="filter-label" for="filter-title">Title</label>
                <input type="text" id="filter-title" name="title" value="{{ request('title') }}"
                       class="form-control" placeholder="Search title">
            </div>

            <div class="col-md-4">
                <label class="filter-label" for="filter-status">Status</label>
                <select name="status" id="filter-status" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="feather-filter"></i> Filter
                </button>
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    {{-- ================= List ================= --}}
    <div class="card">
        <div class="card-header">
            <h5>All Banners</h5>
            <span class="text-muted fs-13">{{ $promotions->total() }} total</span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Banner</th>
                        <th style="width:190px;">Coupon</th>
                        <th style="width:210px;">Schedule</th>
                        <th class="num" style="width:90px;">Order</th>
                        <th style="width:110px;">Status</th>
                        @if ($hasActions)
                            <th style="width:110px;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promotions as $promotion)

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

                        <tr>
                            <td>
                                <div class="cell-media">
                                    @if ($promotion->image)
                                        <img src="{{ asset('storage/' . $promotion->image) }}"
                                             alt="{{ $promotion->title }}" class="banner-thumb">
                                    @else
                                        <span class="thumb-empty"><i class="feather-image"></i></span>
                                    @endif

                                    <div>
                                        <p class="cell-title">{{ $promotion->title }}</p>

                                        @if (filled($promotion->subtitle))
                                            <p class="cell-sub">{{ $promotion->subtitle }}</p>
                                        @else
                                            <p class="cell-sub">No subtitle</p>
                                        @endif

                                        @if (filled($promotion->link_url))
                                            <a href="{{ $promotion->link_url }}" class="cell-link"
                                               target="_blank" rel="noopener noreferrer">
                                                <i class="feather-external-link"></i>
                                                {{ \Illuminate\Support\Str::limit($promotion->link_url, 42) }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if ($promotion->coupon)
                                    <span class="code-chip">{{ $promotion->coupon->code }}</span>
                                    <small class="d-block text-muted mt-1">{{ $promotion->coupon->offer_label }}</small>
                                @else
                                    <span class="text-muted">Not linked</span>
                                @endif
                            </td>

                            <td>
                                <span class="status-pill {{ $windowTone }}">{{ $windowLabel }}</span>
                                <small class="d-block text-muted mt-1">
                                    {{ $promotion->starts_at ? $promotion->starts_at->format('d M Y, h:i A') : 'Starts immediately' }}
                                </small>
                                <small class="d-block text-muted">
                                    &rarr; {{ $promotion->ends_at ? $promotion->ends_at->format('d M Y, h:i A') : 'No end date' }}
                                </small>
                            </td>

                            <td class="num">{{ $promotion->sort_order }}</td>

                            <td>
                                <span class="badge {{ $promotion->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $promotion->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            @if ($hasActions)
                                <td>
                                    <div class="action-group">
                                        @can('promotions.edit')
                                            <a href="{{ route('admin.promotions.edit', $promotion->id) }}"
                                               class="btn btn-icon btn-soft-warning" title="Edit banner">
                                                <i class="feather-edit-2"></i>
                                            </a>
                                        @endcan

                                        @can('promotions.delete')
                                            <form action="{{ route('admin.promotions.delete', $promotion->id) }}"
                                                  method="POST" class="delete-form"
                                                  data-confirm-title="Delete {{ $promotion->title }}?"
                                                  data-confirm-text="The banner and its image are removed. This cannot be undone."
                                                  data-confirm-button="Yes, delete it">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-soft-danger"
                                                        title="Delete banner">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>

                    @empty
                        <tr>
                            <td colspan="{{ $colspan }}">
                                <x-empty-state icon="feather-image" title="No promo banners match this filter"
                                               message="Clear the filters, or add a banner to advertise an offer on the storefront." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($promotions->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $promotions->firstItem() }}&ndash;{{ $promotions->lastItem() }}
                    of {{ $promotions->total() }}</p>
                {{ $promotions->links('pagination::bootstrap-5') }}
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

    .banner-thumb {
        width: 96px;
        height: 54px;
        object-fit: cover;
        border-radius: var(--ar-radius-xs);
        border: 1px solid var(--ar-line);
    }

    .thumb-empty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 96px;
        height: 54px;
        border-radius: var(--ar-radius-xs);
        border: 1px dashed var(--ar-line);
        background: var(--ar-surface-2);
        color: var(--ar-faint);
        font-size: 18px;
    }

    .cell-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-top: 4px;
        font-size: 12px;
        color: var(--ar-primary);
        text-decoration: none;
        word-break: break-all;
    }

    .cell-link:hover { text-decoration: underline; }

    .banner-preview {
        display: block;
        width: 100%;
        max-width: 320px;
        aspect-ratio: 3 / 1;
        object-fit: cover;
        border-radius: var(--ar-radius-sm);
        border: 1px solid var(--ar-line);
    }
</style>
@endpush
