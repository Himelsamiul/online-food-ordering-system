@extends('frontend.master')

@section('title', 'Order Confirmed')

@push('styles')
<style>
    .success-card {
        padding: 40px 34px;
        text-align: center;
    }

    .success-icon {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        margin: 0 auto 20px;
        display: grid;
        place-items: center;
        font-size: 38px;
        color: var(--sf-green);
        background: rgba(29, 191, 115, .14);
        border: 1px solid rgba(29, 191, 115, .45);
    }

    .success-card h3 {
        color: var(--sf-ink);
        font-weight: 800;
        margin-bottom: 8px;
    }

    .success-card .success-sub {
        color: var(--sf-muted);
        margin-bottom: 24px;
    }

    .order-number {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 14px 30px;
        border-radius: var(--sf-radius-sm);
        background: rgba(241, 184, 22, .1);
        border: 1px dashed rgba(241, 184, 22, .45);
        margin-bottom: 28px;
    }

    .order-number span {
        font-size: 11.5px;
        letter-spacing: .1em;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--sf-muted);
    }

    .order-number strong {
        font-family: monospace;
        font-size: 21px;
        font-weight: 800;
        letter-spacing: 1px;
        color: var(--sf-accent);
    }

    /* ================= WHAT HAPPENS NEXT ================= */
    .next-steps {
        text-align: left;
        margin-bottom: 28px;
    }

    .next-title,
    .section-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--sf-muted);
        margin-bottom: 14px;
    }

    .next-step {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 11px 0;
    }

    .next-step .dot {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, .07);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-accent);
        font-size: 14px;
    }

    .next-step strong {
        display: block;
        color: var(--sf-ink);
        font-size: 14.5px;
        font-weight: 700;
    }

    .next-step small {
        display: block;
        color: var(--sf-muted);
        font-size: 13px;
        margin-top: 2px;
    }

    /* ================= ITEMS + TOTALS ================= */
    .order-info { text-align: left; }

    .order-info .row-line {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
        color: rgba(255, 255, 255, .82);
        font-size: 14.5px;
    }

    .order-info .row-line:last-child { border-bottom: none; }
    .order-info .row-line strong { color: var(--sf-ink); font-weight: 700; white-space: nowrap; }
    .order-info .row-line .muted { color: var(--sf-muted); font-size: 12.5px; display: block; }
    .order-info .row-line.discount strong { color: var(--sf-green); }

    .order-info .row-line.grand {
        border-top: 1px dashed rgba(255, 255, 255, .22);
        border-bottom: none;
        margin-top: 8px;
        padding-top: 14px;
        color: var(--sf-ink);
        font-weight: 700;
    }

    .order-info .row-line.grand strong {
        font-size: 26px;
        font-weight: 800;
        color: var(--sf-green);
        letter-spacing: -.02em;
    }

    .divider {
        height: 1px;
        background: var(--sf-glass-line);
        margin: 26px 0;
    }

    /* ================= BADGES ================= */
    .badge-paid,
    .badge-pending,
    .badge-status {
        display: inline-block;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .badge-paid {
        background: rgba(29, 191, 115, .18);
        border: 1px solid rgba(29, 191, 115, .5);
        color: var(--sf-green);
    }

    .badge-pending {
        background: rgba(241, 184, 22, .16);
        border: 1px solid rgba(241, 184, 22, .45);
        color: var(--sf-accent);
    }

    .badge-status {
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: rgba(255, 255, 255, .82);
    }

    .success-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
    }

    .success-actions .btn { padding: 12px 26px; font-weight: 700; }
</style>
@endpush

@section('content')

