@extends('frontend.master')

@section('title', 'Your Cart')

@push('styles')
<style>
    .sf-page-head h2 { color: var(--sf-ink); font-weight: 700; }
    .sf-page-head p  { color: var(--sf-muted); margin-bottom: 0; }
    .sf-page-head    { margin-bottom: 28px; }

    /* ================= ITEM LIST ================= */
    .cart-box { padding: 0; overflow: hidden; }

    .cart-box-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 22px;
        border-bottom: 1px solid var(--sf-glass-line);
        background: rgba(255, 255, 255, .04);
    }

    .cart-box-head h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: var(--sf-ink);
    }

    .cart-box-head span {
        display: block;
        font-size: 12.5px;
        font-weight: 500;
        color: var(--sf-muted);
        margin-top: 2px;
    }

    .clear-cart-btn {
        background: rgba(231, 76, 60, .12);
        border: 1px solid rgba(231, 76, 60, .4);
        color: var(--sf-danger);
        padding: 7px 18px;
        font-size: 13px;
        font-weight: 700;
        border-radius: 30px;
        transition: background-color .18s, color .18s;
    }

    .clear-cart-btn:hover { background: rgba(231, 76, 60, .24); color: var(--sf-ink); }

    .cart-item {
        border-bottom: 1px solid rgba(255, 255, 255, .08);
        padding: 18px 22px;
        margin: 0;
        transition: background-color .18s;
    }

    .cart-item:last-child { border-bottom: none; }
    .cart-item:hover { background: rgba(255, 255, 255, .03); }

    .cart-img {
        width: 84px;
        height: 84px;
        background: rgba(255, 255, 255, .06);
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius-sm);
        overflow: hidden;
        display: grid;
        place-items: center;
        color: var(--sf-faint);
        font-size: 22px;
    }

    .cart-img img { width: 100%; height: 100%; object-fit: cover; }

    .cart-name {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 15.5px;
        margin: 0 0 4px;
    }

    .cart-unit {
        display: block;
        color: var(--sf-muted);
        font-size: 13px;
    }

    .cart-unit strong { color: var(--sf-ink); font-weight: 700; }
    .cart-unit del { color: var(--sf-faint); margin-left: 6px; }

    .stock-ok   { color: var(--sf-green); font-weight: 600; }
    .stock-low  { color: var(--sf-accent); font-weight: 600; }
    .stock-out  { color: var(--sf-danger); font-weight: 700; }

    .cart-stock { display: block; font-size: 12.5px; margin-top: 5px; }

    /* ================= QUANTITY STEPPER ================= */
    .qty-stepper {
        display: inline-flex;
        align-items: center;
        background: rgba(255, 255, 255, .07);
        border: 1px solid var(--sf-glass-line);
        border-radius: 30px;
        padding: 3px;
    }

    .qty-stepper form { margin: 0; }

    .qty-btn {
        width: 32px;
        height: 32px;
        line-height: 1;
        background: rgba(255, 255, 255, .08);
        color: var(--sf-ink);
        border: none;
        border-radius: 50%;
        font-weight: 800;
        font-size: 15px;
        padding: 0;
        transition: background-color .16s, color .16s;
    }

    .qty-btn:hover:not(:disabled) { background: var(--sf-green); color: var(--sf-ink); }

    .qty-btn:disabled {
        background: transparent;
        color: var(--sf-faint);
        cursor: not-allowed;
    }

    .qty-value {
        min-width: 40px;
        text-align: center;
        font-weight: 800;
        font-size: 15px;
        color: var(--sf-ink);
    }

    .qty-max {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: var(--sf-faint);
    }

    /* ================= LINE TOTAL / REMOVE ================= */
    .line-total {
        display: block;
        font-size: 18px;
        font-weight: 800;
        color: var(--sf-ink);
        letter-spacing: -.02em;
    }

    .remove-btn {
        margin-top: 8px;
        background: transparent;
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-muted);
        border-radius: 30px;
        padding: 5px 14px;
        font-size: 12.5px;
        font-weight: 600;
        transition: border-color .16s, color .16s, background-color .16s;
    }

    .remove-btn:hover {
        border-color: var(--sf-danger);
        color: var(--sf-danger);
        background: rgba(231, 76, 60, .12);
    }

    /* ================= SUMMARY ================= */
    .cart-summary {
        padding: 24px 22px;
        text-align: left;
    }

    .cart-summary h5 {
        margin: 0 0 4px;
        font-weight: 700;
        color: var(--sf-ink);
    }

    .cart-summary .summary-sub {
        display: block;
        color: var(--sf-muted);
        font-size: 13px;
        margin-bottom: 18px;
    }

    .cart-summary .btn-success {
        width: 100%;
        margin-top: 18px;
        padding: 13px 20px;
        font-size: 15.5px;
        font-weight: 800;
    }

    .summary-note {
        display: block;
        margin-top: 12px;
        text-align: center;
        color: var(--sf-faint);
        font-size: 12.5px;
    }

    .keep-shopping {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        color: var(--sf-muted);
        font-size: 14px;
        font-weight: 600;
    }

    .keep-shopping:hover { color: var(--sf-accent); text-decoration: none; }

    @media (min-width: 992px) {
        .cart-summary-col { position: sticky; top: calc(var(--sf-header-h) + 20px); }
    }

    /* ================= EMPTY CART ================= */
    .empty-cart {
        padding: 64px 24px;
        text-align: center;
    }

    .empty-cart-icon {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        margin: 0 auto 20px;
        display: grid;
        place-items: center;
        font-size: 34px;
        color: var(--sf-accent);
        background: rgba(241, 184, 22, .12);
        border: 1px solid rgba(241, 184, 22, .3);
    }

    .empty-cart h4 { color: var(--sf-ink); font-weight: 700; margin-bottom: 8px; }
    .empty-cart p  { color: var(--sf-muted); margin-bottom: 24px; }

    /* The shared responsive rule centres .cart-summary on small screens;
       this panel is a list of label/value rows, so it stays left aligned. */
    @media (max-width: 767.98px) {
        .cart-summary { text-align: left; }
        .cart-item { padding: 18px 16px; }
        .cart-box-head { padding: 14px 16px; }

        /* .glass-card drops to a flat 18px on small screens; these two panels
           own their own spacing (rows go edge to edge, the empty state needs
           room to breathe). */
        .glass-card.cart-box { padding: 0 !important; }
        .glass-card.empty-cart { padding: 48px 20px !important; }
    }
