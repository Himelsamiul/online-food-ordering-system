@extends('frontend.master')

@section('title', 'Checkout')

@push('styles')
<style>
    .sf-page-head h2 { color: var(--sf-ink); font-weight: 700; }
    .sf-page-head p  { color: var(--sf-muted); margin-bottom: 0; }
    .sf-page-head    { margin-bottom: 28px; }

    .checkout-card { padding: 24px 22px; margin-bottom: 22px; }

    .checkout-head {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 15px;
        color: var(--sf-ink);
        border-bottom: 1px solid var(--sf-glass-line);
        padding-bottom: 12px;
        margin-bottom: 18px;
    }

    .checkout-head i { color: var(--sf-accent); }

    .checkout-step {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 12.5px;
        font-weight: 800;
        color: rgba(0, 0, 0, .88);
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        flex: 0 0 auto;
    }

    /* ================= PAYMENT CARDS ================= */
    .payment-box {
        display: block;
        position: relative;
        border: 1px solid var(--sf-glass-line);
        background: rgba(255, 255, 255, .04);
        border-radius: var(--sf-radius-sm);
        padding: 16px 18px 16px 50px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: border-color .18s, background-color .18s, box-shadow .18s;
    }

    .payment-box:hover { background: rgba(255, 255, 255, .08); }

    .payment-box.is-selected {
        border-color: var(--sf-green);
        background: rgba(29, 191, 115, .1);
        box-shadow: 0 0 0 3px rgba(29, 191, 115, .14);
    }

    .payment-box input {
        position: absolute;
        left: 18px;
        top: 20px;
        margin: 0;
        accent-color: var(--sf-green);
    }

    .payment-box .pay-title {
        display: flex;
        align-items: center;
        gap: 9px;
        font-weight: 700;
        font-size: 15px;
        color: var(--sf-ink);
    }

    .payment-box .pay-title i { color: var(--sf-accent); }

    .payment-box .pay-sub {
        display: block;
        color: var(--sf-muted);
        font-size: 13px;
        margin-top: 4px;
    }

    .payment-box .pay-test {
        display: block;
        margin-top: 8px;
        font-size: 12.5px;
        color: rgba(255, 255, 255, .8);
        background: rgba(241, 184, 22, .12);
        border: 1px solid rgba(241, 184, 22, .3);
        border-radius: 10px;
        padding: 7px 11px;
    }

    .payment-box .pay-test code {
        color: var(--sf-accent);
        background: none;
        font-size: 12.5px;
    }

    /* ================= REVIEW PANEL ================= */
    .review-card { padding: 22px 20px; }

    .review-line {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
    }

    .review-line:last-of-type { border-bottom: none; }

    .review-line .name {
        color: var(--sf-ink);
        font-weight: 600;
        font-size: 14.5px;
        line-height: 1.35;
    }

    .review-line .meta {
        display: block;
        color: var(--sf-muted);
        font-size: 12.5px;
        margin-top: 2px;
    }

    .review-line .amount {
        color: var(--sf-ink);
        font-weight: 700;
        white-space: nowrap;
    }

    .review-card .btn-success {
        width: 100%;
        margin-top: 18px;
        padding: 13px 20px;
        font-size: 15.5px;
        font-weight: 800;
    }

    .review-note {
        display: block;
        margin-top: 12px;
        text-align: center;
        color: var(--sf-faint);
        font-size: 12.5px;
    }

    .back-to-cart {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--sf-muted);
        font-size: 14px;
        font-weight: 600;
    }

    .back-to-cart:hover { color: var(--sf-accent); text-decoration: none; }

    @media (min-width: 992px) {
        .review-col { position: sticky; top: calc(var(--sf-header-h) + 20px); }
    }
</style>
@endpush

@section('content')

