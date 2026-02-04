<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Invoice</title>

    <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        body{
            background:#eef1f5;
            font-family:'Segoe UI', Arial, sans-serif;
        }

        /* ===== PAPER ===== */
        .print-area{
            max-width:900px;
            margin:30px auto;
            background:#ffffff;
            padding:40px;
            border-radius:8px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
        }

        /* ===== HEADER ===== */
        .invoice-header{
            text-align:center;
            margin-bottom:25px;
            background:#f0f7ff;
            padding:20px;
            border-radius:6px;
        }

        .invoice-header h2{
            margin-bottom:4px;
            font-weight:700;
            color:#1e3a8a;
        }

        .invoice-header p{
            margin:0;
            font-size:14px;
            color:#475569;
        }

        /* ===== INFO ===== */
        .info-row{
            display:flex;
            justify-content:space-between;
            font-size:14px;
            margin-bottom:18px;
            background:#f8fafc;
            padding:18px;
            border-radius:6px;
            border:1px solid #e5e7eb;
        }

        .info-row p{
            margin:4px 0;
        }

        /* ===== STATUS COLORS ===== */
        .status-pending{ color:#d97706; font-weight:600; }
        .status-completed{ color:#15803d; font-weight:600; }
        .status-paid{ color:#15803d; font-weight:600; }
        .status-cod{ color:#1d4ed8; font-weight:600; }
        .status-stripe{ color:#2563eb; font-weight:600; }

        /* ===== CUSTOMER ===== */
        .customer-box{
            background:#fff7ed;
            border-left:5px solid #fb923c;
            border-radius:6px;
            padding:14px 18px;
            margin-bottom:25px;
            font-size:14px;
        }

        .customer-box h6{
            margin-bottom:6px;
            font-weight:600;
            color:#9a3412;
        }

        .customer-box p{
            margin:2px 0;
        }

        /* ===== TABLE ===== */
        table{
            width:100%;
            border-collapse:collapse;
            font-size:14px;
        }

        table thead{
            background:#1e293b;
            color:#ffffff;
        }

        table th{
            padding:11px;
            border:1px solid #cbd5e1;
            text-align:center;
            font-weight:600;
        }

        table td{
            padding:10px;
            border:1px solid #e5e7eb;
            text-align:center;
        }

        table tbody tr:nth-child(even){
            background:#f8fafc;
        }

        /* ===== AMOUNT COLORS ===== */
        .amount-sub{ color:#334155; font-weight:600; }
        .amount-discount{ color:#dc2626; font-weight:700; }
        .amount-payable{ color:#1d4ed8; font-weight:800; }
        .amount-paid{ color:#15803d; font-weight:800; }
        .amount-due{ color:#dc2626; font-weight:800; }

        /* ===== SUMMARY ===== */
        .summary-box{
            width:360px;
            margin-left:auto;
            margin-top:25px;
            background:#f1f5f9;
            border-radius:6px;
            padding:12px;
            border:1px solid #e5e7eb;
        }

        .summary-box table th{
            text-align:left;
            padding:8px;
        }

        .summary-box table td{
            text-align:right;
            padding:8px;
        }

        /* ===== FOOTER ===== */
        .invoice-footer{
            text-align:center;
            font-size:12px;
            color:#64748b;
            margin-top:40px;
            border-top:1px dashed #cbd5e1;
            padding-top:12px;
        }

        /* ===== PRINT BUTTON ===== */
        .print-btn{
            text-align:center;
            margin:30px 0;
        }

        /* ===== PRINT ===== */
        @media print{
            body{ background:#fff; }
            .print-btn{ display:none; }
            .print-area{ box-shadow:none; }
            @page{ size:A4; margin:20mm; }
        }
    </style>
</head>
<body>

<div class="print-area">

    {{-- HEADER --}}
    <div class="invoice-header">
        <h2>Feane Restaurant</h2>
        <p>Order Invoice</p>
        <p>Email: info@feanerestaurant.com</p>
    </div>

    {{-- INFO --}}
    <div class="info-row">
        <div>
            <p><strong>Order No:</strong> {{ $order->order_number }}</p>
            <p><strong>Transaction ID:</strong> {{ $order->transaction_number }}</p>
            <p>
                <strong>Order Status:</strong>
                <span class="status-pending">{{ ucfirst($order->order_status) }}</span>
            </p>
        </div>
        <div class="text-end">
            <p>
                <strong>Payment Method:</strong>
                <span class="status-stripe">{{ strtoupper($order->payment_method) }}</span>
            </p>
            <p>
                <strong>Payment Status:</strong>
                <span class="status-paid">{{ ucfirst($order->payment_status) }}</span>
            </p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    {{-- CUSTOMER --}}
    <div class="customer-box">
        <h6>Customer Information</h6>
        <p><strong>Name:</strong> {{ $order->name }}</p>
        <p><strong>Phone:</strong> {{ $order->phone }}</p>
        <p><strong>Address:</strong> {{ $order->address }}</p>
    </div>

    {{-- ITEMS --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Food</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Final</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $sub=0; $disc=0; @endphp
            @foreach($order->items as $k=>$item)
                @php
                    $price=$item->food->price;
                    $d=$item->food->discount ?? 0;
                    $dAmt=($price*$d)/100;
                    $final=$price-$dAmt;
                    $line=$final*$item->quantity;
                    $sub+=$price*$item->quantity;
                    $disc+=$dAmt*$item->quantity;
                @endphp
                <tr>
                    <td>{{ $k+1 }}</td>
                    <td>{{ $item->food->name }}</td>
                    <td>{{ number_format($price,2) }} ৳</td>
                    <td>{{ $d }}%</td>
                    <td>{{ number_format($final,2) }} ৳</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($line,2) }} ৳</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SUMMARY --}}
    @php
        $pay=$sub-$disc;
        $paid=$order->payment_method==='stripe'?$pay:0;
        $due=$pay-$paid;
    @endphp

    <div class="summary-box">
        <table>
            <tr><th>Subtotal</th><td class="amount-sub">{{ number_format($sub,2) }} ৳</td></tr>
            <tr><th>Total Discount</th><td class="amount-discount">- {{ number_format($disc,2) }} ৳</td></tr>
            <tr><th>Payable Amount</th><td class="amount-payable">{{ number_format($pay,2) }} ৳</td></tr>
            <tr><th>Paid Amount</th><td class="amount-paid">{{ number_format($paid,2) }} ৳</td></tr>
            <tr><th>Due Amount</th><td class="amount-due">{{ number_format($due,2) }} ৳</td></tr>
        </table>
    </div>

    <div class="invoice-footer">
        Thank you for ordering from <strong>Feane Restaurant</strong><br>
        This is a system generated invoice.
    </div>

</div>

{{-- PRINT BUTTON --}}
<div class="print-btn">
    <button onclick="window.print()" class="btn btn-lg btn-primary px-5 shadow">
        🖨️ Print Invoice
    </button>
</div>

</body>
</html>