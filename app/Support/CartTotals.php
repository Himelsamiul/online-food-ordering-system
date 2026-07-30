<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\DeliveryZone;

/**
 * Single source of truth for what a cart costs.
 *
 * The cart page, the checkout page, the order it becomes and the Stripe
 * line items all read from here, so a discount can never be shown in one
 * place and charged differently in another. Delivery works the same way —
 * the chosen zone lives in the session like the coupon and is re-validated on
 * every build, because a zone can be switched off or repriced between the
 * moment it was picked and the moment the order is placed.
 */
class CartTotals
{
    public const SESSION_KEY = 'coupon_code';
    public const ZONE_KEY    = 'delivery_zone_id';

    public function __construct(
        public readonly float $subtotal,
        public readonly ?Coupon $coupon = null,
        public readonly float $discount = 0.0,
        /** Set when a previously applied coupon stopped qualifying. */
        public readonly ?string $droppedReason = null,
        public readonly ?DeliveryZone $zone = null,
        public readonly float $deliveryCharge = 0.0,
        /** Set when a previously chosen delivery zone stopped qualifying. */
        public readonly ?string $zoneDroppedReason = null,
    ) {}

    public function total(): float
    {
        // Delivery is added AFTER the discount: a coupon discounts the food,
        // not the rider's trip.
        $goods = max(0, $this->subtotal - $this->discount);

        return round($goods + $this->deliveryCharge, 2);
    }

    public function hasCoupon(): bool
    {
        return $this->coupon !== null && $this->discount > 0;
    }

    public function hasZone(): bool
    {
        return $this->zone !== null;
    }

    /** A zone IS selected but costs nothing — worth saying so explicitly. */
    public function hasFreeDelivery(): bool
    {
        return $this->zone !== null && $this->deliveryCharge <= 0;
    }

    /** Blocks checkout: the chosen zone has a minimum this cart does not meet. */
    public function belowZoneMinimum(): bool
    {
        return $this->zone !== null && !$this->zone->meetsMinimum($this->subtotal);
    }

    public function amountToFreeDelivery(): ?float
    {
        return $this->zone?->amountToFreeDelivery($this->subtotal);
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
     * Build totals from the session cart, and whatever coupon and delivery
     * zone are held there.
     *
     * Both are re-validated every single time. Removing an item can drop the
     * cart below the coupon's minimum, and a zone can be deactivated by an
     * admin mid-session, so neither is assumed to still be valid at checkout.
     */
    public static function fromSession(?int $registrationId = null): self
    {
        $cart     = session()->get('cart', []);
        $subtotal = self::subtotalOf($cart);

        [$zone, $charge, $zoneDropped] = self::resolveZone($subtotal);

        $code = session()->get(self::SESSION_KEY);

        if (!$code) {
            return new self($subtotal, null, 0.0, null, $zone, $charge, $zoneDropped);
        }

        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            session()->forget(self::SESSION_KEY);

            return new self(
                $subtotal, null, 0.0, 'That coupon no longer exists.',
                $zone, $charge, $zoneDropped
            );
        }

        $check = $coupon->validateFor($subtotal, $registrationId);

        if (!$check['ok']) {
            session()->forget(self::SESSION_KEY);

            return new self(
                $subtotal, null, 0.0, $check['message'],
                $zone, $charge, $zoneDropped
            );
        }

        return new self(
            $subtotal, $coupon, $check['discount'], null,
            $zone, $charge, $zoneDropped
        );
    }

    /**
     * @return array{0: ?DeliveryZone, 1: float, 2: ?string}
     */
    private static function resolveZone(float $subtotal): array
    {
        $zoneId = session()->get(self::ZONE_KEY);

        if (!$zoneId) {
            return [null, 0.0, null];
        }

        $zone = DeliveryZone::find($zoneId);

        if (!$zone) {
            session()->forget(self::ZONE_KEY);

            return [null, 0.0, 'That delivery area is no longer available.'];
        }

        if (!$zone->is_active) {
            session()->forget(self::ZONE_KEY);

            return [null, 0.0, "We are not delivering to {$zone->name} right now."];
        }

        return [$zone, $zone->chargeFor($subtotal), null];
    }
}
