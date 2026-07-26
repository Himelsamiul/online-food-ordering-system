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
