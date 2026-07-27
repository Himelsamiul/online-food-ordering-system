{{--
    LEGACY PAGE — kept, not reachable.

    `menu.foods` (/menu/{subcategory}) now redirects into /menu with the
    category + subcategory filter pre-applied, so MenuController never renders
    this view any more. It is left in place, retheme only, in case the old
    drill-down route is ever wired back up.

    Expects: $subcategory, $foods
--}}
@extends('frontend.master')

@section('title', $subcategory->name ?? 'Food')

@push('styles')
<style>
    .legacy-page .sf-page-head { margin-bottom: 26px; }

    .legacy-page .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .legacy-page .sf-page-head p {
        color: var(--sf-muted);
        margin-bottom: 0;
    }

    .food-box {
        background: var(--sf-glass);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius);
        overflow: hidden;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .food-box:hover {
        transform: translateY(-6px);
        border-color: var(--sf-accent);
        box-shadow: var(--sf-shadow-lg);
    }

    .food-img {
        height: 220px;
        background: rgba(0, 0, 0, .45);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
    }

    .food-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .food-img i { color: rgba(255, 255, 255, .3); }

    .food-details {
        background: rgba(0, 0, 0, .34);
        padding: 18px;
        color: var(--sf-ink);
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .food-details h5 {
        color: var(--sf-ink);
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .food-desc {
        color: var(--sf-muted);
        font-size: 13px;
        line-height: 1.55;
        margin-bottom: 8px;
    }

    .food-meta-line {
        color: var(--sf-faint);
        font-size: 12.5px;
        margin-bottom: 2px;
    }

    /* .stock-out takes its colour from the already-themed .text-danger in
       storefront-refresh.css, so the shade stays in one place. */
    .stock-in  { color: var(--sf-green); font-weight: 700; }
    .stock-out { font-weight: 700; }

    .price-final {
        color: var(--sf-ink);
        font-size: 19px;
        font-weight: 800;
        margin-bottom: 0;
    }

    .discount-row {
        min-height: 22px;
        color: var(--sf-accent);
        font-size: 12.5px;
        margin-bottom: 0;
    }

    .discount-hidden { visibility: hidden; }

    .discount-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 10;
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        color: var(--sf-ink);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .45);
    }

    .add-cart-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        color: var(--sf-ink);
        transition: transform .18s, box-shadow .18s;
    }

    .add-cart-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, .45);
        color: var(--sf-ink);
    }

    .add-cart-btn.disabled-btn,
    .add-cart-btn:disabled {
        background: rgba(255, 255, 255, .12);
        color: var(--sf-muted);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .cart-hint {
        display: block;
        margin-top: 6px;
        color: var(--sf-accent);
        font-size: 11.5px;
        line-height: 1.4;
    }

    .cart-hint span { color: var(--sf-faint); }

    .details-link,
    .details-link:hover,
    .details-link:focus {
        text-decoration: none;
        color: inherit;
    }

    .details-link:hover h5 { color: var(--sf-accent); }

    .food-note {
        padding: 18px 22px;
        border-radius: var(--sf-radius-sm);
        background: rgba(29, 191, 115, .12);
        border: 1px solid rgba(29, 191, 115, .35);
        color: rgba(255, 255, 255, .85);
        text-align: center;
        font-size: 14.5px;
        line-height: 1.65;
    }

    .legacy-empty {
        text-align: center;
        padding: 54px 22px;
        border-radius: var(--sf-radius);
        background: var(--sf-glass);
        border: 1px dashed var(--sf-glass-line);
    }

    .legacy-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: rgba(0, 0, 0, .85);
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        box-shadow: 0 12px 26px rgba(0, 0, 0, .45);
    }

    .legacy-empty h5 { color: var(--sf-ink); font-weight: 700; margin-bottom: 6px; }
    .legacy-empty p { color: var(--sf-muted); margin-bottom: 20px; }
</style>
@endpush

@section('content')

