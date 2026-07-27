@extends('backend.master')
@section('title', 'Food Details')

@section('content')
<div class="container-fluid">

    @php
        $qty        = (int) $food->quantity;
        $alert      = (int) ($food->low_stock_alert ?? 0);
        $stockClass = 'on';
        $stockLabel = 'In stock';

        if ($qty <= 0) {
            $stockClass = 'off';
            $stockLabel = 'Out of stock';
        } elseif ($alert > 0 && $qty <= $alert) {
            $stockClass = 'wait';
            $stockLabel = 'Low stock';
        }

        $initials = mb_strtoupper(mb_substr(trim((string) $food->name), 0, 2));
    @endphp

    <x-page-header
        title="{{ $food->name }}"
        subtitle="SKU {{ $food->sku }} &middot; {{ $food->subcategory->category->name ?? 'N/A' }} / {{ $food->subcategory->name ?? 'N/A' }}"
        icon="feather-shopping-bag"
        :breadcrumb="['Catalog' => null, 'Foods' => route('admin.foods.index'), $food->name => null]">

        <a href="{{ route('admin.foods.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Foods
        </a>

        @can('foods.edit')
            <a href="{{ route('admin.foods.edit', $food->id) }}" class="btn btn-primary">
                <i class="feather-edit-2"></i> Edit Food
            </a>
        @endcan
    </x-page-header>

    <div class="row g-3">

        {{-- ================= IMAGE + PLACEMENT ================= --}}
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    @if ($food->image)
                        <img src="{{ asset('storage/' . $food->image) }}"
                             alt="{{ $food->name }}"
                             style="width:100%;max-height:280px;object-fit:cover;border-radius:var(--ar-radius-sm)">
                    @else
                        <div style="display:flex;align-items:center;justify-content:center;height:200px;
                                    background:var(--ar-surface-2);border-radius:var(--ar-radius-sm);
                                    border:1px dashed var(--ar-line)">
                            <span class="avatar-initials">{{ $initials !== '' ? $initials : '?' }}</span>
                        </div>
                    @endif

                    <hr class="hr-soft">

                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Status</span>
                            <span class="kv-val">
                                <span class="badge {{ $food->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $food->status ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Stock</span>
                            <span class="kv-val">
                                <span class="status-pill {{ $stockClass }}">{{ $stockLabel }}</span>
                            </span>
                        </div>
                    </div>

                    <hr class="hr-soft">
                    <p class="section-title">Storefront Highlights</p>

                    <div class="chip-row">
                        <span class="chip {{ $food->is_featured ? 'active' : '' }}">
                            <i class="feather-star"></i> Featured
                        </span>
                        <span class="chip {{ $food->is_popular ? 'active' : '' }}">
                            <i class="feather-trending-up"></i> Most Popular
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= DETAIL LISTS ================= --}}
        <div class="col-xl-8">
            <div class="row g-3">

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header"><h5>Pricing</h5></div>
                        <div class="card-body">
                            <div class="kv-list">
                                <div class="kv-row">
                                    <span class="kv-key">Base Price</span>
                                    <span class="kv-val num">BDT {{ number_format($price, 2) }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Discount</span>
                                    <span class="kv-val num">
                                        {{ $discountPercent ? rtrim(rtrim(number_format($discountPercent, 2), '0'), '.') . '%' : '—' }}
                                    </span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Discount Amount</span>
                                    <span class="kv-val num">
                                        {{ $discountAmount > 0 ? '−BDT ' . number_format($discountAmount, 2) : '—' }}
                                    </span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Final Price</span>
                                    <span class="kv-val num">BDT {{ number_format($finalPrice, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header"><h5>Stock</h5></div>
                        <div class="card-body">
                            <div class="kv-list">
                                <div class="kv-row">
                                    <span class="kv-key">Quantity</span>
                                    <span class="kv-val num">{{ $qty }} {{ $food->unit->name ?? '' }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Low Stock Alert</span>
                                    <span class="kv-val num">
                                        {{ $food->low_stock_alert !== null ? $food->low_stock_alert : '—' }}
                                    </span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Unit</span>
                                    <span class="kv-val">{{ $food->unit->name ?? '—' }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Barcode</span>
                                    <span class="kv-val">{{ $food->barcode ?: '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h5>Catalogue</h5></div>
                        <div class="card-body">
                            <div class="kv-list">
                                <div class="kv-row">
                                    <span class="kv-key">Food</span>
                                    <span class="kv-val">{{ $food->name }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">SKU</span>
                                    <span class="kv-val">{{ $food->sku }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Category</span>
                                    <span class="kv-val">{{ $food->subcategory->category->name ?? 'N/A' }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Subcategory</span>
                                    <span class="kv-val">{{ $food->subcategory->name ?? 'N/A' }}</span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Created</span>
                                    <span class="kv-val">
                                        {{ $food->created_at ? $food->created_at->format('d M Y, h:i A') : '—' }}
                                    </span>
                                </div>
                                <div class="kv-row">
                                    <span class="kv-key">Last Updated</span>
                                    <span class="kv-val">
                                        {{ $food->updated_at ? $food->updated_at->format('d M Y, h:i A') : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h5>Description</h5></div>
                        <div class="card-body">
                            @if ($food->description)
                                <p class="mb-0">{{ $food->description }}</p>
                            @else
                                <p class="text-muted mb-0">No description provided.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection
