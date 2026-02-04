@extends('backend.master')

@section('content')
<div class="container-fluid">

    <div class="mb-3">
        <a href="{{ route('admin.delivery-runs.index') }}"
           class="btn btn-sm btn-secondary">
            ← Back
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-2">🚚 Delivery Run Details</h5>

            <p class="mb-1">
                <strong>Delivery Man:</strong>
                {{ $run->deliveryMan->name ?? 'N/A' }}
            </p>

            <p class="mb-1">
                <strong>Status:</strong>
                {{ ucfirst(str_replace('_',' ', $run->status)) }}
            </p>

            <p class="mb-0">
                <strong>Departed At:</strong>
                {{ $run->departed_at->format('d M Y, h:i A') }}
            </p>
        </div>
    </div>

    {{-- ================= ORDERS DETAILS ================= --}}
    @php $grandTotal = 0; @endphp

    @foreach($orders as $order)
        @php
            $customerTotal = 0;
        @endphp

        <div class="card mb-4">
            <div class="card-header">
                <strong>👤 {{ $order->name }}</strong>
                <span class="text-muted">
                    ({{ $order->phone }})
                </span>
                <span class="text-muted">
                    ({{ $order->address }})
                </span>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Food</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $lineTotal = $item->price * $item->quantity;
                                $customerTotal += $lineTotal;
                            @endphp
                            <tr>
                                <td>{{ $item->food->name ?? 'N/A' }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($lineTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">
                                Customer Total
                            </th>
                            <th>{{ number_format($customerTotal, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @php
            $grandTotal += $customerTotal;
        @endphp
    @endforeach

    {{-- ================= GRAND TOTAL ================= --}}
    <div class="card">
        <div class="card-body text-end">
            <h5 class="fw-bold">
                💰 Grand Total:
                {{ number_format($grandTotal, 2) }}
            </h5>
        </div>
    </div>

</div>
@endsection
