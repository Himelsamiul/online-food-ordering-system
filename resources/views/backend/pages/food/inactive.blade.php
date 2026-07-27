@extends('backend.master')
@section('title', 'Inactive Foods')

@section('content')
<div class="container-fluid">

    @php
        /**
         * The only action on this page is "activate", which needs foods.edit.
         * Without it the column would be empty, so it is not drawn at all —
         * the item name still links through to the detail page.
         */
        $canEditFood = auth()->user()?->can('foods.edit') ?? false;
        $colCount    = $canEditFood ? 9 : 8;
    @endphp

    <x-page-header
        title="Inactive Foods"
        subtitle="Items taken off the menu. Activating one puts it straight back in the storefront."
        icon="feather-archive"
        :breadcrumb="['Catalog' => null, 'Foods' => route('admin.foods.index'), 'Inactive' => null]">

        <a href="{{ route('admin.foods.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Active Foods
        </a>

        @can('foods.create')
            <a href="{{ route('admin.foods.create') }}" class="btn btn-primary">
                <i class="feather-plus"></i> Add Food
            </a>
        @endcan
    </x-page-header>

    {{-- ================= FILTER ================= --}}
    <form method="GET" action="{{ route('admin.foods.inactive') }}" class="filter-card">
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
                <a href="{{ route('admin.foods.inactive') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h5>Inactive Foods</h5>
            <span class="text-muted fs-13">
                {{ $foods->total() }} {{ $foods->total() === 1 ? 'item' : 'items' }} hidden from the storefront
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
                        @can('foods.edit')
                            <th>Actions</th>
                        @endcan
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

                            $initials = mb_strtoupper(mb_substr(trim((string) $food->name), 0, 2));
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
                                <span class="badge bg-secondary">Inactive</span>
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

                            @can('foods.edit')
                                <td>
                                    <div class="action-group">
                                        <form action="{{ route('admin.foods.activate', $food->id) }}"
                                              method="POST" class="delete-form"
                                              data-confirm-title="Activate {{ $food->name }}?"
                                              data-confirm-text="The item goes back on the storefront immediately."
                                              data-confirm-button="Yes, activate">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-soft-success btn-sm">
                                                <i class="feather-check-circle"></i> Activate
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.foods.edit', $food->id) }}"
                                           class="btn btn-icon btn-soft-warning" title="Edit food">
                                            <i class="feather-edit-2"></i>
                                        </a>

                                        <a href="{{ route('admin.foods.show', $food->id) }}"
                                           class="btn btn-icon btn-soft-info" title="View details">
                                            <i class="feather-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            @endcan
                        </tr>

                    @empty
                        <tr>
                            <td colspan="{{ $colCount }}">
                                <x-empty-state icon="feather-archive"
                                               title="No inactive foods"
                                               message="Nothing has been taken off the menu — everything is live.">
                                    <a href="{{ route('admin.foods.index') }}" class="btn btn-soft">
                                        Back to active foods
                                    </a>
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
