<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\CustomerNotifier;

/**
 * Turns order lifecycle changes into storefront notifications.
 *
 * An observer rather than a call at each controller is deliberate: orders are
 * written from the checkout, the admin status form, the Stripe return handler
 * and the delivery-run screens, and a notification bolted onto each of those
 * would be one refactor away from silently going missing.
 *
 * The catch is that Eloquent events do NOT fire for mass updates
 * (Order::whereIn(...)->update(...)). DeliveryRunController was rewritten to
 * save models individually for exactly this reason — if a new mass update
 * appears anywhere, its status change will not reach the customer.
 */
class OrderObserver
{
    public function __construct(private readonly CustomerNotifier $notifier) {}

    public function created(Order $order): void
    {
        $this->notifier->orderPlaced($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('order_status')) {
            $this->notifier->orderStatusChanged(
                $order,
                $order->getOriginal('order_status'),
                $order->order_status,
            );
        }

        // Only the transition INTO paid is news; a paid order re-saved for an
        // unrelated reason must not announce itself again.
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            $this->notifier->paymentReceived($order);
        }
    }
}
