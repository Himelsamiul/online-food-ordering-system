@extends('backend.master')

@section('title', 'POS Sales')

@section('content')

@php
    $canInvoice = \Illuminate\Support\Facades\Gate::allows('pos.view');
    $colSpan    = 7 + ($canInvoice ? 1 : 0);

    $typeLabels = ['dine_in' => 'Dine-in', 'takeaway' => 'Takeaway', 'delivery' => 'Delivery'];
    $typeTones  = ['dine_in' => 'bg-primary', 'takeaway' => 'bg-info', 'delivery' => 'bg-warning'];
@endphp

<div class="container-fluid">

    <x-page-header
        title="POS Sales"
        subtitle="Every counter sale, with the receipt behind each invoice number."
        icon="feather-file-text"
        :breadcrumb="['POS Billing' => null, 'Sales History' => null]">
        @can('pos.create')
            <a href="{{ route('admin.pos.index') }}" class="btn btn-primary">
                <i class="feather-plus"></i> New Sale
            </a>
        @endcan
    </x-page-header>

    <div class="stat-grid">
        <x-stat-card label="Sales" :value="number_format($summary['count'])"
                     icon="feather-shopping-cart" tone="primary"
                     foot="matching the current filter" />

        <x-stat-card label="Collected" :value="number_format($summary['total'], 2)"
                     prefix="৳" icon="feather-dollar-sign" tone="success"
                     foot="matching the current filter" />
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.pos.sales') }}" class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="filter-label" for="invoice_no">Invoice No</label>
                <input type="text" name="invoice_no" id="invoice_no" value="{{ request('invoice_no') }}"
                       class="form-control" placeholder="INV-…">
            </div>
            <div class="col-md-2">
                <label class="filter-label" for="order_type">Type</label>
                <select name="order_type" id="order_type" class="form-select">
                    <option value="">All</option>
                    @foreach ($typeLabels as $k => $v)
                        <option value="{{ $k }}" {{ request('order_type') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label" for="payment_method">Payment</label>
                <select name="payment_method" id="payment_method" class="form-select">
                    <option value="">All</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="filter-label" for="from_date">From</label>
                <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="filter-label" for="to_date">To</label>
                <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" class="form-control">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-primary"><i class="feather-filter"></i> Filter</button>
                <a href="{{ route('admin.pos.sales') }}" class="btn btn-soft">Reset</a>
            </div>
        </div>
        <p class="foot-note mt-2 mb-0">A date range is applied only when both From and To are set.</p>
    </form>

    <div class="card">
        <div class="card-header">
            <h5>Sales History</h5>
            <span class="text-muted fs-13">{{ number_format($sales->total()) }} invoices</span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th class="num">Items</th>
                        <th>Payment</th>
                        <th class="num">Total</th>
                        @can('pos.view')
                            <th class="text-end">Action</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>
                                <div class="cell-media">
                                    <div>
                                        <p class="cell-title">{{ $sale->invoice_no }}</p>
                                        <p class="cell-sub">{{ $sale->cashier->name ?? 'Unknown cashier' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap">{{ $sale->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <span class="badge {{ $typeTones[$sale->order_type] ?? 'bg-secondary' }}">
                                    {{ $typeLabels[$sale->order_type] ?? ucfirst(str_replace('_', ' ', $sale->order_type)) }}
                                </span>
                            </td>
                            <td>
                                <div class="cell-media">
                                    <div>
                                        <p class="cell-title">
                                            {{ $sale->customer_name ?: ($sale->table_no ? 'Table ' . $sale->table_no : 'Walk-in') }}
                                        </p>
                                        @if ($sale->phone)
                                            <p class="cell-sub">{{ $sale->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="num">{{ $sale->items_count ?? $sale->items->count() }}</td>
                            <td>
                                <span class="chip">
                                    <i class="{{ $sale->payment_method === 'cash' ? 'feather-dollar-sign' : 'feather-credit-card' }}"></i>
                                    {{ ucfirst($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="num fw-650">৳{{ number_format($sale->total, 2) }}</td>
                            @can('pos.view')
                                <td class="text-end">
                                    <div class="action-group justify-content-end">
                                        <a href="{{ route('admin.pos.invoice', $sale->id) }}"
                                           class="btn btn-soft-primary btn-sm" title="Open invoice">
                                            <i class="feather-file-text"></i> Invoice
                                        </a>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $colSpan }}">
                                <x-empty-state icon="feather-inbox"
                                               title="No sales match this filter"
                                               message="Widen the date range or clear the filters to see more." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }}
                    of {{ number_format($sales->total()) }}</p>
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
