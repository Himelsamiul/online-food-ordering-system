@extends('backend.master')

@section('content')
<div class="container-fluid">

    <h4 class="fw-bold mb-4">Create Delivery Run</h4>

    <form action="{{ route('admin.delivery-runs.store') }}" method="POST">
        @csrf

        {{-- ================= ORDER SELECT (DROPDOWN) ================= --}}
        <div class="mb-4">
            <label class="form-label fw-semibold">
                Select Order (Max 5)
            </label>

            <select id="order_select" class="form-select">
                <option value="">-- Select Order --</option>
                @foreach($orders as $order)
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
        </div>

        {{-- ================= ORDER WISE DETAILS ================= --}}
        <div id="orders_container"></div>

        {{-- ================= DELIVERY MAN ================= --}}
        <div class="mb-4">
            <label class="form-label fw-semibold">Delivery Man</label>
            <select name="delivery_man_id" class="form-select" required>
                <option value="">-- Select Delivery Man --</option>
                @foreach($deliveryMen as $man)
                    <option value="{{ $man->id }}">
                        {{ $man->name }} ({{ $man->phone }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= NOTE ================= --}}
        <div class="mb-4">
            <label class="form-label fw-semibold">Note</label>
            <textarea name="note" class="form-control" rows="2"
                placeholder="Optional note for delivery man..."></textarea>
        </div>

        <button class="btn btn-primary px-4">
            Send for Delivery
        </button>
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
                        <td>${item.price}</td>
                        <td>${item.quantity}</td>
                        <td>${item.subtotal}</td>
                    </tr>
                `;
            });

            grandTotal += order.order_total;

            html += `
                <div class="card mb-4">
                    <div class="card-header fw-bold">
                        Order #${order.order_number}
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Customer:</strong> ${order.customer_name}
                            </div>
                            <div class="col-md-4">
                                <strong>Phone:</strong> ${order.phone}
                            </div>
                            <div class="col-md-4">
                                <strong>Address:</strong> ${order.address}
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Food</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>

                        <div class="text-end fw-bold">
                            Order Total: ${order.order_total}
                        </div>
                    </div>
                </div>
            `;
        });

        html += `
            <div class="alert alert-info fw-bold text-end">
                Grand Total: ${grandTotal}
            </div>
        `;

        container.innerHTML = html;
    });
}
</script>
@endsection
