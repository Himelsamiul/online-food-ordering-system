{{--
    Coupon apply / remove + the price breakdown.
    Expects $totals (App\Support\CartTotals).

    Every number here comes straight off $totals — the cart page, this partial
    and the order that gets created must never disagree, so nothing is
    recomputed locally.

    The block carries no panel chrome of its own: both callers (the cart
    summary and the checkout review panel) already wrap it in a card.
--}}

@once
    @push('styles')
        <style>
            .coupon-box { color: var(--sf-ink); }

            .coupon-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
                font-weight: 700;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: var(--sf-muted);
                margin-bottom: 10px;
            }

            .coupon-title i { color: var(--sf-accent); }

            /* ---------- code entry ---------- */
            .coupon-form {
                display: flex;
                gap: 8px;
                margin-bottom: 6px;
            }

            .coupon-input {
                flex: 1 1 auto;
                min-width: 0;
                background: rgba(255, 255, 255, .1);
                border: 1px solid var(--sf-glass-line);
                border-radius: 30px;
                color: var(--sf-ink);
                padding: 10px 18px;
                outline: none;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-weight: 700;
                font-size: 14px;
                transition: border-color .18s, box-shadow .18s, background-color .18s;
            }

            .coupon-input:focus {
                border-color: var(--sf-accent);
                background: rgba(255, 255, 255, .14);
                box-shadow: 0 0 0 3px rgba(241, 184, 22, .18);
            }

            .coupon-input::placeholder {
                color: var(--sf-faint);
                letter-spacing: 0;
                font-weight: 400;
                text-transform: none;
            }

            .coupon-apply-btn {
                flex: 0 0 auto;
                background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
                color: rgba(0, 0, 0, .88);
                border: none;
                border-radius: 30px;
                padding: 10px 24px;
                font-weight: 800;
                font-size: 14px;
                white-space: nowrap;
                transition: transform .18s, box-shadow .18s, opacity .18s;
            }

            .coupon-apply-btn:hover:not(:disabled) {
                transform: translateY(-1px);
                box-shadow: 0 10px 22px rgba(241, 184, 22, .34);
            }

            .coupon-apply-btn:disabled { opacity: .6; transform: none; box-shadow: none; }

            /* ---------- applied chip ---------- */
            .coupon-applied {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                background: rgba(29, 191, 115, .14);
                border: 1px solid rgba(29, 191, 115, .45);
                border-radius: 30px;
                padding: 8px 8px 8px 16px;
            }

            .coupon-applied .code {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                font-family: monospace;
                font-weight: 800;
                letter-spacing: 1px;
                color: var(--sf-green);
                font-size: 16px;
                line-height: 1.2;
            }

            .coupon-applied .coupon-offer {
                display: block;
                color: var(--sf-muted);
                font-size: 12px;
                margin-top: 1px;
            }

            .coupon-remove-btn {
                flex: 0 0 auto;
                width: 30px;
                height: 30px;
                line-height: 1;
                background: rgba(255, 255, 255, .08);
                border: 1px solid var(--sf-glass-line);
                color: var(--sf-muted);
                border-radius: 50%;
                font-size: 15px;
                padding: 0;
                transition: background-color .16s, color .16s, border-color .16s;
            }

            .coupon-remove-btn:hover {
                border-color: var(--sf-danger);
                color: var(--sf-danger);
                background: rgba(231, 76, 60, .16);
            }

            /* ---------- feedback ---------- */
            .coupon-note {
                border-radius: var(--sf-radius-sm);
                padding: 9px 13px;
                font-size: 13px;
                line-height: 1.45;
                margin-bottom: 10px;
            }

            .coupon-note.is-warning {
                background: rgba(241, 184, 22, .12);
                border: 1px solid rgba(241, 184, 22, .34);
                color: rgba(255, 255, 255, .86);
            }

            .coupon-note.is-error {
                background: rgba(231, 76, 60, .14);
                border: 1px solid rgba(231, 76, 60, .42);
                color: var(--sf-danger);
            }

            .coupon-hint {
                display: block;
                color: var(--sf-faint);
                font-size: 12.5px;
                margin-bottom: 4px;
            }

            /* ---------- breakdown ---------- */
            .totals-wrap {
                margin-top: 18px;
                padding-top: 16px;
                border-top: 1px solid var(--sf-glass-line);
            }

            .totals-row {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                gap: 12px;
                padding: 6px 0;
                color: rgba(255, 255, 255, .82);
                font-size: 14.5px;
            }

            .totals-row span:last-child { font-weight: 600; white-space: nowrap; }

            .totals-row.discount { color: var(--sf-green); }
            .totals-row.discount span:last-child { font-weight: 700; }
            .totals-row .totals-free { color: var(--sf-green); font-weight: 700; }

            .totals-row.grand {
                border-top: 1px dashed rgba(255, 255, 255, .22);
                margin-top: 10px;
                padding-top: 14px;
                color: var(--sf-ink);
                font-size: 16px;
                font-weight: 700;
            }

            .totals-row.grand span:last-child {
                font-size: 26px;
                font-weight: 800;
                letter-spacing: -.02em;
                color: var(--sf-green);
            }

            .totals-saved {
                margin-top: 10px;
                text-align: right;
                font-size: 12.5px;
                color: var(--sf-green);
                font-weight: 600;
            }

            .totals-zone {
                display: block;
                font-size: 11.5px;
                color: var(--sf-faint);
                font-weight: 400;
            }

            .totals-pending {
                color: var(--sf-faint) !important;
                font-weight: 400 !important;
                font-size: 13px;
            }

            .totals-nudge {
                margin-top: 2px;
                font-size: 12px;
                color: var(--sf-accent);
                text-align: right;
            }

            .totals-nudge[hidden],
            [data-totals-minimum][hidden] { display: none; }
        </style>
    @endpush
