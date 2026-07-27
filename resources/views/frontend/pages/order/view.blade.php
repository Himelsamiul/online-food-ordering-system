@extends('frontend.master')

@section('title', 'Order ' . $order->order_number)

@push('styles')
<style>
    .sf-page-head h2 { color: var(--sf-ink); font-weight: 700; }
    .sf-page-head p  { color: var(--sf-muted); margin-bottom: 0; }
    .sf-page-head    { margin-bottom: 26px; }

    .order-card { padding: 24px 22px; margin-bottom: 22px; }

    .order-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 15px;
        color: var(--sf-ink);
        border-bottom: 1px solid var(--sf-glass-line);
        padding-bottom: 12px;
        margin-bottom: 18px;
    }

    .order-card-head i { color: var(--sf-accent); }

    /* ================= HEADER STRIP ================= */
    .order-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .order-top .order-no {
        font-family: monospace;
        font-size: 21px;
        font-weight: 800;
        letter-spacing: 1px;
        color: var(--sf-accent);
        display: block;
    }

    .order-top .order-when {
        display: block;
        color: var(--sf-muted);
        font-size: 13px;
        margin-top: 3px;
    }

    .pill {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: rgba(255, 255, 255, .82);
    }

    .pill.is-green {
        background: rgba(29, 191, 115, .18);
        border-color: rgba(29, 191, 115, .5);
        color: var(--sf-green);
    }

    .pill.is-amber {
        background: rgba(241, 184, 22, .16);
        border-color: rgba(241, 184, 22, .45);
        color: var(--sf-accent);
    }

    .pill.is-red {
        background: rgba(231, 76, 60, .16);
        border-color: rgba(231, 76, 60, .5);
        color: var(--sf-danger);
    }

    /* ================= STATUS TRACK ================= */
    .track {
        display: flex;
        margin-top: 6px;
    }

    .track-step {
        flex: 1 1 0;
        position: relative;
        text-align: center;
        padding: 4px 4px 0;
    }

    .track-step::before {
        content: '';
        position: absolute;
        top: 21px;
        left: -50%;
        right: 50%;
        height: 2px;
        background: rgba(255, 255, 255, .14);
    }

    .track-step:first-child::before { display: none; }
    .track-step.is-done::before { background: var(--sf-green); }

    .track-dot {
        position: relative;
        z-index: 1;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        margin: 0 auto 10px;
        display: grid;
        place-items: center;
        font-size: 14px;
        background: rgba(18, 18, 22, .9);
        border: 2px solid rgba(255, 255, 255, .16);
        color: var(--sf-faint);
    }

    .track-step.is-done .track-dot {
        border-color: var(--sf-green);
        background: rgba(29, 191, 115, .18);
        color: var(--sf-green);
    }

    .track-step.is-current .track-dot {
        border-color: var(--sf-accent);
        background: rgba(241, 184, 22, .2);
        color: var(--sf-accent);
        box-shadow: 0 0 0 5px rgba(241, 184, 22, .14);
    }

    .track-label {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--sf-faint);
        line-height: 1.3;
    }

    .track-step.is-done .track-label,
    .track-step.is-current .track-label { color: var(--sf-ink); }

    .track-sub {
        display: block;
        font-size: 12px;
        color: var(--sf-muted);
        margin-top: 3px;
    }

    .cancelled-note {
        display: flex;
        align-items: center;
        gap: 12px;
        border-radius: var(--sf-radius-sm);
        background: rgba(231, 76, 60, .12);
        border: 1px solid rgba(231, 76, 60, .4);
        padding: 14px 16px;
        color: rgba(255, 255, 255, .86);
        font-size: 14px;
    }

    .cancelled-note i { color: var(--sf-danger); font-size: 20px; }

    /* ================= TABLE ================= */
    .glass-card .table td .item-sub {
        display: block;
        color: var(--sf-muted);
        font-size: 12.5px;
    }

    /* ================= TOTALS ================= */
    .totals-list {
        max-width: 340px;
        margin-left: auto;
    }

    .totals-list .line {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 14px;
        padding: 7px 0;
        color: rgba(255, 255, 255, .82);
        font-size: 14.5px;
    }

    .totals-list .line strong { color: var(--sf-ink); font-weight: 700; white-space: nowrap; }
    .totals-list .line.discount strong { color: var(--sf-green); }
    .totals-list .line.due strong { color: var(--sf-danger); }

    .totals-list .line.grand {
        border-top: 1px dashed rgba(255, 255, 255, .22);
        margin-top: 8px;
        padding-top: 13px;
        color: var(--sf-ink);
        font-weight: 700;
    }

    .totals-list .line.grand strong {
        font-size: 24px;
        font-weight: 800;
        color: var(--sf-green);
        letter-spacing: -.02em;
    }

    /* ================= DELIVERY ================= */
    .info-line {
        display: flex;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
        font-size: 14px;
    }

    .info-line:last-child { border-bottom: none; }

    .info-line .k {
        flex: 0 0 110px;
        color: var(--sf-muted);
        font-size: 13px;
    }

    .info-line .v { color: var(--sf-ink); font-weight: 600; }

    .order-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .order-actions .btn { padding: 10px 22px; font-weight: 700; }

    @media (max-width: 575.98px) {
        .track { flex-direction: column; }

        .track-step {
            display: flex;
            align-items: center;
            gap: 14px;
            text-align: left;
            padding: 9px 0;
        }

        .track-step::before {
            top: -9px;
            bottom: auto;
            left: 18px;
            right: auto;
            width: 2px;
            height: 18px;
        }

        .track-dot { margin: 0; flex: 0 0 auto; }
    }

    /* ================= PRINT ================= */
    @media print {
        .header_section,
        .footer_section,
        .sf-top,
        .no-print { display: none !important; }

        .hero_area > .bg-box { display: none !important; }

        body { background: rgba(255, 255, 255, 1) !important; }

        .order-view,
        .order-view * {
            color: rgba(0, 0, 0, .86) !important;
            background: transparent !important;
            box-shadow: none !important;
            text-shadow: none !important;
            border-color: rgba(0, 0, 0, .2) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        .order-view .glass-card {
            border: 1px solid rgba(0, 0, 0, .2) !important;
            page-break-inside: avoid;
        }

        .layout_padding { padding: 0 !important; }

        @page { size: A4; margin: 16mm; }
    }
</style>
@endpush

@section('content')

@php
    // The stored columns are the record of what was actually charged; nothing
    // here is recalculated from live food prices, which move over time.
    $subtotal = $order->subtotal ?? ($order->total_amount + $order->discount_amount);
    $isPaid   = $order->payment_status === 'paid';
    $paid     = $isPaid ? (float) $order->total_amount : 0.0;
    $due      = max(0, (float) $order->total_amount - $paid);

    // Order statuses written anywhere in the app: pending, cooking,
    // out_for_delivery, delivered — plus cancelled, which leaves the flow.
    $flow = [
        'pending'          => ['label' => 'Order placed',    'icon' => 'fa-check',     'sub' => 'We have your order'],
        'cooking'          => ['label' => 'Preparing',       'icon' => 'fa-cutlery',   'sub' => 'In the kitchen'],
        'out_for_delivery' => ['label' => 'Out for delivery','icon' => 'fa-motorcycle','sub' => 'On the way to you'],
        'delivered'        => ['label' => 'Delivered',       'icon' => 'fa-home',      'sub' => 'Enjoy your meal'],
    ];

    $isCancelled = $order->order_status === 'cancelled';
    $stepIndex   = array_search($order->order_status, array_keys($flow), true);
    $stepIndex   = $stepIndex === false ? 0 : $stepIndex;
@endphp

<section class="order-view order-view-section layout_padding">
    <div class="container">

        <div class="sf-page-head text-center no-print">
            <h2>Order details</h2>
            <p>Everything we recorded for this order.</p>
        </div>

        {{-- ================= HEADER ================= --}}
        <div class="glass-card order-card">
            <div class="order-top">
                <div>
                    <span class="order-no">{{ $order->order_number }}</span>
                    <span class="order-when">
                        Placed {{ $order->created_at->format('d M Y, h:i A') }}
                        · {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                    </span>
                </div>

                <div>
                    <span class="pill {{ $isCancelled ? 'is-red' : ($order->order_status === 'delivered' ? 'is-green' : 'is-amber') }}">
                        {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                    </span>

                    <span class="pill {{ $isPaid ? 'is-green' : 'is-amber' }}">
                        {{ $isPaid ? 'Paid' : 'Payment pending' }}
                    </span>

                    <span class="pill">
                        {{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'Card' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- ================= STATUS TRACK ================= --}}
        <div class="glass-card order-card">
            <div class="order-card-head">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                Progress
            </div>

            @if ($isCancelled)
                <div class="cancelled-note">
                    <i class="fa fa-times-circle" aria-hidden="true"></i>
                    <span>
                        This order was cancelled.
                        @if ($isPaid)
                            Any payment already taken is refunded to your card.
                        @endif
                    </span>
                </div>
            @else
                <div class="track">
                    @foreach ($flow as $key => $step)
                        @php $i = $loop->index; @endphp

                        <div class="track-step {{ $i < $stepIndex ? 'is-done' : '' }} {{ $i === $stepIndex ? 'is-done is-current' : '' }}">
                            <span class="track-dot">
                                <i class="fa {{ $step['icon'] }}" aria-hidden="true"></i>
                            </span>
                            <span class="track-label">{{ $step['label'] }}</span>
                            <span class="track-sub">
                                {{ $i <= $stepIndex ? $step['sub'] : 'Waiting' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="row">

            {{-- ================= ITEMS ================= --}}
            <div class="col-lg-8">
                <div class="glass-card order-card">
                    <div class="order-card-head">
                        <i class="fa fa-shopping-basket" aria-hidden="true"></i>
                        Items
                    </div>

                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th class="text-right">Unit price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Line total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $k => $item)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>
                                            {{ $item->food->name ?? 'Item no longer on the menu' }}
                                        </td>
                                        <td class="text-right">৳{{ number_format($item->price, 2) }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-right">৳{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ================= TOTALS ================= --}}
                    <div class="totals-list mt-4">
                        <div class="line">
                            <span>Subtotal</span>
                            <strong>৳{{ number_format($subtotal, 2) }}</strong>
                        </div>

                        @if ($order->discount_amount > 0)
                            <div class="line discount">
                                <span>Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                                <strong>− ৳{{ number_format($order->discount_amount, 2) }}</strong>
                            </div>
                        @endif

                        <div class="line">
                            <span>Delivery</span>
                            <strong>Free</strong>
                        </div>

                        <div class="line grand">
                            <span>Total</span>
                            <strong>৳{{ number_format($order->total_amount, 2) }}</strong>
                        </div>

                        <div class="line">
                            <span>Paid</span>
                            <strong>৳{{ number_format($paid, 2) }}</strong>
                        </div>

                        @if ($due > 0)
                            <div class="line due">
                                <span>Due on delivery</span>
                                <strong>৳{{ number_format($due, 2) }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ================= DELIVERY + PAYMENT ================= --}}
            <div class="col-lg-4">
                <div class="glass-card order-card">
                    <div class="order-card-head">
                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                        Delivery
                    </div>

                    <div class="info-line">
                        <span class="k">Name</span>
                        <span class="v">{{ $order->name }}</span>
                    </div>

                    <div class="info-line">
                        <span class="k">Phone</span>
                        <span class="v">{{ $order->phone }}</span>
                    </div>

                    <div class="info-line">
                        <span class="k">Address</span>
                        <span class="v">{{ $order->address }}</span>
                    </div>
                </div>

                <div class="glass-card order-card">
                    <div class="order-card-head">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                        Payment
                    </div>

                    <div class="info-line">
                        <span class="k">Method</span>
                        <span class="v">
                            {{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'Card (Stripe)' }}
                        </span>
                    </div>

                    <div class="info-line">
                        <span class="k">Status</span>
                        <span class="v">{{ $isPaid ? 'Paid' : 'Pending' }}</span>
                    </div>

                    <div class="info-line">
                        <span class="k">Reference</span>
                        <span class="v" style="font-family:monospace;font-size:12.5px;word-break:break-all;">
                            {{ $order->transaction_number ?? 'Not issued yet' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ================= ACTIONS ================= --}}
        <div class="order-actions no-print mb-5">
            <button type="button" class="btn btn-warning" onclick="window.print()">
                <i class="fa fa-print mr-1" aria-hidden="true"></i> Print invoice
            </button>

            <a href="{{ route('profile') }}" class="btn btn-outline-light">Back to my orders</a>

            <a href="{{ route('menu.index') }}" class="sf-link-muted ml-2">Order something else</a>
        </div>

    </div>
</section>

@endsection
