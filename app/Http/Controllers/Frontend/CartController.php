<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Food;
use App\Support\CartTotals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Show cart page
     */
    public function index()
    {
        $cart = session()->get('cart', []);

        $totals = CartTotals::fromSession(Auth::guard('frontend')->id());

        return view('frontend.pages.cart', compact('cart', 'totals'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request, Food $food)
    {
        // Active check
        if ($food->status != 1) {
            return $this->addResponse($request, 'error', 'This item is not available.');
        }

        // Stock check
        if ($food->quantity < 1) {
            return $this->addResponse($request, 'error', 'This item is out of stock.');
        }

        $cart = session()->get('cart', []);

        // Already exists
        if (isset($cart[$food->id])) {
            return $this->addResponse($request, 'info', 'Item already in cart.');
        }

        // Price calculation
        $price = $food->price;
        $discount = $food->discount ?? 0;
        $finalPrice = $discount > 0
            ? $price - (($price * $discount) / 100)
            : $price;

        $cart[$food->id] = [
            'food_id'  => $food->id,
            'name'     => $food->name,
            'original_price' => $food->price,   //  food create time price
            'price'          => $finalPrice,     //  discounted / selling price,
            'quantity' => 1,
            'stock'    => $food->quantity,
            'image'    => $food->image,
        ];

        session()->put('cart', $cart);

        return $this->addResponse($request, 'success', 'Item added to cart.');
    }

    /**
     * The menu page adds to the cart over AJAX so its filters and scroll
     * position survive; every other page still gets the usual redirect.
     */
    private function addResponse(Request $request, string $status, string $message)
    {
        if ($request->ajax()) {
            return response()->json([
                'status'     => $status,
                'message'    => $message,
                'cart_count' => count(session()->get('cart', [])),
            ]);
        }

        return back()->with($status, $message);
    }

    /**
     * Update quantity (+ / -)
     */
    public function update(Request $request, Food $food)
    {
        $cart = session()->get('cart', []);

        if (!isset($cart[$food->id])) {
            return back()->with('error', 'Item not found in cart.');
        }

        $action = $request->action; // plus | minus
        $currentQty = $cart[$food->id]['quantity'];
        $stock = $food->quantity;

        // Max allowed quantity
        $maxQty = min(10, $stock);

        if ($action === 'plus') {

            if ($currentQty >= $maxQty) {
                return back()->with(
                    'info',
                    "You can order maximum {$maxQty} item(s) for this product."
                );
            }

            $cart[$food->id]['quantity']++;

        } elseif ($action === 'minus') {

            if ($currentQty > 1) {
                $cart[$food->id]['quantity']--;
            }
        }

        session()->put('cart', $cart);

        return back();
    }

    /**
     * Remove single item
     */
    public function remove(Food $food)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$food->id])) {
            unset($cart[$food->id]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Clear full cart
     */
    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Cart cleared successfully.');
    }
}