</style>
@endpush

@section('content')

<section class="cart-section layout_padding">
    <div class="container">

        <div class="sf-page-head text-center">
            <h2>Your cart</h2>
            <p>Check everything over, then head to checkout.</p>
        </div>

        {{-- ================= EMPTY CART ================= --}}
        @if (!isset($cart) || count($cart) === 0)

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="glass-card empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        </div>

                        <h4>Your cart is empty</h4>
                        <p>Nothing here yet — have a look at the menu.</p>

                        <a href="{{ route('menu.index') }}" class="btn btn-warning px-4 py-2">
                            Browse the menu
                        </a>
                    </div>
                </div>
            </div>

        @else

            <div class="row">

                {{-- ================= LINE ITEMS ================= --}}
                <div class="col-lg-8 mb-4">

                    <div class="glass-card cart-box">

                        <div class="cart-box-head">
                            <div>
                                <h5>{{ count($cart) }} {{ Str::plural('item', count($cart)) }} in your cart</h5>
                                <span>Prices already include every item discount.</span>
                            </div>

                            <form action="{{ route('cart.clear') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="clear-cart-btn">
                                    <i class="fa fa-trash-o mr-1" aria-hidden="true"></i> Clear cart
                                </button>
                            </form>
                        </div>

                        @foreach ($cart as $item)

                            @php
                                $quantity   = $item['quantity'] ?? 1;
                                $unitPrice  = $item['price'] ?? 0;
                                $wasPrice   = $item['original_price'] ?? $unitPrice;
                                $itemTotal  = $unitPrice * $quantity;

                                $stock  = $item['stock'] ?? 0;
                                $maxQty = min(10, $stock);
                            @endphp

                            <div class="cart-item row align-items-center">

                                {{-- IMAGE --}}
                                <div class="col-md-2 col-3">
                                    <div class="cart-img">
                                        @if (!empty($item['image']))
                                            <img src="{{ asset('storage/'.$item['image']) }}"
                                                 alt="{{ $item['name'] }}">
                                        @else
                                            <i class="fa fa-cutlery" aria-hidden="true"></i>
                                        @endif
                                    </div>
                                </div>

                                {{-- INFO --}}
                                <div class="col-md-4 col-9">
                                    <h6 class="cart-name">{{ $item['name'] }}</h6>

                                    <small class="cart-unit">
                                        <strong>৳{{ number_format($unitPrice, 2) }}</strong> each
                                        @if ($wasPrice > $unitPrice)
                                            <del>৳{{ number_format($wasPrice, 2) }}</del>
                                        @endif
                                    </small>

                                    <small class="cart-stock">
                                        @if ($stock <= 0)
                                            <span class="stock-out">Out of stock</span>
                                        @elseif ($stock <= 5)
                                            <span class="stock-low">Only {{ $stock }} left</span>
                                        @else
                                            <span class="stock-ok">In stock ({{ $stock }})</span>
                                        @endif
                                    </small>
                                </div>

                                {{-- QUANTITY --}}
                                <div class="col-md-3">
                                    <div class="qty-stepper">
                                        <form action="{{ route('cart.update', $item['food_id']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="minus">
                                            <button type="submit" class="qty-btn"
                                                    aria-label="Decrease quantity"
                                                    {{ $quantity <= 1 ? 'disabled' : '' }}>&minus;</button>
                                        </form>

                                        <span class="qty-value">{{ $quantity }}</span>

                                        <form action="{{ route('cart.update', $item['food_id']) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="plus">
                                            <button type="submit" class="qty-btn"
                                                    aria-label="Increase quantity"
                                                    {{ $quantity >= $maxQty ? 'disabled' : '' }}>+</button>
                                        </form>
                                    </div>

                                    <small class="qty-max">Up to {{ $maxQty }} per order</small>
                                </div>

                                {{-- LINE TOTAL + REMOVE --}}
                                <div class="col-md-3 text-right">
                                    <strong class="line-total">৳{{ number_format($itemTotal, 2) }}</strong>

                                    <form action="{{ route('cart.remove', $item['food_id']) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="remove-btn">
                                            <i class="fa fa-times mr-1" aria-hidden="true"></i> Remove
                                        </button>
                                    </form>
                                </div>

                            </div>

                        @endforeach

                    </div>

                    <a href="{{ route('menu.index') }}" class="keep-shopping">
                        <i class="fa fa-angle-left" aria-hidden="true"></i> Keep shopping
                    </a>

                </div>

                {{-- ================= SUMMARY ================= --}}
                <div class="col-lg-4">
                    <div class="cart-summary-col">
                        <div class="glass-card cart-summary">

                            <h5>Order summary</h5>
                            <small class="summary-sub">Add a coupon before you check out.</small>

                            @include('frontend.partials.coupon-box', ['totals' => $totals])

                            <a href="{{ route('order.place') }}" class="btn btn-success">
                                Proceed to checkout
                            </a>

                            <small class="summary-note">
                                Pay on delivery or by card — you choose at the next step.
                            </small>

                        </div>
                    </div>
                </div>

            </div>

        @endif

    </div>
</section>

@endsection
