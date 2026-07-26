@extends('frontend.master')

@section('content')

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-radius: 18px;
        border: 1px solid rgba(255,255,255,0.15);
        box-shadow: 0 20px 40px rgba(0,0,0,0.35);
        color: #fff;
    }

    .glass-header {
        font-weight: 700;
        border-bottom: 1px solid rgba(255,255,255,0.15);
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .form-control,
    textarea {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.25);
        color: #fff;
    }

    .form-control:read-only {
        background: rgba(255,255,255,0.05);
        cursor: not-allowed;
    }

    label {
        font-weight: 600;
        color: #ddd;
    }

    .payment-box {
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: .2s;
    }

    .payment-box:hover {
        background: rgba(255,255,255,0.08);
    }

    .payment-box input {
        margin-right: 8px;
    }
</style>

<div class="container py-5">

    <h3 class="fw-bold mb-4 text-center text-success">
         Place Your Order
    </h3>

    {{-- ================= ORDER SUMMARY ================= --}}
    <div class="glass-card p-4 mb-4">
        <div class="glass-header">Order Summary</div>

        @foreach ($cart as $item)
            @php $itemTotal = $item['price'] * $item['quantity']; @endphp

            <div class="d-flex justify-content-between border-bottom py-2">
                <div>
                    <strong>{{ $item['name'] }}</strong><br>
                    <small>
                        ৳{{ number_format($item['price'], 2) }}
                        × {{ $item['quantity'] }}
                    </small>
                </div>
                <div class="fw-bold text-success">
                    ৳{{ number_format($itemTotal, 2) }}
                </div>
            </div>
        @endforeach

    </div>

    {{-- ================= COUPON + TOTALS ================= --}}
    {{-- Kept outside the order form — nesting forms is invalid HTML --}}
    <div class="mb-4">
        @include('frontend.partials.coupon-box', ['totals' => $totals])
    </div>

    {{-- ================= PLACE ORDER FORM ================= --}}
    <form action="{{ route('order.store') }}" method="POST">
        @csrf

        {{-- ================= DELIVERY INFO ================= --}}
        <div class="glass-card p-4 mb-4">
            <div class="glass-header">Delivery Information</div>

            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" class="form-control"
                       value="{{ $user->full_name }}" readonly>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" class="form-control"
                       value="{{ $user->email }}" readonly>
            </div>

            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone"
                       class="form-control"
                       value="{{ $user->phone }}" required>
            </div>

            <div class="mb-3">
                <label>Address</label>
                <textarea name="address"
                          class="form-control"
                          rows="3"
                          required>{{ $user->address ?? '' }}</textarea>
            </div>
        </div>

        {{-- ================= PAYMENT METHOD ================= --}}
        <div class="glass-card p-4 mb-4">
            <div class="glass-header">Payment Method</div>

            <label class="payment-box d-block">
                <input type="radio"
                       name="payment_method"
                       value="cod"
                       checked>
                💵 Cash on Delivery  
                <small class="d-block text-muted">
                    Pay when your food arrives
                </small>
            </label>

            <label class="payment-box d-block">
                <input type="radio"
                       name="payment_method"
                       value="stripe">
                💳 Pay with Card (Stripe)
                <small class="d-block text-muted">
                    You'll be redirected to Stripe's secure payment page
                </small>
                <small class="d-block text-warning mt-1">
                    Test mode — use card <code>4242 4242 4242 4242</code>,
                    any future expiry, any CVC.
                </small>
            </label>
        </div>

        {{-- ================= SUBMIT ================= --}}
        <div class="glass-card p-4 text-center mb-5">
            <button type="submit"
                    class="btn btn-success px-5 py-3 fw-bold"
                    style="font-size:18px;border-radius:30px;">
                ✅ Confirm & Place Order
            </button>
        </div>

    </form>

</div>

@endsection
