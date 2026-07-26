@php
    $price           = $food->price;
    $discountPercent = $food->discount ?? 0;
    $discountAmount  = $discountPercent > 0 ? ($price * $discountPercent) / 100 : 0;
    $finalPrice      = $price - $discountAmount;

    $cart          = session('cart', []);
    $alreadyInCart = isset($cart[$food->id]);
@endphp

<div class="col-6 col-lg-4 col-xl-3 mb-4">
    <div class="food-box position-relative" data-food-id="{{ $food->id }}">

        @if ($discountPercent > 0)
            <span class="discount-badge">-{{ (int) $discountPercent }}%</span>
        @endif

        <a href="{{ route('food.details', $food->id) }}" class="details-link">
            <div class="food-img">
                @if ($food->image)
                    <img src="{{ asset('storage/'.$food->image) }}" alt="{{ $food->name }}" loading="lazy">
                @else
                    <i class="fa fa-cutlery fa-3x text-light opacity-50"></i>
                @endif
            </div>
        </a>

        <div class="food-details">

            <div>
                @if ($food->subcategory)
                    <span class="food-tag">{{ $food->subcategory->name }}</span>
                @endif

                <a href="{{ route('food.details', $food->id) }}" class="details-link">
                    <h6 class="food-name">{{ $food->name }}</h6>
                </a>

                @if ($food->description)
                    <p class="food-desc">{{ Str::limit($food->description, 55) }}</p>
                @endif
            </div>

            <div class="d-flex justify-content-between align-items-end mt-2">
                <div class="price-block">
                    <span class="price-now">৳{{ number_format($finalPrice, 2) }}</span>
                    @if ($discountPercent > 0)
                        <span class="price-was">৳{{ number_format($price, 2) }}</span>
                    @endif
                </div>

                {{-- Submitted over AJAX so filters and scroll position survive --}}
                <form action="{{ route('cart.add', $food->id) }}" method="POST" class="add-cart-form m-0">
                    @csrf
                    <button type="submit"
                            class="add-cart-btn {{ $alreadyInCart ? 'disabled-btn' : '' }}"
                            title="{{ $alreadyInCart ? 'Already in cart' : 'Add to cart' }}"
                            {{ $alreadyInCart ? 'disabled' : '' }}>
                        <i class="fa {{ $alreadyInCart ? 'fa-check' : 'fa-shopping-cart' }}"></i>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
