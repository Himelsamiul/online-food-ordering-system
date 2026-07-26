<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value',
        'min_order_amount', 'max_discount',
        'starts_at', 'expires_at',
        'usage_limit', 'per_user_limit', 'used_count',
        'status',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'expires_at'       => 'datetime',
        'status'           => 'boolean',
        'value'            => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount'     => 'decimal:2',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    /**
     * Codes are compared case-insensitively, so normalise on the way in.
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    /**
     * Coupons a customer could plausibly use right now. Deliberately does
     * NOT check per-user limits or cart total — it drives the "available
     * offers" listings, where those depend on who is looking and what is
     * in their cart.
     */
    public function scopeCurrentlyRunning($query)
    {
        $now = now();

        return $query->where('status', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'));
    }

    /**
     * Full check for one customer and one cart subtotal.
     *
     * @return array{ok: bool, message: string, discount: float}
     */
    public function validateFor(float $subtotal, ?int $registrationId): array
    {
        $fail = fn (string $message) => ['ok' => false, 'message' => $message, 'discount' => 0.0];

        if (!$this->status) {
            return $fail('This coupon is no longer active.');
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return $fail('This coupon starts on ' . $this->starts_at->format('d M Y') . '.');
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return $fail('This coupon expired on ' . $this->expires_at->format('d M Y') . '.');
        }

        if ($this->min_order_amount && $subtotal < (float) $this->min_order_amount) {
            return $fail('Minimum order of ৳' . number_format((float) $this->min_order_amount, 2) . ' required for this coupon.');
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return $fail('This coupon has reached its usage limit.');
        }

        if ($this->per_user_limit !== null && $registrationId) {
            $mine = $this->usages()->where('registration_id', $registrationId)->count();

            if ($mine >= $this->per_user_limit) {
                return $fail('You have already used this coupon the maximum number of times.');
            }
        }

        $discount = $this->discountOn($subtotal);

        if ($discount <= 0) {
            return $fail('This coupon gives no discount on your current cart.');
        }

        return ['ok' => true, 'message' => 'Coupon applied.', 'discount' => $discount];
    }

    /**
     * Discount in taka for a given subtotal, capped so it can never exceed
     * the cart itself (a ৳100 fixed coupon on a ৳60 cart gives ৳60, not ৳100).
     */
    public function discountOn(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->type === 'percent' && $this->max_discount) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    /**
     * "20% off" / "৳100 off" — used on banners and the offers list.
     */
    public function getOfferLabelAttribute(): string
    {
        // Drop trailing zeros but never the value itself: ৳100.50 must not
        // round to ৳101, and 20.00% should read as 20%.
        $value = rtrim(rtrim(number_format((float) $this->value, 2, '.', ''), '0'), '.');

        return $this->type === 'percent'
            ? $value . '% off'
            : '৳' . $value . ' off';
    }
}
