@extends('frontend.master')

@section('content')

<style>
    .food-details-box {
        background: rgba(15,15,15,0.95);
        border-radius: 20px;
        padding: 30px;
        color: #eaeaea;
    }

    .food-img-big {
        height: 380px;
        background: #000;
        border-radius: 18px;
        overflow: hidden;
    }

    .food-img-big img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .info-row {
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding: 14px 0;
    }

    .label {
        color: #9aa0a6;
        font-size: 14px;
    }

    .value {
        color: #ffffff;
        font-weight: 500;
    }

    .price-final {
        font-size: 26px;
        font-weight: 700;
        color: #2ecc71;
    }

    .add-cart-btn {
        background: #198754;
        color: #fff;
        padding: 14px 30px;
        border-radius: 30px;
        font-weight: 600;
        transition: .25s;
        border: none;
    }

    .add-cart-btn:hover {
        background: #157347;
        color: #fff;
    }

    .badge-stock {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .barcode-wrapper {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px;
        display: inline-block;
    }

    .barcode-number {
        text-align: center;
        font-size: 14px;
        letter-spacing: 3px;
        margin-top: 6px;
        color: #333;
        font-weight: 600;
    }
</style>

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

    // ✅ CART CHECK (same as listing page)
    $cart = session('cart', []);
    $alreadyInCart = isset($cart[$food->id]);
@endphp

<div class="container py-5">

    <div class="row g-4">

        {{-- IMAGE --}}
        <div class="col-lg-6">
            <div class="food-img-big">
                @if ($food->image)
                    <img src="{{ asset('storage/'.$food->image) }}" alt="{{ $food->name }}">
                @else
                    <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                        No Image Available
                    </div>
                @endif
            </div>
        </div>

        {{-- DETAILS --}}
        <div class="col-lg-6">
            <div class="food-details-box h-100">

                <h2 class="fw-bold mb-1">{{ $food->name }}</h2>

                <p class="label mb-3">
                    {{ $food->subcategory->category->name }} →
                    {{ $food->subcategory->name }}
                </p>

                {{-- SKU --}}
                <div class="info-row">
                    <div class="label">SKU</div>
                    <div class="value">{{ $food->sku ?? 'N/A' }}</div>
                </div>

                {{-- STOCK --}}
                <div class="my-3">
                    @if ($food->quantity > 0)
                        <span class="badge-stock bg-success">
                            In Stock ({{ $food->quantity }})
                        </span>
                    @else
                        <span class="badge-stock bg-danger">
                            Out of Stock
                        </span>
                    @endif
                </div>

                {{-- DESCRIPTION --}}
                <div class="info-row">
                    <div class="label">Description</div>
                    <div class="value">
                        {{ $food->description ?? 'No description provided.' }}
                    </div>
                </div>

                {{-- PRICING --}}
                <div class="info-row">
                    <div class="label">Pricing</div>

                    <div class="value">
                        Original: ৳{{ number_format($price, 2) }}
                    </div>

                    @if ($discountPercent > 0)
                        <div class="value text-warning">
                            Discount: {{ $discountPercent }}%
                            (৳{{ number_format($discountAmount, 2) }})
                        </div>
                    @endif

                    <div class="price-final mt-2">
                        ৳{{ number_format($finalPrice, 2) }}
                    </div>
                </div>

                {{-- BARCODE --}}
                <div class="info-row">
                    <div class="label mb-2">Barcode</div>

                    @if ($food->barcode)
                        <div class="barcode-wrapper">
                            <svg id="barcode"></svg>
                            <div class="barcode-number">
                                {{ $food->barcode }}
                            </div>
                        </div>
                    @else
                        <div class="value">No barcode available</div>
                    @endif
                </div>

                {{-- CART ACTION --}}
                <div class="mt-4">

                    @if ($alreadyInCart)
                        <button class="btn btn-secondary" disabled>
                            <i class="fa fa-check me-2"></i>
                            Already in Cart
                        </button>

                        <small class="d-block text-warning mt-2">
                            Please update quantity from cart
                        </small>

                    @elseif ($food->quantity > 0)
                        <form action="{{ route('cart.add', $food->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="add-cart-btn">
                                <i class="fa fa-shopping-cart me-2"></i>
                                Add to Cart
                            </button>
                        </form>

                    @else
                        <button class="btn btn-secondary" disabled>
                            Out of Stock
                        </button>
                    @endif

                </div>

            </div>
        </div>

    </div>

</div>

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

@endsection