<section class="order-success-section layout_padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @php
                    // subtotal was added to orders later and is nullable on old rows.
                    $subtotal = $order->subtotal ?? ($order->total_amount + $order->discount_amount);
                    $isPaid   = $order->payment_status === 'paid';
                @endphp

                <div class="glass-card success-card">

                    <div class="success-icon">
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </div>

                    <h3>Your order is confirmed</h3>
                    <p class="success-sub">
                        Thanks {{ Str::of($order->name)->before(' ') }} — the kitchen has it now.
                    </p>

                    <div class="order-number">
                        <span>Order number</span>
                        <strong>{{ $order->order_number }}</strong>
                    </div>

                    {{-- ================= WHAT HAPPENS NEXT ================= --}}
                    <div class="next-steps">
                        <div class="next-title">What happens next</div>

                        <div class="next-step">
                            <span class="dot"><i class="fa fa-cutlery" aria-hidden="true"></i></span>
                            <div>
                                <strong>We start cooking</strong>
                                <small>Your order moves to preparing as soon as the kitchen picks it up.</small>
                            </div>
                        </div>

                        <div class="next-step">
                            <span class="dot"><i class="fa fa-truck" aria-hidden="true"></i></span>
                            <div>
                                <strong>A rider brings it over</strong>
                                <small>We call {{ $order->phone }} when they are close.</small>
                            </div>
                        </div>

                        <div class="next-step">
                            <span class="dot"><i class="fa fa-money" aria-hidden="true"></i></span>
                            <div>
                                @if ($isPaid)
                                    <strong>Already paid</strong>
                                    <small>Nothing left to pay at the door.</small>
                                @else
                                    <strong>Pay ৳{{ number_format($order->total_amount, 2) }} on delivery</strong>
                                    <small>Cash to the rider when the food reaches you.</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    {{-- ================= ORDER DETAILS ================= --}}
                    <div class="order-info">

                        <div class="section-title">Your order</div>

                        @foreach ($order->items as $item)
                            <div class="row-line">
                                <span>
                                    {{ $item->food->name ?? 'Item no longer on the menu' }}
                                    <span class="muted">
                                        ৳{{ number_format($item->price, 2) }} × {{ $item->quantity }}
                                    </span>
                                </span>
                                <strong>৳{{ number_format($item->total, 2) }}</strong>
                            </div>
                        @endforeach

                        <div class="row-line" style="border-bottom:none;">
                            <span>Subtotal</span>
                            <strong>৳{{ number_format($subtotal, 2) }}</strong>
                        </div>

                        @if ($order->discount_amount > 0)
                            <div class="row-line discount" style="border-bottom:none;">
                                <span>Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                                <strong>− ৳{{ number_format($order->discount_amount, 2) }}</strong>
                            </div>
                        @endif

                        <div class="row-line grand">
                            <span>Total</span>
                            <strong>৳{{ number_format($order->total_amount, 2) }}</strong>
                        </div>

                        <div class="divider"></div>

                        <div class="section-title">Payment &amp; delivery</div>

                        <div class="row-line">
                            <span>Payment method</span>
                            <strong>{{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'Card (Stripe)' }}</strong>
                        </div>

                        <div class="row-line">
                            <span>Payment status</span>
                            @if ($isPaid)
                                <span class="badge-paid">Paid</span>
                            @else
                                <span class="badge-pending">Pay on delivery</span>
                            @endif
                        </div>

                        <div class="row-line">
                            <span>Order status</span>
                            <span class="badge-status">
                                {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                            </span>
                        </div>

                        @if ($order->transaction_number)
                            <div class="row-line">
                                <span>Transaction reference</span>
                                <strong style="font-family:monospace;font-size:13px;">
                                    {{ $order->transaction_number }}
                                </strong>
                            </div>
                        @endif

                        <div class="row-line">
                            <span>Delivering to</span>
                            <strong style="text-align:right;white-space:normal;font-weight:600;">
                                {{ $order->address }}
                            </strong>
                        </div>
                    </div>

                    {{-- ================= ACTIONS ================= --}}
                    <div class="success-actions mt-4">
                        <a href="{{ route('profile.order.view', $order->id) }}" class="btn btn-success">
                            Track this order
                        </a>

                        <a href="{{ route('menu.index') }}" class="btn btn-outline-light">
                            Keep shopping
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

@endsection
