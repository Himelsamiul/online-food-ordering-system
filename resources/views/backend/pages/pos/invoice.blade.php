@extends('backend.master')

@section('content')

<style>
    .receipt {
        max-width: 380px;
        margin: 0 auto;
        background: #fff;
        font-family: 'Courier New', monospace;
        color: #111;
    }
    .receipt h3 { letter-spacing: 1px; }
    .receipt table { width: 100%; font-size: 13px; }
    .receipt .muted { color: #555; font-size: 12px; }
    .receipt .totals td { padding: 1px 0; }
    .dashed { border-top: 1px dashed #999; margin: 8px 0; }

    @media print {
        .nxl-navigation, .nxl-header, .no-print, .footer-wrapper { display: none !important; }
        .nxl-container, .nxl-content { margin: 0 !important; padding: 0 !important; }
        body { background: #fff !important; }
        .receipt { max-width: 100%; }
    }
</style>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width:380px;margin:0 auto;">
        <a href="{{ route('admin.pos.index') }}" class="btn btn-outline-secondary btn-sm">＋ New Sale</a>
        <div>
            <a href="{{ route('admin.pos.sales') }}" class="btn btn-outline-primary btn-sm">History</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="feather-printer"></i> Print</button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="receipt">

                <div class="text-center">
                    <h3 class="mb-0 fw-bold">My Restaurant</h3>
                    <div class="muted">Cash Memo / Invoice</div>
                </div>

                <div class="dashed"></div>

                <table>
                    <tr><td>Invoice</td><td class="text-end fw-bold">{{ $sale->invoice_no }}</td></tr>
                    <tr><td>Date</td><td class="text-end">{{ $sale->created_at->format('d M Y, h:i A') }}</td></tr>
                    <tr><td>Type</td><td class="text-end text-capitalize">{{ str_replace('_', '-', $sale->order_type) }}</td></tr>
                    @if($sale->table_no)
                        <tr><td>Table</td><td class="text-end">{{ $sale->table_no }}</td></tr>
                    @endif
                    @if($sale->customer_name)
                        <tr><td>Customer</td><td class="text-end">{{ $sale->customer_name }}</td></tr>
                    @endif
                    @if($sale->phone)
                        <tr><td>Phone</td><td class="text-end">{{ $sale->phone }}</td></tr>
                    @endif
                    @if($sale->address)
                        <tr><td>Address</td><td class="text-end">{{ $sale->address }}</td></tr>
                    @endif
                    @if($sale->cashier)
                        <tr><td>Cashier</td><td class="text-end">{{ $sale->cashier->name }}</td></tr>
                    @endif
                </table>

                <div class="dashed"></div>

                <table>
                    <thead>
                        <tr class="muted">
                            <th class="text-start">Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            <tr>
                                <td class="text-start">{{ $item->name }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="dashed"></div>

                <table class="totals">
                    <tr><td>Subtotal</td><td class="text-end">৳{{ number_format($sale->subtotal, 2) }}</td></tr>
                    @if($sale->discount_amount > 0)
                        <tr>
                            <td>Discount @if($sale->discount_type === 'percent')({{ rtrim(rtrim($sale->discount_value, '0'), '.') }}%)@endif</td>
                            <td class="text-end">-৳{{ number_format($sale->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($sale->tax_amount > 0)
                        <tr><td>VAT ({{ rtrim(rtrim($sale->tax_rate, '0'), '.') }}%)</td><td class="text-end">৳{{ number_format($sale->tax_amount, 2) }}</td></tr>
                    @endif
                    @if($sale->service_charge_amount > 0)
                        <tr><td>Service ({{ rtrim(rtrim($sale->service_charge_rate, '0'), '.') }}%)</td><td class="text-end">৳{{ number_format($sale->service_charge_amount, 2) }}</td></tr>
                    @endif
                    <tr class="fw-bold" style="font-size:16px;">
                        <td class="pt-1 border-top">TOTAL</td>
                        <td class="text-end pt-1 border-top">৳{{ number_format($sale->total, 2) }}</td>
                    </tr>
                </table>

                <div class="dashed"></div>

                <table>
                    <tr><td>Payment</td><td class="text-end text-uppercase">{{ $sale->payment_method }}</td></tr>
                    @if($sale->payment_method === 'cash')
                        <tr><td>Received</td><td class="text-end">৳{{ number_format($sale->paid_amount, 2) }}</td></tr>
                        <tr><td>Change</td><td class="text-end">৳{{ number_format($sale->change_amount, 2) }}</td></tr>
                    @endif
                </table>

                @if($sale->note)
                    <div class="dashed"></div>
                    <div class="muted">Note: {{ $sale->note }}</div>
                @endif

                <div class="dashed"></div>
                <div class="text-center muted">Thank you! Please come again 🙏</div>

            </div>
        </div>
    </div>
</div>

@endsection
