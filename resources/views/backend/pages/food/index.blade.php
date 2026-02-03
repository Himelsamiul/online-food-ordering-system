@extends('backend.master')

@section('content')

{{-- ================= Flatpickr CSS ================= --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

{{-- ================= Filter Styles ================= --}}
<style>
    .filter-card {
        background: linear-gradient(135deg, rgba(59,130,246,.08), rgba(16,185,129,.08));
        border-radius: 14px;
        padding: 18px;
        margin-bottom: 18px;
    }
    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        border-radius: 10px;
        background: #fff;
    }
    .date-picker { cursor: pointer; }
</style>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Food List</h5>
        <a href="{{ route('admin.foods.create') }}" class="btn btn-primary">
            + Add Food
        </a>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- ================= FILTER ================= --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.foods.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-3">
                        <label class="filter-label">Food Name</label>
                        <input type="text" name="name"
                               value="{{ request('name') }}"
                               class="form-control"
                               placeholder="🔍 Food name">
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label">Subcategory</label>
                        <select name="subcategory_id" class="form-select">
                            <option value="">All Subcategories</option>
                            @foreach($subcategories as $sub)
                                <option value="{{ $sub->id }}"
                                    {{ request('subcategory_id') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="filter-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="1" {{ request('status')==='1'?'selected':'' }}>Active</option>
                            <option value="0" {{ request('status')==='0'?'selected':'' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="filter-label">From</label>
                        <input type="text" id="from_date" name="from_date"
                               value="{{ request('from_date') }}"
                               class="form-control date-picker"
                               placeholder="From date">
                    </div>

                    <div class="col-md-2">
                        <label class="filter-label">To</label>
                        <input type="text" id="to_date" name="to_date"
                               value="{{ request('to_date') }}"
                               class="form-control date-picker"
                               placeholder="To date">
                    </div>

                    <div class="col-md-12 d-flex gap-2 mt-2">
                        <button class="btn btn-primary">🔎 Search</button>
                        <a href="{{ route('admin.foods.index') }}"
                           class="btn btn-outline-secondary">Reset</a>
                    </div>

                </div>
            </form>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Image</th>
                    <th>Food</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Discount Amount</th>
                    <th>Final Price</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th width="160">Action</th>
                </tr>
                </thead>

                <tbody>
                @forelse($foods as $food)

                    @php
                        $price = $food->price;
                        $discountPercent = $food->discount ?? 0;
                        $discountAmount = ($price * $discountPercent) / 100;
                        $finalPrice = $price - $discountAmount;
                        $hasOrders = $food->orderItems->count() > 0;
                    @endphp

                    <tr>
                        <td>
                            @if($food->image)
                                <img src="{{ asset('storage/'.$food->image) }}"
                                     style="height:45px;border-radius:6px">
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $food->name }}</td>
                        <td>{{ $food->sku }}</td>
                        <td>{{ $food->subcategory->category->name ?? 'N/A' }}</td>
                        <td>{{ $food->subcategory->name ?? 'N/A' }}</td>

                        <td>{{ number_format($price,2) }} tk</td>

                        <td>{{ $discountPercent ? $discountPercent.'%' : '-' }}</td>

                        <td class="text-danger">
                            {{ $discountAmount > 0 ? '-'.number_format($discountAmount,2).' tk' : '-' }}
                        </td>

                        <td class="fw-semibold text-success">
                            {{ number_format($finalPrice,2) }} tk
                        </td>

                        <td>{{ $food->quantity }}</td>
                        <td>{{ $food->unit->name ?? '-' }}</td>

                        <td>
                            <span class="badge {{ $food->status ? 'bg-success' : 'bg-danger' }}">
                                {{ $food->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        {{-- ACTION --}}
                        <td>
                            <a href="{{ route('admin.foods.show', $food->id) }}"
                               class="btn btn-sm btn-info">View</a>

                            <a href="{{ route('admin.foods.edit',$food->id) }}"
                               class="btn btn-sm btn-primary">Edit</a>

                            @if($hasOrders)
                                <button type="button"
                                        class="btn btn-sm btn-secondary"
                                        onclick="toggleOrders({{ $food->id }})">
                                    Delete
                                </button>
                            @else
                                <form action="{{ route('admin.foods.delete',$food->id) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this food?')">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    {{-- INLINE ORDER DROPDOWN (CATEGORY STYLE) --}}
                    @if($hasOrders)
                        <tr id="orders-{{ $food->id }}" style="display:none;">
                            <td colspan="13" class="bg-light">
                                <div class="p-3 border rounded">
                                    <strong>Ordered by customers:</strong>
                                    <ul class="mb-0 ps-3 text-muted mt-2">
                                        @foreach($food->orderItems as $item)
                                            <li>
                                                {{ $item->order->name }}
                                                ({{ $item->order->phone }})
                                                — Qty: {{ $item->quantity }}
                                                — {{ $item->order->order_number }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endif

                @empty
                    <tr>
                        <td colspan="13" class="text-center text-muted">
                            No food found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            {{ $foods->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>

{{-- ================= Flatpickr JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const fromPicker = flatpickr("#from_date", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange(_, d){ toPicker.set("minDate", d); }
    });
    const toPicker = flatpickr("#to_date", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange(_, d){ fromPicker.set("maxDate", d); }
    });

    function toggleOrders(id) {
        const row = document.getElementById('orders-' + id);
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
</script>

@endsection
