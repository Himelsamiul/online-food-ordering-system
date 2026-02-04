@extends('backend.master')

@section('content')
<style>

    /* Premium UI Theme */
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
        --soft-bg: #f8fafc;
    }

    .container-fluid { background-color: var(--soft-bg); min-height: 100vh; padding-top: 2rem; }
    
    /* Card Styling */
    .custom-card {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .custom-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }

    /* Form Elements */
    .form-label { font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem; }
    .form-control, .form-select {
        border-radius: 10px; border: 1px solid #e2e8f0; padding: 0.6rem 1rem;
        transition: all 0.2s; font-size: 0.9rem;
    }
    .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

    /* Modern Status Badges */
    .badge-active { background: #dcfce7; color: #15803d; border: 1px solid #7bf0a4; }
    .badge-inactive { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-pill-custom { padding: 5px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }

    /* Table Improvements */
    .table thead th {
        background: #f1f5f9; color: #64748b; font-weight: 700;
        text-transform: uppercase; font-size: 0.75rem; border: none; padding: 15px;
    }
    .table tbody td { vertical-align: middle; padding: 12px 15px; border-color: #f1f5f9; }

    /* Action Buttons */
    .btn-icon {
        width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 10px; transition: 0.2s; border: none; text-decoration: none;
        position: relative;
    }
    .btn-status { background: #fef3c7; color: #d97706; }
    .btn-edit { background: #e0f2fe; color: #0284c7; }
    .btn-delete { background: #ffe4e6; color: #e11d48; }
    .btn-lock { background: #f1f5f9; color: #475569; }

    .order-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        background: #ef4444;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        border-radius: 999px;
        padding: 2px 6px;
        line-height: 1;
    }

    .btn-icon:hover { transform: scale(1.15); filter: brightness(0.9); color: inherit; }

    /* Filter UI */
    .filter-wrapper { background: #fff; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid #e2e8f0; }
    .btn-search { background: var(--primary-gradient); color: white; border: none; border-radius: 10px; font-weight: 600; }
    .btn-reset { background: #f1f5f9; color: #64748b; border: none; border-radius: 10px; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
</style>

<div class="container-fluid">
<div class="row">

{{-- ================= LEFT: CREATE FORM ================= --}}
<div class="col-xl-4">
    <div class="custom-card mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="fw-bold text-dark mb-0">✨ Register Delivery Man</h5>
            <p class="text-muted small">Fill in the credentials to onboard</p>
        </div>
        <div class="card-body px-4 pb-4">
            <form action="{{ route('admin.delivery-men.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">NID Number</label>
                        <input type="text" name="nid_number" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Note</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="photo" class="form-control" required>
                    </div>
                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-search w-100 py-2 shadow">
                            Create Delivery Man
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= RIGHT: LIST ================= --}}
<div class="col-xl-8">
{{-- FILTER BOX --}}
<div class="filter-wrapper shadow-sm">
    <form method="GET" action="{{ route('admin.delivery-men.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search Name</label>
                <input type="text"
                       name="name"
                       value="{{ request('name') }}"
                       class="form-control"
                       placeholder="Type name...">
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select text-muted">
                    <option value="">Select Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="text"
                       name="from_date"
                       value="{{ request('from_date') }}"
                       class="form-control datepicker"
                       placeholder="YYYY-MM-DD">
            </div>

            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="text"
                       name="to_date"
                       value="{{ request('to_date') }}"
                       class="form-control datepicker"
                       placeholder="YYYY-MM-DD">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit"
                        class="btn btn-search flex-grow-1 shadow-sm">
                    Search
                </button>

                <a href="{{ route('admin.delivery-men.index') }}"
                   class="btn-reset border shadow-sm"
                   title="Clear Filters">
                    🔄
                </a>
            </div>
        </div>
    </form>
</div>

<div class="custom-card">
<div class="table-responsive p-0">
<table class="table table-hover mb-0">
<thead>
<tr>
    <th class="ps-4">No.</th>
    <th>Delivery Man</th>
    <th>Phone</th>
    <th>NID & Address</th>
    <th>Note</th>
    <th>Status</th>
    <th class="text-end pe-4">Action</th>
</tr>
</thead>
<tbody>

@forelse($deliveryMen as $key => $man)
@php
    $orders = $man->deliveryRuns->flatMap->orders;
@endphp

<tr>
    <td class="ps-4 text-muted">{{ $deliveryMen->firstItem() + $key }}</td>

    <td>
        <div class="d-flex align-items-center">
            <img src="{{ asset('storage/'.$man->photo) }}" class="rounded-circle border" width="40" height="40" style="object-fit:cover;">
            <div class="ms-3">
                <div class="fw-bold text-dark mb-0">{{ $man->name }}</div>
                <div class="small text-muted">{{ $man->email }}</div>
            </div>
        </div>
    </td>

    <td class="fw-semibold text-secondary">{{ $man->phone }}</td>

    <td class="small">
        {{ $man->nid_number }}
        <div class="small text-muted">{{ $man->address }}</div>
    </td>

    <td class="small">{{ $man->note }}</td>

    <td>
        @if($man->status)
            <span class="badge-pill-custom badge-active">Active</span>
        @else
            <span class="badge-pill-custom badge-inactive">Inactive</span>
        @endif
    </td>

    <td class="text-end pe-4">
        <div class="d-flex justify-content-end gap-2">

            <form action="{{ route('admin.delivery-men.status',$man->id) }}" method="POST">
                @csrf @method('PATCH')
                <button class="btn-icon btn-status">⚡</button>
            </form>

            <a href="{{ route('admin.delivery-men.edit',$man->id) }}" class="btn-icon btn-edit">✏️</a>

            @if($orders->count() > 0)
                <button type="button"
                        class="btn-icon btn-lock"
                        onclick="toggleOrders({{ $man->id }})">
                    🔒
                    <span class="order-badge">{{ $orders->count() }}</span>
                </button>
            @else
                <form action="{{ route('admin.delivery-men.delete',$man->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button class="btn-icon btn-delete">🗑</button>
                </form>
            @endif

        </div>
    </td>
</tr>

@if($orders->count() > 0)
<tr id="orders-row-{{ $man->id }}" style="display:none;">
    <td colspan="7" class="bg-light">
        <div class="p-3">
            <h6 class="fw-bold mb-2">
                📦 Used in Orders ({{ $orders->count() }})
            </h6>

            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->name }}</td>
                            <td>{{ ucfirst($order->order_status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </td>
</tr>
@endif

@empty
<tr>
    <td colspan="7" class="text-center py-5 text-muted">
        No records found.
    </td>
</tr>
@endforelse

</tbody>
</table>
</div>

<div class="card-footer bg-transparent border-0 px-4 py-3">
    {{ $deliveryMen->links('pagination::bootstrap-5') }}
</div>

</div>
</div>

</div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr(".datepicker",{ dateFormat:"Y-m-d", disableMobile:true });

function toggleOrders(id) {
    const row = document.getElementById('orders-row-' + id);
    if (!row) return;
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endsection
