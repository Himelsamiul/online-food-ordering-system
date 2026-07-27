@extends('backend.master')
@section('title', 'Create Delivery Run')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Create Delivery Run"
        subtitle="Pick up to five waiting orders, hand them to a rider, and send the run out."
        icon="feather-map"
        :breadcrumb="['Delivery' => null, 'Delivery Runs' => route('admin.delivery-runs.index'), 'Create' => null]">

        <a href="{{ route('admin.delivery-runs.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Delivery Runs
        </a>
    </x-page-header>

    <form action="{{ route('admin.delivery-runs.store') }}" method="POST">
        @csrf

        <div class="row g-3">

            {{-- ================= ORDERS ================= --}}
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Orders</h5>
                        <span class="text-muted fs-13">
                            {{ $orders->count() }} waiting to be assigned
                        </span>
                    </div>

                    <div class="card-body">
                        <label class="filter-label" for="order_select">Select Order (Max 5)</label>

                        <select id="order_select" class="form-select" @disabled($orders->isEmpty())>
                            <option value="">-- Select Order --</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order->id }}">
                                    #{{ $order->order_number }} | {{ $order->name }} | {{ $order->total_amount }}৳
                                </option>
                            @endforeach
                        </select>

                        {{-- hidden inputs for submit --}}
                        <div id="selected_orders_inputs"></div>

                        <small class="text-muted">
                            You can assign maximum 5 orders in one delivery run
                        </small>

                        @if ($orders->isEmpty())
                            <hr class="hr-soft">
                            <x-empty-state icon="feather-inbox"
                                           title="Nothing is waiting for a rider"
                                           message="Only pending or cooking orders that are not already on a run can be assigned." />
                        @endif
                    </div>
                </div>

                {{-- ================= ORDER WISE DETAILS ================= --}}
                <div id="orders_container"></div>
            </div>

            {{-- ================= RIDER & NOTE ================= --}}
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><h5>Assignment</h5></div>

                    <div class="card-body">
                        <label class="filter-label" for="delivery_man_id">Delivery Man</label>
                        <select name="delivery_man_id" id="delivery_man_id" class="form-select" required>
                            <option value="">-- Select Delivery Man --</option>
                            @foreach ($deliveryMen as $man)
                                <option value="{{ $man->id }}" @selected(old('delivery_man_id') == $man->id)>
                                    {{ $man->name }} ({{ $man->phone }})
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_man_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        @if ($deliveryMen->isEmpty())
                            <small class="text-muted">
                                No active delivery man is available. Activate one first.
                            </small>
                        @endif

                        <hr class="hr-soft">

                        <label class="filter-label" for="note">Note</label>
                        <textarea name="note" id="note" class="form-control" rows="3"
                            placeholder="Optional note for delivery man...">{{ old('note') }}</textarea>
                        @error('note')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="feather-send"></i> Send for Delivery
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
let selectedOrders = [];

const orderSelect  = document.getElementById('order_select');
const hiddenInputs = document.getElementById('selected_orders_inputs');
const container    = document.getElementById('orders_container');

orderSelect.addEventListener('change', function () {

    const orderId = this.value;
    if (!orderId) return;

    if (selectedOrders.includes(orderId)) {
        alert('This order is already selected.');
        this.value = '';
        return;
    }

    if (selectedOrders.length >= 5) {
        alert('A delivery man can carry maximum 5 orders.');
        this.value = '';
        return;
    }

    selectedOrders.push(orderId);

    // hidden input for submit
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'order_ids[]';
    input.value = orderId;
    hiddenInputs.appendChild(input);

    loadOrderDetails();

    this.value = '';
});

function loadOrderDetails() {
    fetch('{{ route('admin.delivery-runs.order.details') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            order_ids: selectedOrders
        })
    })
    .then(res => res.json())
    .then(orders => {

        let html = '';
        let grandTotal = 0;

        orders.forEach(order => {

            let rows = '';
            order.items.forEach(item => {
                rows += `
                    <tr>
                        <td>${item.food_name}</td>
                        <td class="num">${item.price}</td>
                        <td class="num">${item.quantity}</td>
                        <td class="num">${item.subtotal}</td>
                    </tr>
                `;
            });

            grandTotal += order.order_total;

            html += `
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Order #${order.order_number}</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <p class="kv-key">Customer</p>
                                <p class="kv-val" style="text-align:left">${order.customer_name}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="kv-key">Phone</p>
                                <p class="kv-val" style="text-align:left">${order.phone}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="kv-key">Address</p>
                                <p class="kv-val" style="text-align:left">${order.address}</p>
                            </div>
                        </div>

                        <div class="table-scroll">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Food</th>
                                        <th class="num">Price</th>
                                        <th class="num">Qty</th>
                                        <th class="num">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows}
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end fw-650 mt-2">
                            Order Total: ${order.order_total}
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
            <div class="alert alert-info fw-650 text-end mt-3">
                Grand Total: ${grandTotal}
            </div>
        `;

        container.innerHTML = html;
    });
}
</script>
@endsection
