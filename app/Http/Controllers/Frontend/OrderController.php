<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    return view('frontend.pages.order.place', compact('cart', 'user'));
}

    /**
     * Store Order (COD for now)
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

    // ================= TOTAL CALCULATION =================
    $grandTotal = 0;

    foreach ($cart as $item) {
        $grandTotal += $item['price'] * $item['quantity'];
    }

    // ================= ORDER NUMBER =================
    $orderNumber = 'ORD-' . now()->format('ymd') . '-' . rand(1000, 9999);

    // ================= PAYMENT LOGIC =================
    $paymentMethod = $request->payment_method;

    if ($paymentMethod === 'stripe') {

        // future real stripe transaction id
        $paymentStatus = 'paid';
        $transactionNumber = 'STRIPE-' . strtoupper(Str::random(12));

    } else {
        // COD
        $paymentStatus = 'pending';
        $transactionNumber = 'COD-' . strtoupper(Str::random(12));
    }

    // ================= CREATE ORDER =================
    $order = Order::create([
        'order_number'       => $orderNumber,
        'user_id'            => $user->id,
        'name'               => $user->full_name,
        'phone'              => $request->phone,
        'address'            => $request->address,
        'total_amount'       => $grandTotal,
        'payment_method'     => $paymentMethod,
        'payment_status'     => $paymentStatus,
        'transaction_number' => $transactionNumber,
        'order_status'       => 'pending',
    ]);

    // ================= ORDER ITEMS + STOCK REDUCE =================
    foreach ($cart as $item) {

        // save order items (relation)
        $order->items()->create([
            'food_id'  => $item['food_id'],
            'price'    => $item['price'], // discounted price
            'quantity' => $item['quantity'],
            'total'    => $item['price'] * $item['quantity'],
        ]);

        // reduce food stock
        Food::where('id', $item['food_id'])
            ->decrement('quantity', $item['quantity']);
    }

    // ================= CLEAR CART =================
    session()->forget('cart');

    // ================= REDIRECT =================
    return redirect()->route('order.success', $order->id)
        ->with('success', 'Order placed successfully');
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

        return view('frontend.pages.order.success', compact('order'));
    }


    
// Admin: all orders list with filters
public function adminIndex(Request $request)
{
    $query = Order::query();

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

    // Date range
    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [
            $request->from_date . ' 00:00:00',
            $request->to_date . ' 23:59:59',
        ]);
    }

    $orders = $query->latest()->paginate(15)->withQueryString();

    // customer dropdown list
    $customers = Order::select('name')->distinct()->orderBy('name')->get();

    return view('backend.pages.orders.index', compact('orders', 'customers'));
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
    // allowed statuses
    $allowedStatuses = ['pending', 'cooking', 'delivered', 'cancelled', 'out_for_delivery'];

    // validation
    $request->validate([
        'order_status' => 'required|in:' . implode(',', $allowedStatuses),
    ]);

    // lock delivered orders
    if ($order->order_status === 'delivered') {
        return back()->with('error', 'Delivered order cannot be changed.');
    }

    // update status
    $order->order_status = $request->order_status;
    $order->save();

    return back()->with('success', 'Order status updated successfully.');
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
