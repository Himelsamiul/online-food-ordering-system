@extends('frontend.master')
@section('title', 'My Account')

@php
    /*
        Everything below is derived from the collection the controller already
        loaded — no extra queries, no lazy loads.
    */
    $totalOrders = $orders->count();
    $inProgress  = $orders->whereNotIn('order_status', ['delivered', 'cancelled'])->count();
    $delivered   = $orders->where('order_status', 'delivered')->count();
    $paidTotal   = $orders->where('payment_status', 'paid')->sum('total_amount');

    $orderTone = [
        'pending'          => 'pending',
        'cooking'          => 'progress',
        'out_for_delivery' => 'progress',
        'delivered'        => 'done',
        'cancelled'        => 'cancel',
    ];

    $payTone = [
        'paid'      => 'done',
        'pending'   => 'pending',
        'failed'    => 'cancel',
        'cancelled' => 'cancel',
    ];
@endphp

@push('styles')
<style>
    .sf-page-head { margin-bottom: 26px; }

    .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .sf-page-head p {
        color: var(--sf-muted);
        margin-bottom: 0;
    }

    .sf-card-title {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 18px;
        margin: 0;
    }

    .sf-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
    }

    .sf-card-head .sf-count {
        color: var(--sf-muted);
        font-size: 13px;
    }

    /* ============ IDENTITY CARD ============ */
    .sf-id-avatar {
        width: 116px;
        height: 116px;
        border-radius: 50%;
        margin: 0 auto 14px;
        overflow: hidden;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .8);
        font-size: 42px;
        font-weight: 800;
        text-transform: uppercase;
        box-shadow: 0 14px 30px rgba(0, 0, 0, .4);
    }

    .sf-id-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .sf-id-name {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 21px;
        margin: 0;
    }

    /* `.glass-card small` in the design layer is more specific than a bare
       class, so these two are scoped to win it back. */
    .glass-card .sf-id-handle {
        color: var(--sf-muted);
        font-size: 13.5px;
        display: block;
        margin-bottom: 12px;
    }

    .sf-id-list {
        list-style: none;
        padding: 0;
        margin: 18px 0 0;
    }

    .sf-id-list li {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 14px;
        padding: 11px 0;
        border-top: 1px solid rgba(255, 255, 255, .09);
        font-size: 13.5px;
    }

    .sf-id-list li > span { color: var(--sf-muted); white-space: nowrap; }

    .sf-id-list li > strong {
        color: var(--sf-ink);
        font-weight: 600;
        text-align: right;
        word-break: break-word;
    }

    /* ============ BADGES ============ */
    .sf-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 30px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .sf-badge-pending  { background: rgba(241, 184, 22, .14); color: var(--sf-accent); border-color: rgba(241, 184, 22, .38); }
    .sf-badge-progress { background: rgba(255, 255, 255, .09); color: var(--sf-ink);    border-color: var(--sf-glass-line); }
    .sf-badge-done     { background: rgba(29, 191, 115, .16); color: var(--sf-green);  border-color: rgba(29, 191, 115, .42); }
    .sf-badge-cancel   { background: rgba(231, 76, 60, .16);  color: var(--sf-danger); border-color: rgba(231, 76, 60, .42); }

    /* ============ STAT TILES ============ */
    .sf-stat {
        height: 100%;
        padding: 15px 12px;
        text-align: center;
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius-sm);
        background: rgba(255, 255, 255, .05);
    }

    .sf-stat strong {
        display: block;
        color: var(--sf-ink);
        font-size: 22px;
        font-weight: 800;
        line-height: 1.2;
    }

    .sf-stat span {
        display: block;
        margin-top: 3px;
        color: var(--sf-muted);
        font-size: 11.5px;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    /* A running total is longer than an order count — give it room. */
    .sf-stat-money strong { font-size: 17px; word-break: break-all; }

    /* ============ ORDER LIST ============ */
    .sf-order-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        margin-bottom: 12px;
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius-sm);
        background: rgba(0, 0, 0, .22);
        transition: border-color .18s, transform .18s, background-color .18s;
    }

    .sf-order-row:last-child { margin-bottom: 0; }

    .sf-order-row:hover {
        border-color: rgba(241, 184, 22, .45);
        background: rgba(0, 0, 0, .3);
        transform: translateY(-2px);
    }

    .sf-order-main { flex: 1 1 240px; min-width: 0; }

    .sf-order-no {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 15px;
        word-break: break-all;
    }

    .sf-order-meta {
        color: var(--sf-muted);
        font-size: 12.5px;
        margin-top: 5px;
    }

    .sf-order-meta i { width: 14px; }

    .sf-order-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        flex: 0 1 auto;
    }

    .sf-order-total {
        color: var(--sf-accent);
        font-weight: 800;
        font-size: 17px;
        white-space: nowrap;
        margin-left: auto;
    }

    .sf-order-row .btn { flex-shrink: 0; }

    /* ============ OFFERS ============ */
    .offer-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        border: 1px dashed rgba(241, 184, 22, .45);
        border-radius: var(--sf-radius-sm);
        padding: 14px 18px;
        margin-bottom: 12px;
        background: rgba(241, 184, 22, .06);
    }

    .offer-row:last-child { margin-bottom: 0; }

    .offer-code {
        background: transparent;
        border: 2px dashed var(--sf-accent);
        color: var(--sf-accent);
        font-family: 'Courier New', Courier, monospace;
        font-weight: 800;
        letter-spacing: 1.2px;
        border-radius: 8px;
        padding: 5px 14px;
        cursor: pointer;
        transition: background-color .2s, color .2s;
    }

    .offer-code:hover { background: var(--sf-accent); color: rgba(0, 0, 0, .82); }

    .offer-value {
        margin-left: 10px;
        font-weight: 800;
        color: var(--sf-green);
    }

    .glass-card .offer-note {
        display: block;
        margin-top: 6px;
        color: var(--sf-muted);
        font-size: 13px;
    }

    .offer-terms {
        color: var(--sf-muted);
        font-size: 12.5px;
        line-height: 1.7;
    }

    /* ============ SUPPORT NOTE + EMPTY STATE ============ */
    .sf-note {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 16px 18px;
        border-radius: var(--sf-radius-sm);
        border: 1px solid rgba(29, 191, 115, .35);
        background: rgba(29, 191, 115, .1);
    }

    .sf-note i { color: var(--sf-green); font-size: 18px; margin-top: 2px; }
    .sf-note strong { display: block; color: var(--sf-ink); font-size: 14px; margin-bottom: 4px; }
    .sf-note p { color: var(--sf-muted); font-size: 13px; margin: 0; line-height: 1.6; }

    .sf-empty { text-align: center; padding: 32px 16px; }
    .sf-empty i { font-size: 42px; color: var(--sf-faint); }
    .sf-empty p { color: var(--sf-muted); margin: 14px 0 18px; }

    @media (max-width: 575.98px) {
        .sf-order-total { margin-left: 0; }
        .sf-order-row .btn { width: 100%; }
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    $('.js-copy-code').on('click', function () {
        const code = $(this).data('code');

        const done = () => Swal.fire({
            icon: 'success',
            title: 'Code copied',
            text: code + ' — paste it in your cart to get the discount.',
            timer: 1800,
            showConfirmButton: false,
        });

        // navigator.clipboard needs HTTPS or localhost; fall back for plain http
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(done);
        } else {
            const tmp = $('<textarea>').val(code).css({position:'fixed', opacity:0}).appendTo('body');
            tmp[0].select();
            document.execCommand('copy');
            tmp.remove();
            done();
        }
    });
});
</script>
@endpush

