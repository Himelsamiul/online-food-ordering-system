@extends('frontend.master')

@section('content')

<style>
    .food-box {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(12px);
        border-radius: 18px;
        overflow: hidden;
        transition: all 0.25s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .food-box:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 35px rgba(0,0,0,0.35);
    }

    .food-img {
        height: 220px;
        background: #111;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .food-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .food-details {
        background: rgba(0, 0, 0, 0.8);
        padding: 18px;
        color: #fff;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .discount-row {
        min-height: 22px;
    }

    .discount-hidden {
        visibility: hidden;
    }

    .discount-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        background: #198754;
        color: #fff;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        z-index: 10;
    }

    .add-cart-btn {
        background: #198754;
        color: #fff;
        padding: 10px 12px;
        border-radius: 50%;
        transition: 0.2s;
    }

    .add-cart-btn:hover {
        background: #157347;
        color: #fff;
    }

    .details-link {
        text-decoration: none;
        color: inherit;
    }

    .details-link:hover {
        color: inherit;
    }
</style>

<div class="container py-5">

    {{-- SUBCATEGORY TITLE --}}
    <div class="text-center mb-5">
        <h3 class="fw-bold text-success">
            {{ $subcategory->name }}
        </h3>
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
            @endphp

            <div class="col-sm-6 col-lg-4 mb-4">

                <div class="food-box position-relative">

                    {{-- DISCOUNT BADGE --}}
                    @if ($discountPercent > 0)
                        <span class="discount-badge">
                            -{{ $discountPercent }}%
                        </span>
                    @endif

                    {{-- IMAGE (DETAILS CLICK) --}}
                    <a href="{{ route('food.details', $food->id) }}" class="details-link">
                        <div class="food-img">
                            @if ($food->image)
                                <img src="{{ asset('storage/'.$food->image) }}" alt="{{ $food->name }}">
                            @else
                                <i class="fa fa-image fa-3x text-light opacity-50"></i>
                            @endif
                        </div>
                    </a>

                    {{-- DETAILS --}}
                    <div class="food-details">

                        <div>
                            {{-- NAME (DETAILS CLICK) --}}
                            <a href="{{ route('food.details', $food->id) }}" class="details-link">
                                <h5 class="fw-bold mb-1">
                                    {{ $food->name }}
                                </h5>
                            </a>

                            @if ($food->description)
                                <p class="text-light small mb-2">
                                    {{ Str::limit($food->description, 80) }}
                                </p>
                            @endif

                            {{-- STOCK --}}
                            <p class="fw-bold mb-1 {{ $food->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                Stock:
                                {{ $food->quantity > 0 ? $food->quantity.' available' : 'Out of stock' }}
                            </p>

                            {{-- PRICE --}}
                            <p class="text-muted small mb-0">
                                Price: ৳{{ number_format($price, 2) }}
                            </p>

                            {{-- DISCOUNT (STRUCTURE SAFE) --}}
                            <p class="text-warning small discount-row {{ $discountPercent == 0 ? 'discount-hidden' : '' }}">
                                Discount: ৳{{ number_format($discountAmount, 2) }}
                            </p>
                        </div>

                        {{-- FINAL PRICE + CART --}}
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <h5 class="fw-bold text-success mb-0">
                                ৳{{ number_format($finalPrice, 2) }}
                            </h5>

                            {{-- CART BUTTON --}}
                            <a href="javascript:void(0)"
                               class="add-cart-btn"
                               title="Add to cart">
                                <i class="fa fa-shopping-cart"></i>
                            </a>
                        </div>

                    </div>

                </div>
            </div>

        @empty
            <div class="col-12 text-center">
                <div class="glass-card p-5">
                    <h5 class="fw-bold text-muted">
                        No food available
                    </h5>
                </div>
            </div>
        @endforelse

    </div>

</div>

@endsection
