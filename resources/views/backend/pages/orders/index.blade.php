@extends('backend.master')

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col">
            <h4 class="fw-bold">Orders</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Order No</th>
                        <th>transaction Number</th>
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
                            <td colspan="10" class="text-center text-muted">
                                No orders found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $orders->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
