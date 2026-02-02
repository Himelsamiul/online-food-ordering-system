@extends('frontend.master')

@section('content')

<style>
    /* ================= CART CLEAR BAR ================= */
    .clear-cart-bar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 25px;
        position: relative;
        z-index: 5;
    }

    .clear-cart-btn {
        background: linear-gradient(135deg, #dc3545, #b02a37);
        color: #fff;
        border: none;
        padding: 12px 30px;
        font-weight: 800;
        border-radius: 30px;
        box-shadow: 0 12px 30px rgba(220,53,69,0.6);
        cursor: pointer;
        transition: .25s;
    }

    .clear-cart-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 40px rgba(220,53,69,0.8);
    }

    /* ================= CART BOX ================= */
    .cart-box {
        background: rgba(0,0,0,0.9);
        border-radius: 20px;
        padding: 30px;
        color: #fff;
        position: relative;
        z-index: 5;
    }

    .cart-item {
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding: 20px 0;
    }

    .cart-img {
        width: 85px;
        height: 85px;
        background: #111;
        border-radius: 14px;
        overflow: hidden;
    }

    .cart-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .qty-btn {
        background: #198754;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 8px;
        font-weight: 700;
    }

    .qty-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
    }

    .remove-btn {
        background: #dc3545;
        color: #fff;
        border: none;
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 600;
    }

    /* ================= SUMMARY ================= */
    .cart-summary {
        background: linear-gradient(
            135deg,
            rgba(25,135,84,0.25),
            rgba(25,135,84,0.05)
        );
        border-radius: 18px;
        padding: 25px;
        margin-top: 40px;
        box-shadow: 0 10px 30px rgba(25,135,84,0.35);
        position: relative;
        z-index: 5;
    }

    .stock-ok { color: #2ecc71; font-weight: 600; }
    .stock-low { color: #f1c40f; font-weight: 600; }
    .stock-out { color: #e74c3c; font-weight: 700; }

    /* ================= EMPTY CART ================= */
    .empty-cart {
        background: rgba(0,0,0,0.85);
        border-radius: 20px;
        padding: 60px 20px;
        text-align: center;
        color: #aaa;
        position: relative;
        z-index: 5;
    }


    /* ================= SUMMARY STRONG HIGHLIGHT ================= */
.cart-summary {
    background: rgba(0, 0, 0, 0.92);   /* strong dark */
    border-radius: 20px;
    padding: 30px;
    margin-top: 45px;
    border: 1px solid rgba(25,135,84,0.4);
    box-shadow:
        0 15px 40px rgba(0,0,0,0.8),
        0 0 25px rgba(25,135,84,0.35);
    position: relative;
    z-index: 6;
}

/* total text stronger */
.cart-summary h4 {
    color: #2ecc71;
    font-weight: 800;
    font-size: 26px;
}

/* subtitle visible */
.cart-summary small {
    color: #bfbfbf;
}

/* ================= ORDER BUTTON POP ================= */
.cart-summary .btn-success {
    background: linear-gradient(135deg, #198754, #2ecc71);
    border: none;
    padding: 14px 36px;
    font-size: 16px;
    font-weight: 800;
    border-radius: 30px;
    box-shadow:
        0 10px 30px rgba(25,135,84,0.7),
        inset 0 0 0 rgba(255,255,255,0);
    transition: .25s;
}

.cart-summary .btn-success:hover {
    transform: translateY(-2px);
    box-shadow:
        0 14px 40px rgba(25,135,84,0.9);
}

</style>

<div class="container py-5">

    <h3 class="fw-bold text-success mb-4 text-center">
        🛒 Your Cart
    </h3>

    {{-- ================= EMPTY CART ================= --}}
    @if (!isset($cart) || count($cart) === 0)

        <div class="empty-cart">
            <h4 class="fw-bold mb-3">
                Your cart is empty
            </h4>
            <p class="mb-4">
                Looks like you haven’t added anything yet.
            </p>
            <a href="{{ route('home') }}" class="btn btn-success px-4 py-2">
                🍔 Explore Our Menu
            </a>
        </div>

    @else

        {{-- ================= CLEAR FULL CART ================= --}}
        <div class="clear-cart-bar">
            <form action="{{ route('cart.clear') }}" method="POST">
                @csrf
                <button type="submit" class="clear-cart-btn">
                    🧹 Clear Full Cart
                </button>
            </form>
        </div>

        @php $grandTotal = 0; @endphp

        <div class="cart-box">

            {{-- ================= CART ITEMS ================= --}}
            @foreach ($cart as $item)

                @php
                    $quantity   = $item['quantity'] ?? 1;
                    $unitPrice  = $item['price'] ?? 0;
                    $itemTotal  = $unitPrice * $quantity;
                    $grandTotal += $itemTotal;

                    $stock = $item['stock'] ?? 0;
                    $maxQty = min(10, $stock);
                @endphp

                <div class="cart-item row align-items-center">

                    {{-- IMAGE --}}
                    <div class="col-md-2">
                        <div class="cart-img">
                            @if (!empty($item['image']))
                                <img src="{{ asset('storage/'.$item['image']) }}">
                            @endif
                        </div>
                    </div>

{{-- INFO --}}
<div class="col-md-3">
    <h6 class="fw-bold mb-1">{{ $item['name'] }}</h6>

    <small class="text-muted d-block">
        Original Price: ৳{{ number_format($item['original_price'], 2) }}
    </small>

    <small class="text-muted d-block">
        Unit Price: ৳{{ number_format($item['price'], 2) }}
    </small>

    @if ($stock <= 0)
        <small class="stock-out">Out of stock</small>
    @elseif ($stock <= 5)
        <small class="stock-low">Limited ({{ $stock }} left)</small>
    @else
        <small class="stock-ok">In stock ({{ $stock }})</small>
    @endif
</div>


                    {{-- QUANTITY --}}
                    <div class="col-md-3">
                        <div class="d-flex align-items-center gap-2">

                            <form action="{{ route('cart.update', $item['food_id']) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="minus">
                                <button class="qty-btn" {{ $quantity <= 1 ? 'disabled' : '' }}>−</button>
                            </form>

                            <strong>{{ $quantity }}</strong>

                            <form action="{{ route('cart.update', $item['food_id']) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="plus">
                                <button class="qty-btn" {{ $quantity >= $maxQty ? 'disabled' : '' }}>+</button>
                            </form>

                        </div>

                        <small class="text-warning">
                            Max allowed: {{ $maxQty }}
                        </small>
                    </div>

                    {{-- ITEM TOTAL --}}
                    <div class="col-md-2">
                        <strong class="text-success">
                            ৳{{ number_format($itemTotal, 2) }}
                        </strong>
                    </div>

                    {{-- REMOVE --}}
                    <div class="col-md-2 text-end">
                        <form action="{{ route('cart.remove', $item['food_id']) }}" method="POST">
                            @csrf
                            <button class="remove-btn">Remove</button>
                        </form>
                    </div>

                </div>

            @endforeach

        </div>

        {{-- ================= SUMMARY (OUTSIDE CART BOX) ================= --}}
        <div class="cart-summary d-flex justify-content-between align-items-center">

            <div>
                <h4 class="fw-bold mb-1">
                    Total: ৳{{ number_format($grandTotal, 2) }}
                </h4>
                <small class="text-muted">
                    Calculated using discounted prices
                </small>
            </div>

            <button class="btn btn-success px-4 py-2" disabled>
                Place Order (Coming Soon)
            </button>

        </div>

    @endif

</div>

@endsection
