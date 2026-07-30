@extends('frontend.master')

@section('title', $food->name)

@push('styles')
<style>
    .food-page .breadcrumb-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-bottom: 22px;
        color: var(--sf-faint);
        font-size: 13.5px;
    }

    .food-page .breadcrumb-row a {
        color: var(--sf-muted);
        text-decoration: none;
        transition: color .18s;
    }

    .food-page .breadcrumb-row a:hover { color: var(--sf-accent); }
    .food-page .breadcrumb-row .sep { color: rgba(255, 255, 255, .25); }
    .food-page .breadcrumb-row .current { color: var(--sf-ink); font-weight: 600; }

    /* ---------- image ---------- */
    .food-media {
        position: relative;
        border-radius: var(--sf-radius);
        overflow: hidden;
        border: 1px solid var(--sf-glass-line);
        box-shadow: var(--sf-shadow-lg);
    }

    .food-img-big {
        height: 460px;
        background: rgba(0, 0, 0, .45);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .food-img-big img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .food-img-big .no-img {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        color: rgba(255, 255, 255, .3);
        font-size: 14px;
    }

    .food-img-big .no-img i { font-size: 46px; }

    .media-flags {
        position: absolute;
        top: 14px;
        left: 14px;
        right: 14px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        pointer-events: none;
    }

    .flag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: .04em;
        background: rgba(0, 0, 0, .68);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-accent);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .flag-stack { display: flex; flex-wrap: wrap; gap: 8px; }

    .flag.is-discount {
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        border-color: transparent;
        color: var(--sf-ink);
    }

    .flag.is-out {
        background: var(--sf-danger);
        border-color: transparent;
        color: var(--sf-ink);
        text-transform: uppercase;
    }

    /* ---------- detail panel ---------- */
    .food-panel { padding: 32px; }

    .food-tag-row {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 14px;
    }

    .food-tag {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-accent);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
        line-height: 1.6;
    }

    .food-tag.is-parent { color: var(--sf-muted); background: rgba(255, 255, 255, .05); }

    .food-title {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 2.1rem;
        line-height: 1.2;
        margin-bottom: 20px;
    }

    /* ---------- price ---------- */
    .price-box {
        padding: 20px 22px;
        margin-bottom: 20px;
        border-radius: var(--sf-radius-sm);
        background: rgba(0, 0, 0, .3);
        border: 1px solid var(--sf-glass-line);
    }

    .price-line {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        gap: 12px;
    }

    .price-now {
        color: var(--sf-ink);
        font-size: 2.15rem;
        font-weight: 800;
        line-height: 1;
    }

    .price-was {
        color: var(--sf-faint);
        font-size: 17px;
        font-weight: 600;
        text-decoration: line-through;
    }

    .price-save {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 12px;
        padding: 6px 14px;
        border-radius: 20px;
        background: rgba(29, 191, 115, .16);
        border: 1px solid rgba(29, 191, 115, .4);
        color: var(--sf-green);
        font-size: 13px;
        font-weight: 700;
    }

    .price-note {
        margin: 12px 0 0;
        color: var(--sf-faint);
        font-size: 12.5px;
    }

    /* ---------- stock ---------- */
    .stock-line {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 22px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13.5px;
        font-weight: 700;
    }

    .stock-line.is-in {
        background: rgba(29, 191, 115, .14);
        border: 1px solid rgba(29, 191, 115, .38);
        color: var(--sf-green);
    }

    .stock-line.is-low {
        background: rgba(241, 184, 22, .14);
        border: 1px solid rgba(241, 184, 22, .4);
        color: var(--sf-accent);
    }

    /* Colour comes from the already-themed .text-danger in
       storefront-refresh.css, so the shade stays in one place. */
    .stock-line.is-out {
        background: rgba(231, 76, 60, .16);
        border: 1px solid rgba(231, 76, 60, .42);
    }

    /* ---------- quantity + cart ---------- */
    .buy-row {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        gap: 14px;
    }

    .qty-field { flex: 0 0 auto; }

    .qty-label {
        display: block;
        margin-bottom: 8px;
        color: var(--sf-faint);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .qty-stepper {
        display: inline-flex;
        align-items: center;
        height: 52px;
        border-radius: 30px;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        overflow: hidden;
    }

    .qty-stepper button {
        width: 46px;
        height: 100%;
        border: none;
        background: none;
        color: var(--sf-ink);
        font-size: 15px;
        line-height: 1;
        transition: background-color .16s, color .16s;
    }

    .qty-stepper button:hover:not(:disabled) {
        background: rgba(255, 255, 255, .12);
        color: var(--sf-accent);
    }

    .qty-stepper button:disabled { color: var(--sf-faint); cursor: not-allowed; }
    .qty-stepper button:focus { outline: none; background: rgba(255, 255, 255, .16); }

    .qty-stepper input {
        width: 52px;
        height: 100%;
        border: none;
        background: none;
        color: var(--sf-ink);
        font-size: 16px;
        font-weight: 700;
        text-align: center;
        -moz-appearance: textfield;
    }

    .qty-stepper input::-webkit-outer-spin-button,
    .qty-stepper input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .qty-stepper input:focus { outline: none; }

    .cart-field { flex: 1 1 220px; display: flex; flex-direction: column; }
    .cart-field .qty-label { visibility: hidden; }

    .buy-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        height: 52px;
        padding: 0 26px;
        border: none;
        border-radius: 30px;
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        color: var(--sf-ink);
        font-size: 15px;
        font-weight: 700;
        transition: transform .18s, box-shadow .18s;
    }

    .buy-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(29, 191, 115, .4);
        color: var(--sf-ink);
    }

    .buy-btn:focus { outline: none; box-shadow: 0 0 0 3px rgba(255, 255, 255, .25); }

    .buy-btn:disabled,
    .buy-btn.is-locked {
        background: rgba(255, 255, 255, .1);
        color: var(--sf-muted);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .buy-note {
        margin: 14px 0 0;
        color: var(--sf-faint);
        font-size: 12.5px;
        line-height: 1.6;
    }

    .buy-note a { color: var(--sf-accent); font-weight: 600; }
    .buy-note a:hover { color: var(--sf-accent-dark); text-decoration: none; }

    /* ---------- spec table ---------- */
    .spec-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1px;
        margin-top: 26px;
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius-sm);
        overflow: hidden;
        background: var(--sf-glass-line);
    }

    .spec-cell {
        padding: 14px 16px;
        background: rgba(0, 0, 0, .3);
    }

    .spec-cell dt {
        margin: 0 0 4px;
        color: var(--sf-faint);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .spec-cell dd {
        margin: 0;
        color: var(--sf-ink);
        font-size: 14.5px;
        font-weight: 600;
        word-break: break-word;
    }

    /* ---------- description + barcode ---------- */
    .info-panel { padding: 28px; margin-top: 26px; }

    .info-panel h5 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 17px;
        margin-bottom: 14px;
    }

    .info-panel p {
        color: var(--sf-muted);
        font-size: 14.5px;
        line-height: 1.8;
        margin-bottom: 0;
        white-space: pre-line;
    }

    /* The barcode has to sit on white to stay scannable. */
    .barcode-wrapper {
        display: inline-block;
        padding: 14px 16px 10px;
        border-radius: var(--sf-radius-sm);
        background: rgba(255, 255, 255, 1);
    }

    .barcode-wrapper svg { display: block; max-width: 100%; }

    .barcode-number {
        margin-top: 6px;
        color: rgba(0, 0, 0, .72);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 3px;
        text-align: center;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-top: 28px;
        color: var(--sf-muted);
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: color .18s;
    }

    .back-link:hover { color: var(--sf-accent); text-decoration: none; }

    @media (max-width: 991.98px) {
        .food-page .food-panel { margin-top: 24px; }
        .food-img-big { height: 340px; }
    }

    @media (max-width: 575.98px) {
        .food-panel { padding: 22px; }
        .food-title { font-size: 1.6rem; }
        .price-now { font-size: 1.75rem; }
        .food-img-big { height: 250px; }
        .qty-field, .cart-field { flex: 1 1 100%; }
        .cart-field .qty-label { display: none; }
        .qty-stepper { width: 100%; justify-content: space-between; }
        .qty-stepper input { flex: 1; }
    }
