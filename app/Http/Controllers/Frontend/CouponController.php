<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Support\CartTotals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:40',
        ]);

        $cart = session()->get('cart', []);

        if (count($cart) === 0) {
            return back()->with('error', 'Your cart is empty.');
        }

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))->first();

        if (!$coupon) {
            return back()->with('error', 'That coupon code is not valid.');
        }

        $user  = Auth::guard('frontend')->user();
        $check = $coupon->validateFor(CartTotals::subtotalOf($cart), $user?->id);

        if (!$check['ok']) {
            return back()->with('error', $check['message']);
        }

        session()->put(CartTotals::SESSION_KEY, $coupon->code);

        return back()->with(
            'success',
            "Coupon {$coupon->code} applied — you saved ৳" . number_format($check['discount'], 2)
        );
    }

    public function remove()
    {
        session()->forget(CartTotals::SESSION_KEY);

        return back()->with('info', 'Coupon removed.');
    }
}