<section class="legacy-foods-section legacy-page layout_padding">
    <div class="container">

        {{-- SUBCATEGORY TITLE --}}
        <div class="sf-page-head text-center">
            <h2>{{ $subcategory->name }}</h2>
            <p>Everything we cook in this section, ready to add to your cart.</p>
        </div>

        {{-- FOOD NOTE --}}
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="food-note">
                    Freshly prepared items are listed below. Prices move with the offers we are
                    running, so what you see here is what you pay.
                </div>
            </div>
        </div>

        <div class="row">

            @forelse ($foods as $food)

                @php
                    $price = $food->price;
                    $discountPercent = $food->discount ?? 0;

                    if ($discountPercent > 0) {
                        $discountAmount = ($price * $discountPercent) / 100;
                        $finalPrice = $price - $discountAmount;
                    } else {
                        $discountAmount = 0;
                        $finalPrice = $price;
                    }

                    $cart = session('cart', []);
                    $alreadyInCart = isset($cart[$food->id]);
                @endphp

                <div class="col-sm-6 col-lg-4 mb-4">

                    <div class="food-box position-relative">

                        {{-- DISCOUNT BADGE --}}
                        @if ($discountPercent > 0)
                            <span class="discount-badge">
                                -{{ (int) $discountPercent }}%
                            </span>
                        @endif

                        {{-- IMAGE --}}
                        <a href="{{ route('food.details', $food->id) }}" class="details-link">
                            <div class="food-img">
                                @if ($food->image)
                                    <img src="{{ asset('storage/'.$food->image) }}" alt="{{ $food->name }}">
                                @else
                                    <i class="fa fa-cutlery fa-3x" aria-hidden="true"></i>
                                @endif
                            </div>
                        </a>

                        {{-- DETAILS --}}
                        <div class="food-details">

                            <div>
                                <a href="{{ route('food.details', $food->id) }}" class="details-link">
                                    <h5>{{ $food->name }}</h5>
                                </a>

                                @if ($food->description)
                                    <p class="food-desc">
                                        {{ Str::limit($food->description, 80) }}
                                    </p>
                                @endif

                                <p class="food-meta-line {{ $food->quantity > 0 ? 'stock-in' : 'stock-out text-danger' }}">
                                    {{ $food->quantity > 0 ? $food->quantity.' in stock' : 'Out of stock' }}
                                </p>

                                <p class="food-meta-line">
                                    Price: ৳{{ number_format($price, 2) }}
                                </p>

                                <p class="discount-row {{ $discountPercent == 0 ? 'discount-hidden' : '' }}">
                                    You save ৳{{ number_format($discountAmount, 2) }}
                                </p>
                            </div>

                            {{-- FINAL PRICE + CART --}}
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <p class="price-final">
                                    ৳{{ number_format($finalPrice, 2) }}
                                </p>

                                @if ($alreadyInCart)
                                    <div class="text-right">
                                        <button class="add-cart-btn disabled-btn" disabled>
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                        </button>
                                        <small class="cart-hint">
                                            Already in cart<br>
                                            <span>Change the amount from your cart</span>
                                        </small>
                                    </div>
                                @else
                                    <form action="{{ route('cart.add', $food->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit"
                                                class="add-cart-btn"
                                                title="{{ $food->quantity < 1 ? 'Out of stock' : 'Add to cart' }}"
                                                {{ $food->quantity < 1 ? 'disabled' : '' }}>
                                            <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>

                    </div>
                </div>

            @empty
                <div class="col-12">
                    <div class="legacy-empty">
                        <div class="legacy-empty-icon">
                            <i class="fa fa-cutlery" aria-hidden="true"></i>
                        </div>

                        <h5>Nothing in this section yet</h5>
                        <p>The kitchen has not put anything here. The rest of the menu is still open.</p>

                        <a href="{{ route('menu.index') }}" class="btn btn-warning">
                            Show the full menu
                        </a>
                    </div>
                </div>
            @endforelse

        </div>

    </div>
</section>

@endsection
