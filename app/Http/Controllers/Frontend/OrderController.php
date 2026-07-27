<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Food;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Services\StripeGateway;
use App\Support\CartTotals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\DeliveryMan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
class OrderController extends Controller
{
    /**
     * Show Place Order (Checkout) Page
     */
public function create()
{
    $cart = session()->get('cart', []);

    if (count($cart) === 0) {
        return redirect()->route('home')
            ->with('info', 'Your cart is empty');
    }

    // ✅ LOGIN USER (Laravel way)
    $user = Auth::guard('frontend')->user();

    $totals = CartTotals::fromSession($user?->id);

    return view('frontend.pages.order.place', compact('cart', 'user', 'totals'));
}

    /**
     * Store Order
     *
     * COD  -> order is confirmed straight away (no gateway involved)
     * Stripe -> order is parked as unpaid and the customer is sent to
     *           Stripe Hosted Checkout; it only becomes "paid" once we
     *           verify the session on the way back.
     */
public function store(Request $request)
{
    // ================= CART CHECK =================
    $cart = session()->get('cart', []);

    if (count($cart) === 0) {
        return redirect()->route('cart.index')
            ->with('error', 'Your cart is empty');
    }

    // ================= VALIDATION =================
    $request->validate([
        'phone'          => 'required|string|max:20',
        'address'        => 'required|string|max:500',
        'payment_method' => 'required|in:cod,stripe',
    ]);

    // ================= USER =================
    $user = Auth::guard('frontend')->user();

    if (!$user) {
        return redirect()->route('login');
    }

    // ================= STOCK RE-CHECK =================
    // The cart holds a snapshot taken when the item was added. Stock can have
    // moved since (another order, an admin edit), so it is re-read here —
    // otherwise decrement() below can drive quantity negative.
    if ($problem = $this->stockProblem($cart)) {
        return redirect()->route('cart.index')->with('error', $problem);
    }

    // ================= TOTALS (re-validates any applied coupon) =================
    $totals = CartTotals::fromSession($user->id);

    // ================= ORDER NUMBER =================
    $orderNumber = 'ORD-' . now()->format('ymd') . '-' . rand(1000, 9999);

    $paymentMethod = $request->payment_method;
    $isCod         = $paymentMethod === 'cod';

    // ================= CREATE ORDER =================
    $order = DB::transaction(function () use (
        $cart, $user, $request, $totals, $orderNumber, $paymentMethod, $isCod
    ) {
        $order = Order::create([
            'order_number'       => $orderNumber,
            'user_id'            => $user->id,
            'name'               => $user->full_name,
            'phone'              => $request->phone,
            'address'            => $request->address,
            'subtotal'           => $totals->subtotal,
            'coupon_id'          => $totals->coupon?->id,
            'coupon_code'        => $totals->coupon?->code,
            'discount_amount'    => $totals->discount,
            'total_amount'       => $totals->total(),
            'payment_method'     => $paymentMethod,
            'payment_status'     => 'pending',
            // Stripe fills this in with the real payment intent id once paid
            'transaction_number' => $isCod
                ? 'COD-' . strtoupper(Str::random(12))
                : null,
            'order_status'       => 'pending',
        ]);

        foreach ($cart as $item) {
            $order->items()->create([
                'food_id'  => $item['food_id'],
                'price'    => $item['price'], // discounted price
                'quantity' => $item['quantity'],
                'total'    => $item['price'] * $item['quantity'],
            ]);
        }

        // COD reduces stock immediately; a Stripe order has not been
        // paid for yet, so its stock is held back until the payment clears.
        // The coupon is redeemed on the same schedule, for the same reason.
        if ($isCod) {
            $this->reduceStock($order);
            $this->redeemCoupon($order);
        }

        return $order;
    });

    // ================= COD: DONE =================
    if ($isCod) {
        session()->forget('cart');
        session()->forget(CartTotals::SESSION_KEY);

        return redirect()->route('order.success', $order->id)
            ->with('success', 'Order placed successfully');
    }

    // ================= STRIPE: HAND OFF TO CHECKOUT =================
    try {
        $session = app(StripeGateway::class)
            ->createCheckoutSession($order, $cart, $user->email, $totals->discount);
    } catch (\Throwable $e) {
        report($e);

        $order->update(['order_status' => 'cancelled']);

        return back()->with(
            'error',
            'Could not start the Stripe payment: ' . $e->getMessage()
        );
    }

    // Cart stays in the session until Stripe confirms the payment,
    // so a cancelled checkout does not lose the customer's items.
    return redirect()->away($session->url);
}


