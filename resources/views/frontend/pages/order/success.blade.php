@extends('frontend.master')

@section('content')

<style>
    .success-card {
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.15);
        box-shadow: 0 20px 40px rgba(0,0,0,0.35);
        color: #fff;
        padding: 40px;
        text-align: center;
    }

    .success-icon {
        font-size: 70px;
        margin-bottom: 15px;
        color: #2ecc71;
    }

    .order-info {
        margin-top: 25px;
        text-align: left;
    }

    .order-info div {
        padding: 10px 0;
        border-bottom: 1px dashed rgba(255,255,255,0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .badge-paid {
        background: #2ecc71;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }

    .badge-pending {
        background: #f1c40f;
        color: #000;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="success-card">

                {{-- ICON --}}
                <div class="success-icon">✅</div>

                {{-- TITLE --}}
                <h3 class="fw-bold mb-2">
                    Order Placed Successfully!
                </h3>

                <p class="text-muted mb-4">
                    Thank you for your order. We’ll start preparing your food shortly 🍽️
                </p>

                {{-- ORDER DETAILS --}}
                <div class="order-info">

                    <div>
                        <span>Order Number</span>
                        <strong>{{ $order->order_number }}</strong>
                    </div>

                    <div>
                        <span>Transaction Number</span>
                        <strong>{{ $order->transaction_number }}</strong>
                    </div>

                    <div>
                        <span>Total Amount</span>
                        <strong>
                            ৳{{ number_format($order->total_amount, 2) }}
                        </strong>
                    </div>

                    <div>
                        <span>Payment Method</span>
                        <strong class="text-uppercase">
                            {{ $order->payment_method }}
                        </strong>
                    </div>

                    <div>
                        <span>Payment Status</span>
                        @if ($order->payment_status === 'paid')
                            <span class="badge-paid">PAID</span>
                        @else
                            <span class="badge-pending">PENDING</span>
                        @endif
                    </div>

                    <div>
                        <span>Order Status</span>
                        <strong class="text-capitalize">
                            {{ $order->order_status }}
                        </strong>
                    </div>

                </div>

                {{-- ACTION --}}
                <div class="mt-4">
                    <a href="{{ route('home') }}"
                       class="btn btn-success px-4 py-2 fw-bold"
                       style="border-radius:30px;">
                         Back to Menu
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>

@endsection
