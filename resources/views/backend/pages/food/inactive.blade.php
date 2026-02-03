@extends('backend.master')

@section('content')

{{-- ================= Flatpickr CSS ================= --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

{{-- ================= Filter Styles (SAME) ================= --}}
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
        <h5>Inactive Food List</h5>
        <a href="{{ route('admin.foods.index') }}" class="btn btn-outline-primary">
            ← Active Foods
        </a>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- ================= FILTER (SAME AS INDEX) ================= --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.foods.inactive') }}">
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
                        <a href="{{ route('admin.foods.inactive') }}"
                           class="btn btn-outline-secondary">Reset</a>
                    </div>

                </div>
            </form>
        </div>

        {{-- ================= TABLE (SAME STRUCTURE) ================= --}}
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
                        $discount = $food->discount ?? 0;
                        $discountAmount = ($price * $discount) / 100;
                        $finalPrice = $price - $discountAmount;
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
                        <td>{{ $food->subcategory->category->name ?? '-' }}</td>
                        <td>{{ $food->subcategory->name ?? '-' }}</td>
                        <td>{{ number_format($price,2) }} tk</td>
                        <td>{{ $discount ? $discount.'%' : '-' }}</td>
                        <td class="fw-semibold text-success">
                            {{ number_format($finalPrice,2) }} tk
                        </td>
                        <td>{{ $food->quantity }}</td>
                        <td>{{ $food->unit->name ?? '-' }}</td>

                        <td>
                            <span class="badge bg-secondary">Inactive</span>
                        </td>

                        <td>
                            <form action="{{ route('admin.foods.activate', $food->id) }}"
                                  method="POST">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-success"
                                        onclick="return confirm('Activate this food?')">
                                    Activate
                                </button>
                            </form>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted">
                            No inactive food found
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
</script>

@endsection
fdvyh