</style>
@endpush

@section('content')

@php
    $price           = $food->price;
    $discountPercent = $food->discount ?? 0;

    if ($discountPercent > 0) {
        $discountAmount = ($price * $discountPercent) / 100;
        $finalPrice     = $price - $discountAmount;
    } else {
        $discountAmount = 0;
        $finalPrice     = $price;
    }

    // CART CHECK (same as listing page)
    $cart = session('cart', []);
    $alreadyInCart = isset($cart[$food->id]);

    $stock      = (int) ($food->quantity ?? 0);
    $lowMark    = (int) ($food->low_stock_alert ?? 0);
    $outOfStock = $stock < 1;
    $lowStock   = !$outOfStock && $lowMark > 0 && $stock <= $lowMark;

    // The cart controller caps a line at 10 items, so the stepper does too.
    $maxQty = max(1, min(10, $stock));

    $subcategory  = $food->subcategory;
    $categoryName = $subcategory ? optional($subcategory->category)->name : null;
    $unitName     = optional($food->unit)->name;
@endphp

<section class="food-details-section food-page layout_padding">
    <div class="container">

        {{-- ================= BREADCRUMB ================= --}}
        <nav class="breadcrumb-row" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <a href="{{ route('menu.index') }}">Menu</a>

            @if ($subcategory)
                <span class="sep">/</span>
                <a href="{{ route('menu.index', ['category' => $subcategory->category_id, 'subcategory' => $subcategory->id]) }}">
                    {{ $subcategory->name }}
                </a>
            @endif

            <span class="sep">/</span>
            <span class="current">{{ $food->name }}</span>
        </nav>

        <div class="row">

            {{-- ================= IMAGE ================= --}}
            <div class="col-lg-6">
                <div class="food-media">
                    <div class="media-flags">
                        <div class="flag-stack">
                            @if ($outOfStock)
                                <span class="flag is-out">
                                    <i class="fa fa-ban" aria-hidden="true"></i> Sold out
                                </span>
                            @elseif ($lowStock)
                                <span class="flag">
                                    <i class="fa fa-clock-o" aria-hidden="true"></i> Only {{ $stock }} left
                                </span>
                            @endif

                            @if ($food->is_featured)
                                <span class="flag"><i class="fa fa-star" aria-hidden="true"></i> Featured</span>
                            @endif

                            @if ($food->is_popular)
                                <span class="flag"><i class="fa fa-fire" aria-hidden="true"></i> Popular</span>
                            @endif
                        </div>

                        @if ($discountPercent > 0 && !$outOfStock)
                            <span class="flag is-discount">-{{ (int) $discountPercent }}%</span>
                        @endif
                    </div>

                    <div class="food-img-big">
                        @if ($food->image)
                            <img src="{{ asset('storage/'.$food->image) }}" alt="{{ $food->name }}">
                        @else
                            <span class="no-img">
                                <i class="fa fa-cutlery" aria-hidden="true"></i>
                                No image for this dish yet
                            </span>
                        @endif
                    </div>
                </div>

                {{-- ---------- description ---------- --}}
                <div class="info-panel glass-card d-none d-lg-block">
                    <h5>About this dish</h5>
                    <p>
                        {{ $food->description ?: 'We have not written this one up yet. It is cooked to order like everything else on the menu — ask us anything on the contact page.' }}
                    </p>
                </div>
            </div>

            {{-- ================= DETAILS ================= --}}
            <div class="col-lg-6">
                <div class="food-panel glass-card">

                    @if ($categoryName || $subcategory)
                        <div class="food-tag-row">
                            @if ($categoryName)
                                <span class="food-tag is-parent">{{ $categoryName }}</span>
                            @endif

                            @if ($subcategory)
                                <span class="food-tag">{{ $subcategory->name }}</span>
                            @endif
                        </div>
                    @endif

                    <h1 class="food-title">{{ $food->name }}</h1>

                    {{-- ---------- pricing ---------- --}}
                    <div class="price-box">
                        <div class="price-line">
                            <span class="price-now">৳{{ number_format($finalPrice, 2) }}</span>

                            @if ($discountPercent > 0)
                                <span class="price-was">৳{{ number_format($price, 2) }}</span>
                            @endif
                        </div>

                        @if ($discountPercent > 0)
                            <span class="price-save">
                                <i class="fa fa-tag" aria-hidden="true"></i>
                                You save ৳{{ number_format($discountAmount, 2) }}
                                ({{ (int) $discountPercent }}% off)
                            </span>
                        @endif

                        <p class="price-note">
                            Price per {{ $unitName ? Str::lower($unitName) : 'item' }}. Delivery is added at checkout.
                        </p>
                    </div>

                    {{-- ---------- stock ---------- --}}
                    @if ($outOfStock)
                        <span class="stock-line is-out text-danger">
                            <i class="fa fa-times-circle" aria-hidden="true"></i> Out of stock
                        </span>
                    @elseif ($lowStock)
                        <span class="stock-line is-low">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            Only {{ $stock }} left in the kitchen
                        </span>
                    @else
                        <span class="stock-line is-in">
                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                            In stock — {{ $stock }} available
                        </span>
                    @endif

                    {{-- ---------- quantity + add to cart ---------- --}}
                    @if ($alreadyInCart)
                        <div class="buy-row">
                            <div class="cart-field">
                                <button type="button" class="buy-btn is-locked" disabled>
                                    <i class="fa fa-check" aria-hidden="true"></i> Already in your cart
                                </button>
                            </div>
                        </div>

                        <p class="buy-note">
                            Change how many you want on the
                            <a href="{{ route('cart.index') }}">cart page</a>.
                        </p>

                    @elseif ($outOfStock)
                        <div class="buy-row">
                            <div class="cart-field">
                                <button type="button" class="buy-btn is-locked" disabled>
                                    <i class="fa fa-ban" aria-hidden="true"></i> Out of stock
                                </button>
                            </div>
                        </div>

                        <p class="buy-note">
                            This one has sold out for now.
                            <a href="{{ route('menu.index') }}">See what else is on today</a>.
                        </p>

                    @else
                        <form action="{{ route('cart.add', $food->id) }}" method="POST"
                              id="addToCartForm" class="add-cart-form m-0">
                            @csrf

                            <div class="buy-row">
                                <div class="qty-field">
                                    <label class="qty-label" for="qtyInput">Quantity</label>

                                    <div class="qty-stepper">
                                        <button type="button" id="qtyDown" aria-label="Fewer">
                                            <i class="fa fa-minus" aria-hidden="true"></i>
                                        </button>

                                        <input type="number" id="qtyInput" name="quantity"
                                               value="1" min="1" max="{{ $maxQty }}" step="1"
                                               inputmode="numeric" aria-label="Quantity">

                                        <button type="button" id="qtyUp" aria-label="More">
                                            <i class="fa fa-plus" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="cart-field">
                                    <span class="qty-label" aria-hidden="true">Add</span>
                                    <button type="submit" class="buy-btn" id="addToCartBtn">
                                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                        Add to cart
                                    </button>
                                </div>
                            </div>
                        </form>

                        <p class="buy-note" id="buyNote">
                            Up to {{ $maxQty }} per order.
                            @guest('frontend')
                                You will be asked to <a href="{{ route('login') }}">sign in</a> first.
                            @endguest
                        </p>
                    @endif

                    {{-- ---------- specs ---------- --}}
                    <dl class="spec-grid">
                        <div class="spec-cell">
                            <dt>SKU</dt>
                            <dd>{{ $food->sku ?: 'Not set' }}</dd>
                        </div>

                        <div class="spec-cell">
                            <dt>Sold by</dt>
                            <dd>{{ $unitName ?: 'Item' }}</dd>
                        </div>

                        <div class="spec-cell">
                            <dt>Category</dt>
                            <dd>{{ $categoryName ?: 'Uncategorised' }}</dd>
                        </div>

                        <div class="spec-cell">
                            <dt>Section</dt>
                            <dd>{{ $subcategory->name ?? 'Uncategorised' }}</dd>
                        </div>
                    </dl>

                </div>

                {{-- ---------- description, phone/tablet position ---------- --}}
                <div class="info-panel glass-card d-lg-none">
                    <h5>About this dish</h5>
                    <p>
                        {{ $food->description ?: 'We have not written this one up yet. It is cooked to order like everything else on the menu — ask us anything on the contact page.' }}
                    </p>
                </div>

                @if ($food->barcode)
                    <div class="info-panel glass-card">
                        <h5>Barcode</h5>

                        <div class="barcode-wrapper">
                            <svg id="barcode"></svg>
                            <div class="barcode-number">{{ $food->barcode }}</div>
                        </div>
                    </div>
                @endif

                <a href="{{ route('menu.index') }}" class="back-link">
                    <i class="fa fa-angle-left" aria-hidden="true"></i> Back to the menu
                </a>
            </div>

        </div>

        {{-- ==================== REVIEWS ==================== --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="info-panel glass-card">
                    <h5>Reviews</h5>

                    @if ($food->hasRating())
                        <div class="review-summary">
                            <div class="review-score">
                                <span class="review-score-value">{{ number_format((float) $food->rating_avg, 1) }}</span>
                                <x-stars :value="$food->rating_avg" size="lg" />
                                <small>{{ $food->rating_count }} {{ Str::plural('review', $food->rating_count) }}</small>
                            </div>

                            <div class="review-bars">
                                @foreach ($distribution as $star => $count)
                                    @php
                                        // Guard the divide: rating_count is never 0 inside
                                        // this branch, but the bar must not blow up if it is.
                                        $pct = $food->rating_count > 0
                                            ? round($count / $food->rating_count * 100)
                                            : 0;
                                    @endphp
                                    <div class="review-bar">
                                        <span class="review-bar-label">{{ $star }} <i class="fa fa-star" aria-hidden="true"></i></span>
                                        <span class="review-bar-track">
                                            <span class="review-bar-fill" style="width: {{ $pct }}%"></span>
                                        </span>
                                        <span class="review-bar-count">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="review-list">
                            @foreach ($reviews as $review)
                                <div class="review-item">
                                    <div class="review-item-head">
                                        <span class="review-avatar">
                                            {{ mb_substr($review->customer_name ?: '?', 0, 1) }}
                                        </span>

                                        <span class="review-who">
                                            <strong>
                                                {{ $review->customer_name }}
                                                <span class="review-verified">
                                                    <i class="fa fa-check-circle" aria-hidden="true"></i> Verified order
                                                </span>
                                            </strong>
                                            <span class="review-when">{{ $review->created_at?->diffForHumans() }}</span>
                                        </span>

                                        <x-stars :value="$review->rating" size="sm" />
                                    </div>

                                    @if ($review->title)
                                        <span class="review-title">{{ $review->title }}</span>
                                    @endif

                                    @if ($review->comment)
                                        <p class="review-body">{{ $review->comment }}</p>
                                    @endif

                                    @if ($review->hasReply())
                                        <div class="review-reply">
                                            <strong>{{ $review->admin_reply_by ?: 'Feane' }} replied</strong>
                                            <p>{{ $review->admin_reply }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="review-empty">
                            <i class="fa fa-star-o" aria-hidden="true"></i>
                            <p>
                                No reviews yet.<br>
                                Only customers who have received this item can leave one.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</section>

@endsection

@push('scripts')
{{-- BARCODE SCRIPT --}}
@if ($food->barcode)
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    JsBarcode("#barcode", "{{ $food->barcode }}", {
        format: "CODE128",
        lineColor: "#000000",
        background: "#ffffff",
        width: 2,
        height: 70,
        displayValue: false
    });
</script>
@endif

@if (!$alreadyInCart && !$outOfStock)
<script>
/*
 * Quantity stepper + add to cart.
 *
 * CartController::add() always puts a single unit in the cart and ignores any
 * quantity in the request, so anything above one is topped up afterwards with
 * the cart's own "plus" route, one step at a time. If the POST does not come
 * back as JSON — a signed-out visitor being bounced to the login page, for
 * instance — the form is submitted normally and the browser takes over.
 */
$(function () {
    var MAX        = {{ $maxQty }};
    var UPDATE_URL = "{{ route('cart.update', $food->id) }}";
    var CART_URL   = "{{ route('cart.index') }}";
    var TOKEN      = $('meta[name="csrf-token"]').attr('content');

    var $form = $('#addToCartForm');
    var $btn  = $('#addToCartBtn');
    var $qty  = $('#qtyInput');

    function clamp(value) {
        var n = parseInt(value, 10);

        if (isNaN(n) || n < 1) { n = 1; }
        if (n > MAX)           { n = MAX; }

        $qty.val(n);
        $('#qtyDown').prop('disabled', n <= 1);
        $('#qtyUp').prop('disabled', n >= MAX);

        return n;
    }

    $('#qtyDown').on('click', function () { clamp($qty.val() - 1); });
    $('#qtyUp').on('click',   function () { clamp(Number($qty.val()) + 1); });
    $qty.on('change blur', function () { clamp(this.value); });

    clamp($qty.val());

    function done(message) {
        $btn.prop('disabled', true).addClass('is-locked')
            .html('<i class="fa fa-check" aria-hidden="true"></i> In your cart');

        $('#qtyDown, #qtyUp, #qtyInput').prop('disabled', true);

        $('#buyNote').html(
            'Change how many you want on the <a href="' + CART_URL + '">cart page</a>.'
        );

        Swal.fire({
            icon: 'success',
            title: 'Added to cart',
            text: message,
            timer: 1600,
            showConfirmButton: false
        });
    }

    // One "plus" per extra unit, in sequence so the session is never raced.
    function topUp(remaining, message) {
        if (remaining < 1) {
            done(message);
            return;
        }

        $.post(UPDATE_URL, { _token: TOKEN, action: 'plus' })
            .always(function () { topUp(remaining - 1, message); });
    }

    $form.on('submit', function (e) {
        e.preventDefault();

        var wanted = clamp($qty.val());

        $btn.prop('disabled', true);

        $.ajax({
            url:      $form.attr('action'),
            type:     'POST',
            dataType: 'json',
            data:     $form.serialize()
        })
        .done(function (res) {
            if (res.status === 'error') {
                $btn.prop('disabled', false);

                Swal.fire({ icon: 'error', title: 'Not added', text: res.message });
                return;
            }

            if (res.cart_count !== undefined) {
                $('#cartCount').text(res.cart_count).show();
            }

            topUp(wanted - 1, wanted > 1
                ? wanted + ' added to your cart.'
                : res.message);
        })
        .fail(function () {
            // Not JSON (usually the sign-in redirect) — let the browser post it.
            $form[0].submit();
        });
    });
});
</script>
@endif
@endpush
