@extends('backend.master')

@section('title', 'Invoice ' . $sale->invoice_no)

@section('content')

<style>
    /* ---------------------------------------------------------------
       Receipt. On screen it follows the panel theme; on paper it is
       forced back to black on white so a dark-mode admin still prints
       something legible.
       --------------------------------------------------------------- */

    .receipt-card {
        max-width: 430px;
        margin: 0 auto;
    }

    .receipt {
        font-family: 'Courier New', ui-monospace, monospace;
        color: var(--ar-ink);
        font-size: 13px;
        line-height: 1.5;
    }

    .receipt-brand { text-align: center; margin-bottom: 4px; }

    .receipt-brand h3 {
        margin: 0;
        font-size: 19px;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--ar-ink);
    }

    .receipt .muted { color: var(--ar-muted); font-size: 12px; }

    .receipt table { width: 100%; font-size: 13px; border-collapse: collapse; }
    .receipt table td, .receipt table th { padding: 2px 0; vertical-align: top; }
    .receipt thead th {
        font-size: 11px;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--ar-muted);
        font-weight: 700;
        padding-bottom: 5px;
    }

    .receipt .r-key { color: var(--ar-muted); white-space: nowrap; padding-right: 12px; }
    .receipt .r-val { text-align: right; color: var(--ar-ink); font-weight: 600; word-break: break-word; }
    .receipt .r-money { text-align: right; font-variant-numeric: tabular-nums; }

    .receipt .dashed { border-top: 1px dashed var(--ar-line); margin: 10px 0; }

    .receipt .r-total td {
        border-top: 1px solid var(--ar-line);
        padding-top: 7px;
        font-size: 16px;
        font-weight: 800;
        color: var(--ar-ink);
    }

    .receipt-stamp {
        display: block;
        width: fit-content;
        margin: 0 auto;
        border: 2px solid var(--ar-success);
        color: var(--ar-success);
        border-radius: 6px;
        padding: 2px 12px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .receipt-foot { text-align: center; }

    @media print {
        .nxl-navigation,
        .nxl-header,
        .no-print,
        .footer-wrapper { display: none !important; }

        .nxl-container,
        .nxl-content { margin: 0 !important; padding: 0 !important; }

        html, body {
            background: #fff !important;
            color: #000 !important;
        }

        .nxl-content .card {
            background: #fff !important;
            border: none !important;
            box-shadow: none !important;
        }

        .nxl-content .card-body { padding: 0 !important; }

        .receipt-card { max-width: 100%; margin: 0; }

        .receipt,
        .receipt * {
            background: #fff !important;
            color: #000 !important;
        }

        .receipt .muted,
        .receipt .r-key,
        .receipt thead th { color: #333 !important; }

        .receipt .dashed { border-top-color: #000 !important; }
        .receipt .r-total td { border-top-color: #000 !important; }
        .receipt-stamp { border-color: #000 !important; color: #000 !important; }

        @page { margin: 10mm; }
    }
</style>

<div class="container-fluid">

    <div class="no-print">
        <x-page-header
            title="Invoice {{ $sale->invoice_no }}"
            subtitle="Counter receipt — print it for the customer or keep it for the till roll."
            icon="feather-file-text"
            :breadcrumb="['POS Billing' => null, 'Sales History' => route('admin.pos.sales'), 'Invoice' => null]">

            @can('pos.create')
                <a href="{{ route('admin.pos.index') }}" class="btn btn-soft">
                    <i class="feather-plus"></i> New Sale
                </a>
            @endcan

            @can('pos.view')
                <a href="{{ route('admin.pos.sales') }}" class="btn btn-soft-primary">
                    <i class="feather-list"></i> Sales History
                </a>
            @endcan

            <button type="button" onclick="window.print()" class="btn btn-primary">
                <i class="feather-printer"></i> Print
            </button>
        </x-page-header>
    </div>

    <div class="card receipt-card">
        <div class="card-body">
            <div class="receipt">

                <div class="receipt-brand">
                    <h3>My Restaurant</h3>
                    <div class="muted">Cash Memo / Invoice</div>
                </div>

                <div class="dashed"></div>

                <table>
                    <tr><td class="r-key">Invoice</td><td class="r-val">{{ $sale->invoice_no }}</td></tr>
                    <tr><td class="r-key">Date</td><td class="r-val">{{ $sale->created_at->format('d M Y, h:i A') }}</td></tr>
                    <tr><td class="r-key">Type</td><td class="r-val text-capitalize">{{ str_replace('_', '-', $sale->order_type) }}</td></tr>
                    @if ($sale->table_no)
                        <tr><td class="r-key">Table</td><td class="r-val">{{ $sale->table_no }}</td></tr>
                    @endif
                    @if ($sale->customer_name)
                        <tr><td class="r-key">Customer</td><td class="r-val">{{ $sale->customer_name }}</td></tr>
                    @endif
                    @if ($sale->phone)
                        <tr><td class="r-key">Phone</td><td class="r-val">{{ $sale->phone }}</td></tr>
                    @endif
                    @if ($sale->address)
                        <tr><td class="r-key">Address</td><td class="r-val">{{ $sale->address }}</td></tr>
                    @endif
                    @if ($sale->cashier)
                        <tr><td class="r-key">Cashier</td><td class="r-val">{{ $sale->cashier->name }}</td></tr>
                    @endif
                </table>

                <div class="dashed"></div>

                <table>
                    <thead>
                        <tr>
                            <th class="text-start">Item</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr>
                                <td class="text-start">{{ $item->name }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="r-money">{{ number_format($item->price, 2) }}</td>
                                <td class="r-money">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="dashed"></div>

                <table class="totals">
                    <tr><td class="r-key">Subtotal</td><td class="r-money">৳{{ number_format($sale->subtotal, 2) }}</td></tr>
                    @if ($sale->discount_amount > 0)
                        <tr>
                            <td class="r-key">Discount @if ($sale->discount_type === 'percent')({{ rtrim(rtrim($sale->discount_value, '0'), '.') }}%)@endif</td>
                            <td class="r-money">-৳{{ number_format($sale->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if ($sale->tax_amount > 0)
                        <tr><td class="r-key">VAT ({{ rtrim(rtrim($sale->tax_rate, '0'), '.') }}%)</td><td class="r-money">৳{{ number_format($sale->tax_amount, 2) }}</td></tr>
                    @endif
                    @if ($sale->service_charge_amount > 0)
                        <tr><td class="r-key">Service ({{ rtrim(rtrim($sale->service_charge_rate, '0'), '.') }}%)</td><td class="r-money">৳{{ number_format($sale->service_charge_amount, 2) }}</td></tr>
                    @endif
                    <tr class="r-total">
                        <td>TOTAL</td>
                        <td class="r-money">৳{{ number_format($sale->total, 2) }}</td>
                    </tr>
                </table>

                <div class="dashed"></div>

                <table>
                    <tr><td class="r-key">Payment</td><td class="r-val text-uppercase">{{ $sale->payment_method }}</td></tr>
                    @if ($sale->payment_method === 'cash')
                        <tr><td class="r-key">Received</td><td class="r-money">৳{{ number_format($sale->paid_amount, 2) }}</td></tr>
                        <tr><td class="r-key">Change</td><td class="r-money">৳{{ number_format($sale->change_amount, 2) }}</td></tr>
                    @endif
                </table>

                @if ($sale->payment_status === 'paid')
                    <div class="dashed"></div>
                    <span class="receipt-stamp">Paid</span>
                @endif

                @if ($sale->note)
                    <div class="dashed"></div>
                    <div class="muted">Note: {{ $sale->note }}</div>
                @endif

                <div class="dashed"></div>
                <div class="receipt-foot muted">Thank you. Please come again.</div>

            </div>
        </div>
    </div>
</div>

@endsection