    /**
     * Stripe success_url — verify the session before trusting it.
     */
    public function stripeReturn(Request $request, Order $order)
    {
        $user = Auth::guard('frontend')->user();

        abort_unless($order->user_id === $user->id, 403);

        if ($order->payment_status === 'paid') {
            return redirect()->route('order.success', $order->id);
        }

        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('order.place')
                ->with('error', 'Missing Stripe session reference.');
        }

        try {
            $session = app(StripeGateway::class)->retrieveSession($sessionId);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('order.place')
                ->with('error', 'Could not verify the Stripe payment. Please try again.');
        }

        // The session must belong to *this* order and must actually be paid.
        // Without this check anyone could hit the success URL by hand.
        $belongsToOrder = (string) ($session->metadata->order_id ?? '') === (string) $order->id;

        if (!$belongsToOrder || $session->payment_status !== 'paid') {
            return redirect()->route('order.place')
                ->with('error', 'Payment was not completed.');
        }

        DB::transaction(function () use ($order, $session) {
            // Lock in case the customer refreshes the return URL twice,
            // otherwise stock would be decremented more than once.
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if ($locked->payment_status === 'paid') {
                return;
            }

            $locked->update([
                'payment_status'     => 'paid',
                'transaction_number' => $session->payment_intent ?? $session->id,
            ]);

            $this->reduceStock($locked);
            $this->redeemCoupon($locked);
        });

        session()->forget('cart');
        session()->forget(CartTotals::SESSION_KEY);

        return redirect()->route('order.success', $order->id)
            ->with('success', 'Payment successful. Your order has been placed.');
    }

    /**
     * Stripe cancel_url — customer backed out of the payment page.
     */
    public function stripeCancel(Order $order)
    {
        $user = Auth::guard('frontend')->user();

        abort_unless($order->user_id === $user->id, 403);

        if ($order->payment_status !== 'paid') {
            $order->update(['order_status' => 'cancelled']);
        }

        return redirect()->route('order.place')
            ->with('info', 'Payment cancelled. Your cart is still saved.');
    }

    /**
     * First reason the cart cannot be fulfilled right now, or null if it can.
     */
    private function stockProblem(array $cart): ?string
    {
        $foods = Food::whereIn('id', array_column($cart, 'food_id'))->get()->keyBy('id');

        foreach ($cart as $item) {
            $food = $foods->get($item['food_id']);

            if (!$food || $food->status != 1) {
                return "\"{$item['name']}\" is no longer available. Please remove it from your cart.";
            }

            if ($food->quantity < $item['quantity']) {
                return $food->quantity < 1
                    ? "\"{$food->name}\" just went out of stock. Please remove it from your cart."
                    : "Only {$food->quantity} left of \"{$food->name}\". Please lower the quantity.";
            }
        }

        return null;
    }

    /**
     * Deduct the ordered quantities from food stock.
     */
    private function reduceStock(Order $order): void
    {
        foreach ($order->items()->get() as $item) {
            Food::where('id', $item->food_id)
                ->decrement('quantity', $item->quantity);
        }
    }

    /**
     * Bank the coupon against this order — bumps the global counter and
     * writes the row that per-customer limits are counted from.
     */
    private function redeemCoupon(Order $order): void
    {
        if (!$order->coupon_id) {
            return;
        }

        CouponUsage::create([
            'coupon_id'       => $order->coupon_id,
            'registration_id' => $order->user_id,
            'order_id'        => $order->id,
            'discount_amount' => $order->discount_amount,
        ]);

        Coupon::whereKey($order->coupon_id)->increment('used_count');
    }


    /**
     * Order Success Page
     */
    public function success($order)
    {
        $order = Order::with([
            'items.food',
            'user'
        ])->findOrFail($order);

        /*
         * Ownership check. Without it any signed-in customer could walk
         * /order/success/1, /2, … and read another customer's name, phone,
         * delivery address, items and totals. viewOrder() already guards this
         * way; success() was missing it.
         */
        abort_unless(
            (int) $order->user_id === (int) Auth::guard('frontend')->id(),
            403,
        );

        return view('frontend.pages.order.success', compact('order'));
    }


    

