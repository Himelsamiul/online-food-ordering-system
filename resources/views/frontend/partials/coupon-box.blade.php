{{--
    Coupon apply / remove + the price breakdown.
    Expects $totals (App\Support\CartTotals).
--}}
<style>
    .coupon-box {
        background: rgba(0,0,0,.92);
        border: 1px solid rgba(241,184,22,.35);
        border-radius: 18px;
        padding: 22px 24px;
        color: #fff;
    }

    .coupon-box .coupon-title {
        font-weight: 700;
        color: #f1b816;
        margin-bottom: 14px;
    }

    .coupon-input {
        background: rgba(255,255,255,.1);
        border: 1px solid rgba(255,255,255,.25);
        border-radius: 30px;
        color: #fff;
        padding: 11px 20px;
        outline: none;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        flex: 1;
    }

    .coupon-input:focus { border-color: #f1b816; }
    .coupon-input::placeholder { color: rgba(255,255,255,.4); letter-spacing: 0; font-weight: 400; }

    .coupon-apply-btn {
        background: #f1b816;
        color: #1c1c1c;
        border: none;
        border-radius: 30px;
        padding: 11px 28px;
        font-weight: 800;
        white-space: nowrap;
    }

    .coupon-apply-btn:hover { background: #d9a412; }

    .coupon-applied {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        background: rgba(25,135,84,.18);
        border: 1px dashed rgba(45,206,137,.6);
        border-radius: 12px;
        padding: 12px 18px;
    }

    .coupon-applied .code {
        font-family: monospace;
        font-weight: 800;
        letter-spacing: 1px;
        color: #2ecc71;
        font-size: 17px;
    }

    .coupon-remove-btn {
        background: transparent;
        border: 1px solid rgba(255,255,255,.3);
        color: rgba(255,255,255,.75);
        border-radius: 20px;
        padding: 5px 16px;
        font-size: 13px;
    }

    .coupon-remove-btn:hover { border-color: #e74c3c; color: #e74c3c; }

    .totals-row {
        display: flex;
        justify-content: space-between;
        padding: 7px 0;
        color: rgba(255,255,255,.85);
    }

    .totals-row.discount { color: #2ecc71; font-weight: 700; }

    .totals-row.grand {
        border-top: 1px dashed rgba(255,255,255,.25);
        margin-top: 8px;
        padding-top: 14px;
        font-size: 21px;
        font-weight: 800;
        color: #2ecc71;
    }
</style>

<div class="coupon-box">

    <div class="coupon-title">🎟️ Have a coupon?</div>

    @if ($totals->droppedReason)
        <div class="alert alert-warning py-2 px-3 small">
            Your coupon was removed — {{ $totals->droppedReason }}
        </div>
    @endif

    @if ($totals->hasCoupon())

        <div class="coupon-applied mb-3">
            <div>
                <span class="code">{{ $totals->coupon->code }}</span>
                <small class="d-block text-white-50">
                    {{ $totals->coupon->offer_label }}
                    @if ($totals->coupon->description) · {{ $totals->coupon->description }} @endif
                </small>
            </div>

            <form action="{{ route('coupon.remove') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="coupon-remove-btn">Remove</button>
            </form>
        </div>

    @else

        <form action="{{ route('coupon.apply') }}" method="POST" class="d-flex gap-2 mb-3">
            @csrf
            <input type="text" name="code" class="coupon-input"
                   placeholder="Enter coupon code" required>
            <button type="submit" class="coupon-apply-btn">Apply</button>
        </form>

    @endif

    {{-- ================= BREAKDOWN ================= --}}
    <div class="totals-row">
        <span>Subtotal</span>
        <span>৳{{ number_format($totals->subtotal, 2) }}</span>
    </div>

    @if ($totals->hasCoupon())
        <div class="totals-row discount">
            <span>Discount ({{ $totals->coupon->code }})</span>
            <span>− ৳{{ number_format($totals->discount, 2) }}</span>
        </div>
    @endif

    <div class="totals-row grand">
        <span>Total</span>
        <span>৳{{ number_format($totals->total(), 2) }}</span>
    </div>

</div>
