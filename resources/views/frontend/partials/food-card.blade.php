@php
    $price           = $food->price;
    $discountPercent = $food->discount ?? 0;
    $discountAmount  = $discountPercent > 0 ? ($price * $discountPercent) / 100 : 0;
    $finalPrice      = $price - $discountAmount;

    $cart          = session('cart', []);
    $alreadyInCart = isset($cart[$food->id]);

    $stock      = (int) ($food->quantity ?? 0);
    $lowMark    = (int) ($food->low_stock_alert ?? 0);
    $outOfStock = $stock < 1;
    $lowStock   = !$outOfStock && $lowMark > 0 && $stock <= $lowMark;

    // Nothing can be added twice, and nothing sold out can be added at all —
    // the cart controller rejects both, so the button says so up front.
    $locked = $alreadyInCart || $outOfStock;

    /*
     * The parent category is only shown when the caller eager-loaded it.
     * The menu page loads `subcategory.category`; the home page loads
     * `subcategory` alone, and touching ->category there would fire one
     * extra query per card.
     */
    $subcategory  = $food->subcategory;
    $categoryName = $subcategory && $subcategory->relationLoaded('category')
        ? optional($subcategory->category)->name
        : null;
@endphp

<div class="col-6 col-lg-4 col-xl-3 mb-4">
    <div class="food-box position-relative {{ $outOfStock ? 'is-out' : '' }}" data-food-id="{{ $food->id }}">

        <div class="food-media">
            @if ($discountPercent > 0 && !$outOfStock)
                <span class="discount-badge">-{{ (int) $discountPercent }}%</span>
            @endif

            @if ($outOfStock)
                <span class="stock-flag is-out">
                    <i class="fa fa-ban" aria-hidden="true"></i> Sold out
                </span>
            @elseif ($lowStock)
                <span class="stock-flag">
                    <i class="fa fa-clock-o" aria-hidden="true"></i> Only {{ $stock }} left
                </span>
            @endif

            {{-- Second link to the same place: hidden from keyboard and screen
                 readers so the dish is a single stop, not two. --}}
            <a href="{{ route('food.details', $food->id) }}" class="details-link"
               tabindex="-1" aria-hidden="true">
                <div class="food-img">
                    @if ($food->image)
                        <img src="{{ asset('storage/'.$food->image) }}" alt="{{ $food->name }}" loading="lazy">
                    @else
                        <i class="fa fa-cutlery fa-3x" aria-hidden="true"></i>
                    @endif
                </div>
            </a>
        </div>

        <div class="food-details">

            <div>
                @if ($categoryName || $subcategory)
                    <div class="food-meta">
                        @if ($categoryName)
                            <span class="food-tag is-parent">{{ $categoryName }}</span>
                        @endif

                        @if ($subcategory)
                            <span class="food-tag">{{ $subcategory->name }}</span>
                        @endif
                    </div>
                @endif

                <a href="{{ route('food.details', $food->id) }}" class="details-link">
                    <h6 class="food-name">{{ $food->name }}</h6>
                </a>

                {{-- Only shown once something has actually been rated; an
                     empty star row on every card reads as "nobody likes this". --}}
                @if ($food->rating_count > 0)
                    <span class="food-rating">
                        <x-stars :value="$food->rating_avg" size="sm" />
                        <strong>{{ number_format((float) $food->rating_avg, 1) }}</strong>
                        <span>({{ $food->rating_count }})</span>
                    </span>
                @endif

                @if ($food->description)
                    <p class="food-desc">{{ Str::limit($food->description, 60) }}</p>
                @endif
            </div>

            <div class="food-foot">
                <div class="price-block">
                    <span class="price-now">৳{{ number_format($finalPrice, 2) }}</span>
                    @if ($discountPercent > 0)
                        <span class="price-was">৳{{ number_format($price, 2) }}</span>
                    @endif
                </div>

                {{-- Submitted over AJAX on the menu page so filters and scroll
                     position survive; a plain POST everywhere else. --}}
                <form action="{{ route('cart.add', $food->id) }}" method="POST" class="add-cart-form m-0">
                    @csrf
                    <button type="submit"
                            class="add-cart-btn {{ $locked ? 'disabled-btn' : '' }}"
                            title="{{ $outOfStock ? 'Out of stock' : ($alreadyInCart ? 'Already in cart' : 'Add to cart') }}"
                            {{ $locked ? 'disabled' : '' }}>
                        <i class="fa {{ $alreadyInCart ? 'fa-check' : ($outOfStock ? 'fa-ban' : 'fa-shopping-cart') }}"
                           aria-hidden="true"></i>
                        <span class="add-cart-label">
                            {{ $outOfStock ? 'Sold out' : ($alreadyInCart ? 'In cart' : 'Add') }}
                        </span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
