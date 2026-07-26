<?php

namespace App\Support;

use App\Models\Coupon;

/**
 * Single source of truth for what a cart costs.
 *
 * The cart page, the checkout page, the order it becomes and the Stripe
 * line items all read from here, so a discount can never be shown in one
 * place and charged differently in another.
 */
class CartTotals
{
    public const SESSION_KEY = 'coupon_code';

    public function __construct(
        public readonly float $subtotal,
        public readonly ?Coupon $coupon = null,
        public readonly float $discount = 0.0,
        /** Set when a previously applied coupon stopped qualifying. */
        public readonly ?string $droppedReason = null,
    ) {}

    public function total(): float
    {
        return round(max(0, $this->subtotal - $this->discount), 2);
    }

    public function hasCoupon(): bool
    {
        return $this->coupon !== null && $this->discount > 0;
    }

    public static function subtotalOf(array $cart): float
    {
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        return round($subtotal, 2);
    }

    /**
     * Build totals from the session cart and whatever coupon is held there.
     *
     * The coupon is re-validated every single time. Removing an item can
     * drop the cart below the coupon's minimum, so a code that was valid
     * when applied is not assumed to still be valid at checkout.
     */
    public static function fromSession(?int $registrationId = null): self
    {
        $cart     = session()->get('cart', []);
        $subtotal = self::subtotalOf($cart);
        $code     = session()->get(self::SESSION_KEY);

        if (!$code) {
            return new self($subtotal);
        }

        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            session()->forget(self::SESSION_KEY);

            return new self($subtotal, null, 0.0, 'That coupon no longer exists.');
        }

        $check = $coupon->validateFor($subtotal, $registrationId);

        if (!$check['ok']) {
            session()->forget(self::SESSION_KEY);

            return new self($subtotal, null, 0.0, $check['message']);
        }

        return new self($subtotal, $coupon, $check['discount']);
    }
}