<section class="checkout-section layout_padding">
    <div class="container">

        <div class="sf-page-head text-center">
            <h2>Checkout</h2>
            <p>Confirm where it goes and how you would like to pay.</p>
        </div>

        <div class="row">

            {{-- ================= LEFT: DELIVERY + PAYMENT ================= --}}
            <div class="col-lg-7">

                <form action="{{ route('order.store') }}" method="POST" id="checkoutForm">
                    @csrf

                    {{-- ---------- DELIVERY INFORMATION ---------- --}}
                    <div class="glass-card checkout-card">
                        <div class="checkout-head">
                            <span class="checkout-step">1</span>
                            Delivery details
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Full name</label>
                                <input type="text" class="form-control"
                                       value="{{ $user->full_name }}" readonly>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" class="form-control"
                                       value="{{ $user->email }}" readonly>
                            </div>
                        </div>

                        <small class="sf-hint mb-3 d-block">
                            Name and email come from your profile — edit them there if they changed.
                        </small>

                        <div class="form-group">
                            <label for="phone">Phone <span class="sf-req">*</span></label>
                            <input type="text" name="phone" id="phone"
                                   class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="01XXXXXXXXX" required>

                            @if ($errors->has('phone'))
                                <div class="invalid-feedback d-block">{{ $errors->first('phone') }}</div>
                            @else
                                <small class="sf-hint">The rider calls this number when they arrive.</small>
                            @endif
                        </div>

                        <div class="form-group mb-0">
                            <label for="address">Delivery address <span class="sf-req">*</span></label>
                            <textarea name="address" id="address" rows="3"
                                      class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                                      placeholder="House, road, area and any landmark"
                                      required>{{ old('address', $user->address ?? '') }}</textarea>

                            @if ($errors->has('address'))
                                <div class="invalid-feedback d-block">{{ $errors->first('address') }}</div>
                            @else
                                <small class="sf-hint">A landmark helps the rider find you faster.</small>
                            @endif
                        </div>
                    </div>

                    {{-- ---------- PAYMENT METHOD ---------- --}}
                    <div class="glass-card checkout-card">
                        <div class="checkout-head">
                            <span class="checkout-step">2</span>
                            Payment method
                        </div>

                        @php $chosen = old('payment_method', 'cod'); @endphp

                        <label class="payment-box {{ $chosen === 'cod' ? 'is-selected' : '' }}">
                            <input type="radio" name="payment_method" value="cod"
                                   {{ $chosen === 'cod' ? 'checked' : '' }}>

                            <span class="pay-title">
                                <i class="fa fa-money" aria-hidden="true"></i> Cash on delivery
                            </span>
                            <small class="pay-sub">Pay the rider when your food arrives.</small>
                        </label>

                        <label class="payment-box {{ $chosen === 'stripe' ? 'is-selected' : '' }} mb-0">
                            <input type="radio" name="payment_method" value="stripe"
                                   {{ $chosen === 'stripe' ? 'checked' : '' }}>

                            <span class="pay-title">
                                <i class="fa fa-credit-card" aria-hidden="true"></i> Pay by card
                            </span>
                            <small class="pay-sub">
                                You are taken to Stripe's secure payment page, then straight back here.
                            </small>
                            <small class="pay-test">
                                Test mode — card <code>4242 4242 4242 4242</code>,
                                any future expiry, any CVC.
                            </small>
                        </label>

                        @if ($errors->has('payment_method'))
                            <div class="invalid-feedback d-block">{{ $errors->first('payment_method') }}</div>
                        @endif
                    </div>
                </form>

                <a href="{{ route('cart.index') }}" class="back-to-cart mb-4">
                    <i class="fa fa-angle-left" aria-hidden="true"></i> Back to cart
                </a>

            </div>

            {{-- ================= RIGHT: ORDER REVIEW ================= --}}
            <div class="col-lg-5">
                <div class="review-col">
                    <div class="glass-card review-card">

                        <div class="checkout-head">
                            <i class="fa fa-shopping-basket" aria-hidden="true"></i>
                            Your order
                        </div>

                        @foreach ($cart as $item)
                            @php $itemTotal = $item['price'] * $item['quantity']; @endphp

                            <div class="review-line">
                                <div>
                                    <span class="name">{{ $item['name'] }}</span>
                                    <small class="meta">
                                        ৳{{ number_format($item['price'], 2) }} × {{ $item['quantity'] }}
                                    </small>
                                </div>
                                <div class="amount">৳{{ number_format($itemTotal, 2) }}</div>
                            </div>
                        @endforeach

                        {{-- The coupon box carries the subtotal / discount / payable rows.
                             It sits outside the checkout form on purpose — it posts to its
                             own route, and forms cannot be nested. --}}
                        <div class="mt-3">
                            @include('frontend.partials.coupon-box', ['totals' => $totals])
                        </div>

                        <button type="submit" form="checkoutForm" id="placeOrderBtn"
                                class="btn btn-success">
                            Place order · ৳{{ number_format($totals->total(), 2) }}
                        </button>

                        <small class="review-note">
                            Stock is checked once more before the order is confirmed.
                        </small>

                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
    (function () {
        var form = document.getElementById('checkoutForm');
        var btn  = document.getElementById('placeOrderBtn');

        if (!form) {
            return;
        }

        // Highlight the chosen payment card.
        var boxes = form.querySelectorAll('.payment-box');

        form.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                boxes.forEach(function (box) {
                    box.classList.toggle('is-selected', box.contains(radio) && radio.checked);
                });
            });
        });

        if (!btn) {
            return;
        }

        // One order per click.
        var lock = function () {
            setTimeout(function () {
                btn.disabled = true;
                btn.textContent = 'Placing your order…';
            }, 0);
        };

        form.addEventListener('submit', lock);

        // The button lives in the summary card, outside <form>. Modern browsers
        // wire that up through the form="" attribute; this is the fallback.
        if (!btn.form) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                if (form.reportValidity && !form.reportValidity()) {
                    return;
                }

                lock();
                form.submit();
            });
        }
    })();
</script>
@endpush
