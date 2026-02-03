@extends('backend.master')

@section('content')

<style>
    /* ====== PRINT RESET ====== */
    @media print {
        body * {
            visibility: hidden;
        }

        .print-area, .print-area * {
            visibility: visible;
        }

        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* hide admin layout & print button */
        .navbar, .sidebar, .header, .footer, .btn-print {
            display: none !important;
        }

        @page {
            size: A4;
            margin: 20mm;
        }
    }

    /* ====== INVOICE STYLE ====== */
    .print-area {
        max-width: 794px;
        margin: auto;
        background: #fff;
        padding: 30px;
        font-size: 14px;
        color: #000;
    }

    .invoice-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .invoice-header h2 {
        margin: 0;
        font-weight: 700;
    }

    .invoice-header p {
        margin: 2px 0;
        font-size: 13px;
    }

    .info-box p {
        margin: 0;
        line-height: 1.6;
    }

    table th, table td {
        vertical-align: middle !important;
    }

    .summary-box {
        max-width: 350px;
        margin-left: auto;
        margin-top: 20px;
    }

    .invoice-footer {
        text-align: center;
        font-size: 12px;
        margin-top: 40px;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }

    .print-btn-wrapper {
        max-width: 794px;
        margin: 0 auto 15px;
        text-align: right;
    }

    
</style>

{{-- ===== PRINT BUTTON ===== --}}
<div class="print-btn-wrapper">
    <button onclick="window.print()" class="btn btn-primary btn-print">
        🖨️ Print Invoice
    </button>
</div>

<div class="print-area">

    {{-- ===== HEADER ===== --}}
    <div class="invoice-header">
        <h2>Feane Restaurant</h2>
        <p>Order Invoice</p>
        <p>Phone: +880-XXXX-XXXXXX</p>
        <p>Email: info@feanerestaurant.com</p>
    </div>

    {{-- ===== ORDER INFO ===== --}}
    <div class="row mb-4">
        <div class="col-md-6 info-box">
            <p><strong>Order No:</strong> {{ $order->order_number }}</p>
            <p><strong>Transaction ID:</strong> {{ $order->transaction_number }}</p>
            <p><strong>Order Status:</strong> {{ ucfirst($order->order_status) }}</p>
        </div>

        <div class="col-md-6 info-box text-end">
           <p>
    <strong>Payment Method:</strong>
    <span class="{{ $order->payment_method === 'stripe' ? 'text-success fw-semibold' : 'text-primary fw-semibold' }}">
        {{ strtoupper($order->payment_method) }}
    </span>
</p>
            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    {{-- ===== CUSTOMER INFO ===== --}}
    <div class="mb-4">
        <h6 class="fw-bold mb-2">Customer Information</h6>
        <p><strong>Name:</strong> {{ $order->name }}</p>
        <p><strong>Phone:</strong> {{ $order->phone }}</p>
        <p><strong>Address:</strong> {{ $order->address }}</p>
    </div>

    {{-- ===== ITEMS TABLE ===== --}}
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Food</th>
                <th>Original Price</th>
                <th>Discount %</th>
                <th>Discount Amount</th>
                <th>Final Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @php
                $subTotal = 0;
                $totalDiscount = 0;
            @endphp

            @foreach($order->items as $key => $item)
                @php
                    $originalPrice = $item->food->price;
                    $discountPercent = $item->food->discount ?? 0;
                    $discountAmount = ($originalPrice * $discountPercent) / 100;
                    $finalPrice = $originalPrice - $discountAmount;

                    $lineTotal = $finalPrice * $item->quantity;

                    $subTotal += $originalPrice * $item->quantity;
                    $totalDiscount += $discountAmount * $item->quantity;
                @endphp

                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->food->name }}</td>
                    <td>{{ number_format($originalPrice, 2) }} ৳</td>
                    <td>{{ $discountPercent }}%</td>
                    <td>{{ number_format($discountAmount, 2) }} ৳</td>
                    <td>{{ number_format($finalPrice, 2) }} ৳</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($lineTotal, 2) }} ৳</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ===== SUMMARY ===== --}}
    @php
        $payableAmount = $subTotal - $totalDiscount;

        if ($order->payment_method === 'stripe') {
            $paidAmount = $payableAmount;
            $dueAmount  = 0;
        } else {
            $paidAmount = 0;
            $dueAmount  = $payableAmount;
        }
    @endphp

    <div class="summary-box">
        <table class="table table-bordered">
            <tr>
                <th>Subtotal</th>
                <td>{{ number_format($subTotal, 2) }} ৳</td>
            </tr>
            <tr>
                <th>Total Discount</th>
                <td>- {{ number_format($totalDiscount, 2) }} ৳</td>
            </tr>
            <tr>
                <th>Payable Amount</th>
                <td class="fw-bold">{{ number_format($payableAmount, 2) }} ৳</td>
            </tr>
            <tr>
                <th>Paid Amount</th>
                <td class="text-success fw-semibold">
                    {{ number_format($paidAmount, 2) }} ৳
                </td>
            </tr>
            <tr>
                <th>Due Amount</th>
                <td class="text-danger fw-semibold">
                    {{ number_format($dueAmount, 2) }} ৳
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== FOOTER ===== --}}
    <div class="invoice-footer">
        Thank you for ordering from <strong>Feane Restaurant</strong><br>
        This is a system generated invoice.
    </div>

</div>
@endsection
