@extends('backend.master')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">POS Sales History</h4>
        <a href="{{ route('admin.pos.index') }}" class="btn btn-primary btn-sm">＋ New Sale</a>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card"><div class="card-body py-3">
                <div class="text-muted small">Sales (filtered)</div>
                <div class="fs-4 fw-bold">{{ $summary['count'] }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body py-3">
                <div class="text-muted small">Total collected</div>
                <div class="fs-4 fw-bold text-success">৳{{ number_format($summary['total'], 2) }}</div>
            </div></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.pos.sales') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Invoice No</label>
                        <input type="text" name="invoice_no" value="{{ request('invoice_no') }}"
                               class="form-control" placeholder="INV-...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="order_type" class="form-select">
                            <option value="">All</option>
                            @foreach(['dine_in' => 'Dine-in', 'takeaway' => 'Takeaway', 'delivery' => 'Delivery'] as $k => $v)
                                <option value="{{ $k }}" {{ request('order_type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment</label>
                        <select name="payment_method" class="form-select">
                            <option value="">All</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-primary w-100">Go</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>Payment</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td class="fw-semibold">{{ $sale->invoice_no }}</td>
                                <td>{{ $sale->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-capitalize">{{ str_replace('_', '-', $sale->order_type) }}</td>
                                <td>{{ $sale->customer_name ?? ($sale->table_no ? 'Table ' . $sale->table_no : '—') }}</td>
                                <td class="text-uppercase">{{ $sale->payment_method }}</td>
                                <td class="text-end fw-bold">৳{{ number_format($sale->total, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.pos.invoice', $sale->id) }}"
                                       class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No sales found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
