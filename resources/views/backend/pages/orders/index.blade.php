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
                        <input type="text" name="order_number"
                               value="{{ request('order_number') }}"
                               class="form-control" placeholder="ORD-XXXX">
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
                        <input type="text" name="phone"
                               value="{{ request('phone') }}"
                               class="form-control" placeholder="01XXXXXXXXX">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="">All</option>
                            <option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>Paid</option>
                            <option value="pending" {{ request('payment_status')=='pending'?'selected':'' }}>Pending</option>
                        </select>
                    </div>

                    <div class="col-md-2">
    <label class="form-label">Order Status</label>
    <select name="order_status" class="form-select">
        <option value="">All</option>
        <option value="pending" {{ request('order_status')=='pending'?'selected':'' }}>Pending</option>
        <option value="cooking" {{ request('order_status')=='cooking'?'selected':'' }}>Cooking</option>
        <option value="out_for_delivery" {{ request('order_status')=='out_for_delivery'?'selected':'' }}>
            Out for Delivery
        </option>
        <option value="delivered" {{ request('order_status')=='delivered'?'selected':'' }}>Delivered</option>
        <option value="cancelled" {{ request('order_status')=='cancelled'?'selected':'' }}>Cancelled</option>
    </select>
</div>



{{-- Delivery Man --}}
<div class="col-md-3">
    <label class="form-label">Delivery Man</label>
    <select name="delivery_man_id" class="form-select">
        <option value="">All Delivery Men</option>
        @foreach($deliveryMen as $man)
            <option value="{{ $man->id }}"
                {{ request('delivery_man_id') == $man->id ? 'selected' : '' }}>
                {{ $man->name }}
            </option>
        @endforeach
    </select>
</div>




                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="text" id="from_date" name="from_date"
                               value="{{ request('from_date') }}"
                               class="form-control" placeholder="YYYY-MM-DD">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="text" id="to_date" name="to_date"
                               value="{{ request('to_date') }}"
                               class="form-control" placeholder="YYYY-MM-DD">
                    </div>

                    <div class="col-md-12 d-flex gap-2">
                        <button class="btn btn-primary">🔍 Search</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Reset</a>
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
                    <th>Delivery Man</th>
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
                        <td class="text-muted">{{ $order->transaction_number ?? '-' }}</td>
                        <td>{{ $order->name }}</td>
                        <td>
    @if($order->deliveryRun && $order->deliveryRun->deliveryMan)
        <span class="fw-semibold text-primary">
            {{ $order->deliveryRun->deliveryMan->name }}
        </span>
    @else
        <span class="text-muted">Not Assigned</span>
    @endif
</td>

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
                            @if($order->order_status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($order->order_status === 'cooking')
                                <span class="badge bg-info text-dark">Cooking</span>
                            @elseif($order->order_status === 'out_for_delivery')
                                <span class="badge bg-primary">Out for Delivery</span>
                            @elseif($order->order_status === 'delivered')
                                <span class="badge bg-success">Delivered</span>
                            @else
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </td>

                        <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>

                        {{-- ACTION --}}
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center gap-2">

                                {{-- Status change (ONLY if not assigned to delivery run) --}}
                                @if(is_null($order->delivery_run_id))
                                    <button type="button"
                                            class="btn btn-light action-btn action-dot"
                                            data-order-id="{{ $order->id }}"
                                            title="Change Status">
                                        &#8942;
                                    </button>
                                @endif

                                {{-- View --}}
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="btn btn-light action-icon"
                                   title="View Order">
                                    👁
                                </a>

                                {{-- Payment (COD only, pending only) --}}
                                @if($order->payment_method === 'cod' && $order->payment_status === 'pending')
                                    <form action="{{ route('admin.orders.payment.paid', $order->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Confirm cash payment received?')">
                                        @csrf
                                        @method('PATCH')

                                        <button class="btn btn-light action-icon"
                                                title="Mark Payment as Paid">
                                            💰
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted">No orders found</td>
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

{{-- ================= STATUS MODAL ================= --}}
<div id="statusModal" class="custom-modal d-none">
    <div class="custom-modal-content">
        <h5 class="mb-3">Update Order Status</h5>

        <form id="statusForm" method="POST">
            @csrf
            @method('PATCH')

            <input type="hidden" name="order_status" id="selectedStatus">

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-warning status-option" data-status="pending">Pending</button>
                <button type="button" class="btn btn-outline-info status-option" data-status="cooking">Cooking</button>
                <button type="button" class="btn btn-outline-danger status-option" data-status="cancelled">Cancelled</button>
            </div>

            <div class="mt-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" id="closeModal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= CSS ================= --}}
<style>
.action-icon {
    font-size: 18px;
    padding: 6px 10px;
    border-radius: 8px;
    line-height: 1;
}
.action-dot {
    font-size: 30px;
    line-height: 1;
    padding: 6px 10px;
    border-radius: 8px;
}
.custom-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.custom-modal-content {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    width: 320px;
}
</style>

{{-- ================= JS ================= --}}
<script>
const modal = document.getElementById('statusModal');
const closeBtn = document.getElementById('closeModal');
const statusForm = document.getElementById('statusForm');
const statusInput = document.getElementById('selectedStatus');

document.querySelectorAll('.action-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        statusForm.action = `/admin/orders/${btn.dataset.orderId}/status`;
        modal.classList.remove('d-none');
    });
});

document.querySelectorAll('.status-option').forEach(btn => {
    btn.addEventListener('click', () => {
        statusInput.value = btn.dataset.status;
        document.querySelectorAll('.status-option').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

closeBtn.addEventListener('click', () => modal.classList.add('d-none'));
modal.addEventListener('click', e => e.target === modal && modal.classList.add('d-none'));
</script>

{{-- ================= Flatpickr JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr("#from_date",{dateFormat:"Y-m-d",maxDate:"today"});
flatpickr("#to_date",{dateFormat:"Y-m-d",maxDate:"today"});
</script>

@endsection