// Admin: all orders list with filters
public function adminIndex(Request $request)
{
    // 🔥 eager load (delivery man name show করার জন্য)
    $query = Order::with(['deliveryRun.deliveryMan']);

    // Order number
    if ($request->filled('order_number')) {
        $query->where('order_number', trim($request->order_number));
    }

    // Customer name
    if ($request->filled('customer_name')) {
        $query->where('name', $request->customer_name);
    }

    // Phone
    if ($request->filled('phone')) {
        $query->where('phone', 'like', '%' . $request->phone . '%');
    }

    // Payment status
    if ($request->filled('payment_status')) {
        $query->where('payment_status', $request->payment_status);
    }

    // ✅ NEW: Order status
    if ($request->filled('order_status')) {
        $query->where('order_status', $request->order_status);
    }

    // ✅ NEW: Delivery man filter
    if ($request->filled('delivery_man_id')) {
        $query->whereHas('deliveryRun', function ($q) use ($request) {
            $q->where('delivery_man_id', $request->delivery_man_id);
        });
    }

    // Date range (flatpickr)
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [
            $request->from_date . ' 00:00:00',
            $request->to_date . ' 23:59:59',
        ]);
    }

    $orders = $query
        ->latest()
        ->paginate(15)
        ->withQueryString();

    // 🔽 Dropdown data
    $customers = Order::select('name')
        ->distinct()
        ->orderBy('name')
        ->get();

    $deliveryMen = DeliveryMan::orderBy('name')->get();

    return view(
        'backend.pages.orders.index',
        compact('orders', 'customers', 'deliveryMen')
    );
}



    // Admin: single order details
    public function adminShow(Order $order)
    {
        $order->load([
            'user',
            'items.food.subcategory.category'
        ]);

        return view('backend.pages.orders.show', compact('order'));
    }


    public function updateStatus(Request $request, Order $order)
{
    // 🚫 If order already assigned to delivery run
    if ($order->delivery_run_id) {
        return back()->with(
            'error',
            'This order is already assigned to a delivery run.'
        );
    }

    $allowedStatuses = ['pending', 'cooking', 'cancelled'];

    $request->validate([
        'order_status' => 'required|in:' . implode(',', $allowedStatuses),
    ]);

    // delivered protection (already done, still ok)
    if ($order->order_status === 'delivered') {
        return back()->with('error', 'Delivered order cannot be changed.');
    }

    $previous = $order->order_status;

    DB::transaction(function () use ($order, $request, $previous) {
        // COD takes stock as soon as the order is placed; a Stripe order only
        // takes it once the payment clears. An unpaid Stripe order therefore
        // never took any and must not be handed any back.
        $stockWasTaken = $order->payment_method === 'cod'
            || $order->payment_status === 'paid';

        $isBeingCancelled = $request->order_status === 'cancelled'
            && $previous !== 'cancelled';

        $order->order_status = $request->order_status;
        $order->save();

        if ($isBeingCancelled && $stockWasTaken) {
            $this->restoreStock($order);
        }
    });

    return back()->with('success', 'Order status updated successfully.');
}

    /**
     * Put an order's quantities back into food stock.
     */
    private function restoreStock(Order $order): void
    {
        foreach ($order->items()->get() as $item) {
            Food::where('id', $item->food_id)
                ->increment('quantity', $item->quantity);
        }
    }




public function markPaymentPaid(Order $order)
{
    // only COD orders allowed
    if ($order->payment_method !== 'cod') {
        return back()->with('error', 'Only COD payments can be marked as paid.');
    }

    // already paid protection
    if ($order->payment_status === 'paid') {
        return back()->with('info', 'Payment already marked as paid.');
    }

    // mark as paid
    $order->payment_status = 'paid';
    $order->save();

    return back()->with('success', 'COD payment marked as paid successfully.');
}

}
