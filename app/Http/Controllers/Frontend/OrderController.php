<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    $cart = session()->get('cart', []);

    if (count($cart) === 0) {
        return redirect()->route('cart.index')
            ->with('error', 'Your cart is empty');
    }

    // validate user input
    $request->validate([
        'phone'          => 'required|string|max:20',
        'address'        => 'required|string|max:500',
        'payment_method' => 'required|in:cod,stripe',
    ]);

    // calculate total (SERVER SIDE)
    $grandTotal = 0;
    foreach ($cart as $item) {
        $grandTotal += $item['price'] * $item['quantity'];
    }

    // 🔑 CORE LOGIC (THIS IS WHAT YOU WANTED)
    $paymentMethod = $request->payment_method;

    $paymentStatus = $paymentMethod === 'stripe'
        ? 'paid'
        : 'pending'; // COD

    $transactionNumber = $paymentMethod === 'stripe'
        ? 'STRIPE-' . uniqid()   // future real stripe id
        : null;

    // create order
    $order = Order::create([
        'order_number'       => 'ORD-' . date('ymd') . '-' . rand(1000, 9999),
        'user_id'            => Auth::guard('frontend')->id(),
        'name'               => Auth::guard('frontend')->user()->full_name,
        'phone'              => $request->phone,
        'address'            => $request->address,
        'total_amount'       => $grandTotal,
        'payment_method'     => $paymentMethod,
        'payment_status'     => $paymentStatus,
        'transaction_number' => $transactionNumber,
        'order_status'       => 'pending',
    ]);

    // save order items + reduce stock
    foreach ($cart as $item) {

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

    // clear cart
    session()->forget('cart');

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
}
