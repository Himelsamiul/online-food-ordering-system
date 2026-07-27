<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Writes the storefront bell.
 *
 * Every method is best-effort: a notification is a side effect of something
 * the customer already did successfully, so a failure here must never roll
 * back or 500 the thing being announced.
 */
class CustomerNotifier
{
    /**
     * Order statuses worth telling the customer about, with their copy.
     *
     * Deliberately not every transition. An order bounced back to "pending"
     * because an admin deleted a delivery run is bookkeeping, not news, and
     * announcing it would only confuse.
     */
    private const ORDER_COPY = [
        'confirmed' => [
            'title' => 'Order confirmed',
            'body'  => 'We have accepted your order and will start preparing it shortly.',
            'icon'  => 'fa-check-circle',
            'tone'  => 'success',
        ],
        'cooking' => [
            'title' => 'Your food is being prepared',
            'body'  => 'The kitchen has started cooking your order.',
            'icon'  => 'fa-cutlery',
            'tone'  => 'info',
        ],
        'out_for_delivery' => [
            'title' => 'Your food is on the way',
            'body'  => 'Your order has been picked up and is heading to you now.',
            'icon'  => 'fa-motorcycle',
            'tone'  => 'info',
        ],
        'delivered' => [
            'title' => 'Order delivered',
            'body'  => 'Your order has been delivered. Enjoy your meal!',
            'icon'  => 'fa-smile-o',
            'tone'  => 'success',
        ],
        'cancelled' => [
            'title' => 'Order cancelled',
            'body'  => 'This order has been cancelled. Contact support if this was not you.',
            'icon'  => 'fa-times-circle',
            'tone'  => 'danger',
        ],
    ];

    /* ----------------------------------------------------------------- core */

    public function notify(
        ?Registration $customer,
        string $type,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?string $icon = null,
        ?string $tone = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
    ): ?CustomerNotification {
        if (!$customer) {
            return null;
        }

        try {
            return CustomerNotification::create([
                'registration_id' => $customer->id,
                'type'            => $type,
                'title'           => $title,
                'body'            => $body ? Str::limit($body, 390) : null,
                'url'             => $url,
                'icon'            => $icon,
                'tone'            => $tone,
                'subject_type'    => $subjectType,
                'subject_id'      => $subjectId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Customer notification failed: ' . $e->getMessage());

            return null;
        }
    }

    /* ---------------------------------------------------------------- orders */

    public function orderPlaced(Order $order): void
    {
        $this->notify(
            $order->user,
            CustomerNotification::TYPE_ORDER,
            'Order placed',
            "We have received order {$this->ref($order)}. You will get an update as soon as it is confirmed.",
            $this->orderUrl($order),
            'fa-shopping-bag',
            'info',
            Order::class,
            $order->id,
        );
    }

    /**
     * An order moved. Returns silently for transitions the customer does not
     * need to hear about.
     */
    public function orderStatusChanged(Order $order, ?string $from, string $to): void
    {
        $copy = self::ORDER_COPY[$to] ?? null;

        if (!$copy || $from === $to) {
            return;
        }

        $body = $copy['body'];

        // Name the rider when we can — "Rahim picked up your order" lands far
        // better than an anonymous status change.
        if ($to === 'out_for_delivery') {
            $rider = $order->deliveryRun?->deliveryMan?->name;

            if ($rider) {
                $body = "{$rider} has picked up your order and is on the way to you.";
            }
        }

        $this->notify(
            $order->user,
            CustomerNotification::TYPE_ORDER,
            $copy['title'] . ' · ' . $this->ref($order),
            $body,
            $this->orderUrl($order),
            $copy['icon'],
            $copy['tone'],
            Order::class,
            $order->id,
        );
    }

    public function paymentReceived(Order $order): void
    {
        $this->notify(
            $order->user,
            CustomerNotification::TYPE_ORDER,
            'Payment received · ' . $this->ref($order),
            'Your payment has cleared. Thank you!',
            $this->orderUrl($order),
            'fa-credit-card',
            'success',
            Order::class,
            $order->id,
        );
    }

    /* ------------------------------------------------------------------ chat */

    /**
     * Support answered.
     *
     * Collapses onto the existing unread chat notification instead of adding a
     * row per reply — three quick messages from an agent should read as one
     * "Support replied", not spam the bell three times.
     */
    public function chatReply(ChatConversation $conversation, string $agentName, string $preview): void
    {
        $customer = $conversation->customer;

        if (!$customer) {
            return;
        }

        try {
            $existing = CustomerNotification::query()
                ->for($customer->id)
                ->unread()
                ->where('type', CustomerNotification::TYPE_CHAT)
                ->where('subject_id', $conversation->id)
                ->latest('id')
                ->first();

            if ($existing) {
                $existing->forceFill([
                    'title'      => "{$agentName} replied",
                    'body'       => Str::limit($preview, 390),
                    'created_at' => now(),          // float it back to the top
                ])->save();

                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Chat notification collapse failed: ' . $e->getMessage());
        }

        $this->notify(
            $customer,
            CustomerNotification::TYPE_CHAT,
            "{$agentName} replied",
            $preview,
            route('home') . '#chat',
            'fa-comments',
            'info',
            ChatConversation::class,
            $conversation->id,
        );
    }

    /* --------------------------------------------------------------- account */

    public function account(Registration $customer, string $title, string $body, string $tone = 'info'): void
    {
        $this->notify(
            $customer,
            CustomerNotification::TYPE_ACCOUNT,
            $title,
            $body,
            route('profile'),
            'fa-user-circle',
            $tone,
        );
    }

    /* -------------------------------------------------------------- internal */

    private function ref(Order $order): string
    {
        return '#' . ($order->order_number ?: $order->id);
    }

    private function orderUrl(Order $order): string
    {
        return route('profile.order.view', $order->id);
    }
}