@section('content')

<section class="profile-section layout_padding">
    <div class="container">

        <div class="sf-page-head text-center">
            <h2>My Account</h2>
            <p>Your details, your orders and the offers you can use right now.</p>
        </div>

        <div class="row">

            {{-- ============ IDENTITY ============ --}}
            <div class="col-lg-4 mb-4">
                <div class="glass-card p-4">

                    <div class="text-center">
                        <div class="sf-id-avatar">
                            @if ($user->image && file_exists(public_path('storage/'.$user->image)))
                                <img src="{{ asset('storage/'.$user->image) }}"
                                     alt="{{ $user->full_name }}">
                            @else
                                {{ \Illuminate\Support\Str::of($user->full_name)->substr(0, 1) }}
                            @endif
                        </div>

                        <h3 class="sf-id-name">{{ $user->full_name }}</h3>
                        <small class="sf-id-handle">{{ '@'.$user->username }}</small>

                        @if ($user->isActive())
                            <span class="sf-badge sf-badge-done">Active account</span>
                        @else
                            <span class="sf-badge sf-badge-cancel">Deactivated</span>
                        @endif
                    </div>

                    <ul class="sf-id-list">
                        <li>
                            <span>Email</span>
                            <strong>{{ $user->email }}</strong>
                        </li>
                        <li>
                            <span>Phone</span>
                            <strong>{{ $user->phone }}</strong>
                        </li>
                        <li>
                            <span>Address</span>
                            <strong>{{ $user->address ?: 'Not added yet' }}</strong>
                        </li>
                        <li>
                            <span>Date of birth</span>
                            <strong>
                                {{ $user->dob
                                    ? \Illuminate\Support\Carbon::parse($user->dob)->format('d M Y')
                                    : 'Not added yet' }}
                            </strong>
                        </li>
                        <li>
                            <span>Member since</span>
                            <strong>{{ optional($user->created_at)->format('M Y') ?: '—' }}</strong>
                        </li>
                    </ul>

                    <a href="{{ route('profile.edit') }}" class="btn btn-warning btn-block mt-4">
                        Edit Profile
                    </a>

                    @unless ($user->isActive())
                        <div class="sf-help-box">
                            <p>
                                <strong>This account is switched off.</strong>
                                An administrator has to turn it back on before you can order again.
                            </p>
                            <a class="btn btn-warning"
                               href="{{ route('account.help', ['type' => 'activation', 'email' => $user->email]) }}">
                                Request reactivation
                            </a>
                        </div>
                    @endunless

                    <div class="text-center mt-3">
                        <a href="{{ route('account.help', ['type' => 'password', 'email' => $user->email]) }}"
                           class="sf-link-muted">
                            Trouble with your password? Ask an admin
                        </a>
                    </div>
                </div>

                <div class="sf-note mt-4">
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    <div>
                        <strong>Need to cancel an order?</strong>
                        <p>
                            Call our support team on +880-1234-567890. Eligible payments are refunded
                            under our order cancellation and refund policy.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ============ ORDERS + OFFERS ============ --}}
            <div class="col-lg-8">

                {{-- summary tiles --}}
                <div class="row mb-2">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="sf-stat">
                            <strong>{{ $totalOrders }}</strong>
                            <span>Orders</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="sf-stat">
                            <strong>{{ $inProgress }}</strong>
                            <span>On the way</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="sf-stat">
                            <strong>{{ $delivered }}</strong>
                            <span>Delivered</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="sf-stat sf-stat-money">
                            <strong>৳{{ number_format($paidTotal, 2) }}</strong>
                            <span>Paid so far</span>
                        </div>
                    </div>
                </div>

                {{-- offers --}}
                <div class="glass-card p-4 mb-4">
                    <div class="sf-card-head">
                        <h4 class="sf-card-title">My Offers</h4>
                        @if ($offers->count())
                            <span class="sf-count">Tap a code to copy it</span>
                        @endif
                    </div>

                    @forelse ($offers as $offer)
                        <div class="offer-row">
                            <div>
                                <button type="button" class="offer-code js-copy-code"
                                        data-code="{{ $offer->code }}"
                                        title="Click to copy">
                                    <i class="fa fa-clone" aria-hidden="true"></i> {{ $offer->code }}
                                </button>

                                <span class="offer-value">{{ $offer->offer_label }}</span>

                                @if ($offer->description)
                                    <small class="offer-note">{{ $offer->description }}</small>
                                @endif
                            </div>

                            <div class="offer-terms text-right">
                                @if ($offer->min_order_amount)
                                    <div>Min order ৳{{ number_format($offer->min_order_amount, 0) }}</div>
                                @else
                                    <div>No minimum order</div>
                                @endif

                                @if ($offer->type === 'percent' && $offer->max_discount)
                                    <div>Up to ৳{{ number_format($offer->max_discount, 0) }}</div>
                                @endif

                                <div>
                                    {{ $offer->expires_at
                                        ? 'Valid till ' . $offer->expires_at->format('d M Y')
                                        : 'No expiry' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="sf-empty">
                            <i class="fa fa-tag" aria-hidden="true"></i>
                            <p>No offers for you at the moment — we add new ones regularly.</p>
                            <a href="{{ route('menu.index') }}" class="btn btn-outline-light">Browse the menu</a>
                        </div>
                    @endforelse
                </div>

                {{-- order history --}}
                <div class="glass-card p-4">
                    <div class="sf-card-head">
                        <h4 class="sf-card-title">Order History</h4>
                        @if ($totalOrders)
                            <span class="sf-count">
                                {{ $totalOrders }} {{ \Illuminate\Support\Str::plural('order', $totalOrders) }}, newest first
                            </span>
                        @endif
                    </div>

                    @forelse ($orders as $order)
                        @php
                            $oTone = $orderTone[$order->order_status] ?? 'progress';
                            $pTone = $payTone[$order->payment_status] ?? 'progress';
                        @endphp

                        <div class="sf-order-row">
                            <div class="sf-order-main">
                                <div class="sf-order-no">#{{ $order->order_number }}</div>
                                <div class="sf-order-meta">
                                    {{ $order->created_at->format('d M Y, g:i a') }}
                                    &middot; {{ strtoupper($order->payment_method) }}
                                    @if ($order->transaction_number)
                                        &middot; Txn {{ $order->transaction_number }}
                                    @endif
                                </div>
                            </div>

                            <div class="sf-order-tags">
                                <span class="sf-badge sf-badge-{{ $oTone }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->order_status)) }}
                                </span>
                                <span class="sf-badge sf-badge-{{ $pTone }}">
                                    Payment {{ $order->payment_status }}
                                </span>
                            </div>

                            <div class="sf-order-total">৳{{ number_format($order->total_amount, 2) }}</div>

                            <a href="{{ route('profile.order.view', $order->id) }}"
                               class="btn btn-sm btn-outline-light">
                                View
                            </a>
                        </div>
                    @empty
                        <div class="sf-empty">
                            <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                            <p>You have not placed an order yet — have a look at the menu.</p>
                            <a href="{{ route('menu.index') }}" class="btn btn-warning">Start an order</a>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

    </div>
</section>

@endsection