@endonce

<div class="coupon-box">

    <div class="coupon-title">
        <i class="fa fa-ticket" aria-hidden="true"></i>
        <span>Coupon</span>
    </div>

    @if ($totals->droppedReason)
        <div class="coupon-note is-warning">
            Your coupon was removed — {{ $totals->droppedReason }}
        </div>
    @endif

    @if ($totals->hasCoupon())

        <div class="coupon-applied">
            <div>
                <span class="code">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>{{ $totals->coupon->code }}
                </span>
                <small class="coupon-offer">
                    {{ $totals->coupon->offer_label }}
                    @if ($totals->coupon->description) · {{ $totals->coupon->description }} @endif
                </small>
            </div>

            <form action="{{ route('coupon.remove') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="coupon-remove-btn"
                        title="Remove coupon {{ $totals->coupon->code }}"
                        aria-label="Remove coupon {{ $totals->coupon->code }}">&times;</button>
            </form>
        </div>

    @else

        <form action="{{ route('coupon.apply') }}" method="POST" class="coupon-form">
            @csrf
            <input type="text" name="code" class="coupon-input"
                   value="{{ old('code') }}"
                   placeholder="Enter coupon code"
                   autocomplete="off" autocapitalize="characters" spellcheck="false"
                   aria-label="Coupon code" required>
            <button type="submit" class="coupon-apply-btn">Apply</button>
        </form>

        @if ($errors->has('code'))
            <div class="coupon-note is-error mt-2 mb-0">{{ $errors->first('code') }}</div>
        @else
            <small class="coupon-hint">One code per order. It is checked again at checkout.</small>
        @endif

    @endif

    {{-- ================= BREAKDOWN ================= --}}
    <div class="totals-wrap">

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

        {{-- Delivery. The row reads differently depending on whether an area
             has been chosen yet, because "Free" and "not picked yet" are very
             different things to show above a Total. --}}
        <div class="totals-row" data-totals-delivery-row>
            <span>
                Delivery
                @if ($totals->hasZone())
                    <small class="totals-zone" data-totals-zone>({{ $totals->zone->name }})</small>
                @endif
            </span>

            @if (!$totals->hasZone())
                <span class="totals-pending" data-totals-delivery>Choose an area</span>
            @elseif ($totals->hasFreeDelivery())
                <span class="totals-free" data-totals-delivery>Free</span>
            @else
                <span data-totals-delivery>৳{{ number_format($totals->deliveryCharge, 2) }}</span>
            @endif
        </div>

        {{-- Always in the DOM, hidden when not applicable: the checkout script
             toggles these as the area changes, and it cannot show an element
             that was never rendered. --}}
        <div class="totals-nudge" data-totals-nudge
             @unless ($totals->amountToFreeDelivery()) hidden @endunless>
            @if ($totals->amountToFreeDelivery())
                Add ৳{{ number_format($totals->amountToFreeDelivery(), 2) }} more for free delivery
            @endif
        </div>

        <div class="coupon-note is-error mt-2 mb-0" data-totals-minimum
             @unless ($totals->belowZoneMinimum()) hidden @endunless>
            @if ($totals->belowZoneMinimum())
                Orders to {{ $totals->zone->name }} start at
                ৳{{ number_format((float) $totals->zone->min_order, 2) }}.
            @endif
        </div>

        <div class="totals-row grand">
            <span>Total</span>
            <span data-totals-grand>৳{{ number_format($totals->total(), 2) }}</span>
        </div>

        @if ($totals->hasCoupon())
            <div class="totals-saved">
                You saved ৳{{ number_format($totals->discount, 2) }}
            </div>
        @endif

    </div>

</div>
