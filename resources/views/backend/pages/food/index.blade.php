@extends('backend.master')
@section('title', 'Foods')

@section('content')
<div class="container-fluid">

    @php
        /**
         * The Actions column holds nothing but edit/delete controls, so an
         * admin with neither permission never gets the column at all. The
         * item name itself links to the detail page, so read access to a
         * single food does not depend on that column being drawn.
         */
        $canEditFood   = auth()->user()?->can('foods.edit') ?? false;
        $canDeleteFood = auth()->user()?->can('foods.delete') ?? false;
        $showActions   = $canEditFood || $canDeleteFood;
        $colCount      = $showActions ? 9 : 8;

        // Stock health for the items on this page.
        $pageItems     = collect($foods->items());
        $outOfStock    = $pageItems->filter(fn ($f) => (int) $f->quantity <= 0)->count();
        $lowStock      = $pageItems->filter(function ($f) {
            $alert = (int) ($f->low_stock_alert ?? 0);
            return (int) $f->quantity > 0 && $alert > 0 && (int) $f->quantity <= $alert;
        })->count();
    @endphp

    <x-page-header
        title="Foods"
        subtitle="Everything currently on the menu, with its pricing, stock level and storefront placement."
        icon="feather-shopping-bag"
        :breadcrumb="['Catalog' => null, 'Foods' => null]">

        <a href="{{ route('admin.foods.inactive') }}" class="btn btn-soft">
            <i class="feather-archive"></i> Inactive Foods
        </a>

        @can('foods.create')
            <a href="{{ route('admin.foods.create') }}" class="btn btn-primary">
                <i class="feather-plus"></i> Add Food
            </a>
        @endcan
    </x-page-header>

    <div class="stat-grid">
        <x-stat-card label="Active Foods" :value="$foods->total()" icon="feather-shopping-bag"
                     tone="primary" foot="matching the current filter" />
        <x-stat-card label="Low Stock" :value="$lowStock" icon="feather-alert-triangle"
                     tone="warning" foot="at or below the alert level, on this page" />
        <x-stat-card label="Out of Stock" :value="$outOfStock" icon="feather-slash"
                     tone="danger" foot="nothing left to sell, on this page" />
    </div>

    {{-- ================= FILTER ================= --}}
    <form method="GET" action="{{ route('admin.foods.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="filter-label" for="filter_name">Food Name</label>
                <input type="text" id="filter_name" name="name"
                       value="{{ request('name') }}"
                       class="form-control"
                       placeholder="Search by name">
            </div>

            <div class="col-md-3">
                <label class="filter-label" for="filter_subcategory">Subcategory</label>
                <select name="subcategory_id" id="filter_subcategory" class="form-select">
                    <option value="">All Subcategories</option>
                    @foreach ($subcategories as $sub)
                        <option value="{{ $sub->id }}"
                            {{ request('subcategory_id') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="from_date">From</label>
                <input type="date" id="from_date" name="from_date"
                       value="{{ request('from_date') }}"
                       max="{{ date('Y-m-d') }}"
                       class="form-control date-picker">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="to_date">To</label>
                <input type="date" id="to_date" name="to_date"
                       value="{{ request('to_date') }}"
                       max="{{ date('Y-m-d') }}"
                       class="form-control date-picker">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="feather-filter"></i> Filter
                </button>
                <a href="{{ route('admin.foods.index') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h5>Active Foods</h5>
            <span class="text-muted fs-13">
                {{ $foods->total() }} {{ $foods->total() === 1 ? 'item' : 'items' }}
                &middot; both date fields are needed for the date filter to apply
            </span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th class="num">Price (BDT)</th>
                        <th class="num">Discount</th>
                        <th class="num">Final (BDT)</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Highlights</th>
                        @canany(['foods.edit', 'foods.delete'])
                            <th>Actions</th>
                        @endcanany
                    </tr>
                </thead>

                <tbody>
                    @forelse ($foods as $food)

                        @php
                            $price           = (float) $food->price;
                            $discountPercent = (float) ($food->discount ?? 0);
                            $discountAmount  = ($price * $discountPercent) / 100;
                            $finalPrice      = $price - $discountAmount;

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

                            $hasOrders = $food->orderItems->count() > 0;
                            $initials  = mb_strtoupper(mb_substr(trim((string) $food->name), 0, 2));
                        @endphp

                        <tr>
                            <td>
                                <div class="cell-media">
                                    @if ($food->image)
                                        <img src="{{ asset('storage/' . $food->image) }}"
                                             alt="{{ $food->name }}"
                                             style="width:44px;height:44px;object-fit:cover;border-radius:10px">
                                    @else
                                        <span class="avatar-initials sm">{{ $initials !== '' ? $initials : '?' }}</span>
                                    @endif

                                    <div>
                                        <p class="cell-title">
                                            <a href="{{ route('admin.foods.show', $food->id) }}"
                                               class="text-ink" style="text-decoration:none">
                                                {{ $food->name }}
                                            </a>
                                        </p>
                                        <p class="cell-sub">{{ $food->sku }}</p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <p class="cell-title">{{ $food->subcategory->category->name ?? 'N/A' }}</p>
                                <p class="cell-sub">{{ $food->subcategory->name ?? 'N/A' }}</p>
                            </td>

                            <td class="num">{{ number_format($price, 2) }}</td>

                            <td class="num">
                                @if ($discountPercent > 0)
                                    {{ rtrim(rtrim(number_format($discountPercent, 2), '0'), '.') }}%
                                    <p class="cell-sub">&minus;{{ number_format($discountAmount, 2) }}</p>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>

                            <td class="num fw-650">{{ number_format($finalPrice, 2) }}</td>

                            <td>
                                <span class="status-pill {{ $stockClass }}">{{ $stockLabel }}</span>
                                <p class="cell-sub">
                                    {{ $qty }} {{ $food->unit->name ?? 'units' }}
                                    @if ($alert > 0)
                                        &middot; alert at {{ $alert }}
                                    @endif
                                </p>
                            </td>

                            <td>
                                <span class="badge {{ $food->status ? 'bg-success' : 'bg-danger' }}">
                                    {{ $food->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div class="chip-row">
                                    @if ($food->is_featured)
                                        <span class="chip active"><i class="feather-star"></i> Featured</span>
                                    @endif

                                    @if ($food->is_popular)
                                        <span class="chip active"><i class="feather-trending-up"></i> Popular</span>
                                    @endif

                                    @if (!$food->is_featured && !$food->is_popular)
                                        <span class="text-muted fs-13">&mdash;</span>
                                    @endif
                                </div>
                            </td>

                            @canany(['foods.edit', 'foods.delete'])
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('admin.foods.show', $food->id) }}"
                                           class="btn btn-icon btn-soft-info" title="View details">
                                            <i class="feather-eye"></i>
                                        </a>

                                        @can('foods.edit')
                                            <a href="{{ route('admin.foods.edit', $food->id) }}"
                                               class="btn btn-icon btn-soft-warning" title="Edit food">
                                                <i class="feather-edit-2"></i>
                                            </a>
                                        @endcan

                                        @can('foods.delete')
                                            @if ($hasOrders)
                                                <button type="button"
                                                        class="btn btn-icon btn-soft"
                                                        data-ar-toggle="#orders-{{ $food->id }}"
                                                        title="Already ordered — cannot be deleted. Show the orders.">
                                                    <i class="feather-lock"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.foods.delete', $food->id) }}"
                                                      method="POST" class="delete-form"
                                                      data-confirm-title="Delete {{ $food->name }}?"
                                                      data-confirm-text="The item and its image are removed from the menu."
                                                      data-confirm-button="Yes, delete it">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-soft-danger"
                                                            title="Delete food">
                                                        <i class="feather-trash-2"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            @endcanany
                        </tr>

                        {{-- Why this item cannot be deleted: the orders it already appears on. --}}
                        @can('foods.delete')
                            @if ($hasOrders)
                                <tr id="orders-{{ $food->id }}" hidden>
                                    <td colspan="{{ $colCount }}">
                                        <p class="section-title">Ordered by customers</p>
                                        <div class="kv-list">
                                            @foreach ($food->orderItems as $item)
                                                <div class="kv-row">
                                                    <span class="kv-key">
                                                        {{ $item->order->order_number ?? 'Order #' . $item->order_id }}
                                                        &middot; {{ $item->order->name ?? 'Unknown' }}
                                                        ({{ $item->order->phone ?? '—' }})
                                                    </span>
                                                    <span class="kv-val">Qty {{ $item->quantity }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endcan

                    @empty
                        <tr>
                            <td colspan="{{ $colCount }}">
                                <x-empty-state icon="feather-shopping-bag"
                                               title="No foods match this filter"
                                               message="Clear the filter, or add an item to the menu.">
                                    @can('foods.create')
                                        <a href="{{ route('admin.foods.create') }}" class="btn btn-primary">
                                            <i class="feather-plus"></i> Add Food
                                        </a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($foods->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $foods->firstItem() }}&ndash;{{ $foods->lastItem() }}
                    of {{ $foods->total() }}</p>
                {{ $foods->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
