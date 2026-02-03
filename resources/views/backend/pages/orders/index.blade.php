@extends('backend.master')

@section('content')

{{-- ================= Flatpickr CSS ================= --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">

<div class="container-fluid">

    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-bold">Orders</h4>
        </div>
    </div>

    {{-- ================= FILTER SECTION ================= --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-2">
                        <label class="form-label">Order No</label>
                        <input type="text"
                               name="order_number"
                               value="{{ request('order_number') }}"
                               class="form-control"
                               placeholder="ORD-XXXX">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <select name="customer_name" class="form-select">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->name }}"
                                    {{ request('customer_name') == $c->name ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Phone</label>
                        <input type="text"
                               name="phone"
                               value="{{ request('phone') }}"
                               class="form-control"
                               placeholder="01XXXXXXXXX">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All</option>
                            <option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>
                                Paid
                            </option>
                            <option value="pending" {{ request('payment_status')=='pending'?'selected':'' }}>
                                Pending
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="text"
                               id="from_date"
                               name="from_date"
                               value="{{ request('from_date') }}"
                               class="form-control"
                               placeholder="YYYY-MM-DD">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="text"
                               id="to_date"
                               name="to_date"
                               value="{{ request('to_date') }}"
                               class="form-control"
                               placeholder="YYYY-MM-DD">
                    </div>

                    <div class="col-md-12 d-flex gap-2">
                        <button class="btn btn-primary">🔍 Search</button>
                        <a href="{{ route('admin.orders.index') }}"
                           class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ================= ORDERS TABLE ================= --}}
    <div class="card">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Order No</th>
                    <th>Transaction No</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Total (৳)</th>
                    <th>Payment</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody>
                @forelse($orders as $key => $order)
                    <tr>
                        <td>{{ $orders->firstItem() + $key }}</td>
                        <td class="fw-semibold">{{ $order->order_number }}</td>
                        <td class="text-muted">
                            {{ $order->transaction_number ?? '-' }}
                        </td>
                        <td>{{ $order->name }}</td>
                        <td>{{ $order->phone }}</td>
                        <td>{{ $order->address }}</td>

                        <td>{{ number_format($order->total_amount, 2) }}</td>

                        <td>
                            <span class="badge bg-info text-dark">
                                {{ strtoupper($order->payment_method) }}
                            </span>
                        </td>

                        <td>
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                {{ ucfirst($order->order_status) }}
                            </span>
                        </td>

                        <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>

                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}"
                               class="btn btn-sm btn-primary">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted">
                            No orders found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>

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
