<?php

namespace App\Services;

use App\Models\Order;
use RuntimeException;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\StripeClient;

/**
 * Thin wrapper around Stripe Hosted Checkout.
 *
 * The card form lives on Stripe's own page, so no card data ever
 * touches this application.
 */
class StripeGateway
{
    private StripeClient $stripe;

    public function __construct()
    {
        $secret = config('services.stripe.secret');

        if (blank($secret)) {
            throw new RuntimeException('STRIPE_SECRET is missing from your .env file.');
        }

        $this->stripe = new StripeClient($secret);
    }

    /**
     * Build a Checkout Session for an order that is still unpaid.
     */
    public function createCheckoutSession(
        Order $order,
        array $cart,
        ?string $customerEmail = null,
        float $discountAmount = 0.0
    ): CheckoutSession {
        $lineItems = [];

        foreach ($cart as $item) {
            $lineItems[] = [
                'quantity'   => $item['quantity'],
                'price_data' => [
                    'currency'     => config('services.stripe.currency'),
                    'unit_amount'  => $this->toMinorUnit($item['price']),
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                ],
            ];
        }

        /*
         * Delivery as its own line so the customer sees what they are paying
         * for, and so the session total matches the order total exactly.
         *
         * The Stripe coupon below is an amount_off across the whole session,
         * giving (food + delivery) - discount, while CartTotals computes
         * (food - discount) + delivery. Those are the same number, so the two
         * cannot drift — the one exception being a discount larger than the
         * food subtotal, which Coupon::validateFor already caps.
         */
        if ($order->delivery_charge > 0) {
            $lineItems[] = [
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => config('services.stripe.currency'),
                    'unit_amount'  => $this->toMinorUnit((float) $order->delivery_charge),
                    'product_data' => [
                        'name' => 'Delivery' . ($order->delivery_zone_name ? ' — ' . $order->delivery_zone_name : ''),
                    ],
                ],
            ];
        }

        $payload = [
            'mode'                => 'payment',
            'line_items'          => $lineItems,
            'customer_email'      => $customerEmail,
            'client_reference_id' => (string) $order->id,

            // Used on the way back to prove the session belongs to this order
            'metadata'            => ['order_id' => (string) $order->id],

            'success_url'         => route('order.payment.return', $order)
                                        . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'          => route('order.payment.cancel', $order),
        ];

        // Stripe has no "negative line item", so the coupon becomes a
        // one-off Stripe coupon. This keeps the discount visible on
        // Stripe's own page instead of silently shrinking the prices.
        if ($discountAmount > 0) {
            $stripeCoupon = $this->stripe->coupons->create([
                'amount_off' => $this->toMinorUnit($discountAmount),
                'currency'   => config('services.stripe.currency'),
                'duration'   => 'once',
                'name'       => $order->coupon_code
                    ? 'Coupon ' . $order->coupon_code
                    : 'Discount',
            ]);

            $payload['discounts'] = [['coupon' => $stripeCoupon->id]];
        }

        return $this->stripe->checkout->sessions->create($payload);
    }

    public function retrieveSession(string $sessionId): CheckoutSession
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId);
    }

    /**
     * Stripe wants the amount in the smallest unit (poisha for BDT, cents for USD).
     */
    private function toMinorUnit(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
