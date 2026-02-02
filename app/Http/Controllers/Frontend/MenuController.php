<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Food;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // CATEGORY → SUBCATEGORIES
    public function show($id)
    {
        $category = Category::with(['subcategories' => function ($q) {
            $q->where('status', 1);
        }])
        ->where('status', 1)
        ->findOrFail($id);

        return view('frontend.pages.subcategory', compact('category'));
    }

    // SUBCATEGORY → FOODS
    public function foods(Subcategory $subcategory)
    {
        $foods = Food::where('subcategory_id', $subcategory->id)
            ->where('status', 1)
            ->get();

        return view('frontend.pages.foods', compact('subcategory', 'foods'));
    }


    public function foodDetails(Food $food)
{
    // optional: ensure only active food
    if ($food->status != 1) {
        abort(404);
    }

    return view('frontend.pages.food-details', compact('food'));
}

public function addToCart(Request $request, Food $food)
{
    // Only active food
    if ($food->status != 1) {
        return redirect()->back()->with('error', 'This item is not available.');
    }

    // Stock check
    if ($food->quantity < 1) {
        return redirect()->back()->with('error', 'This item is out of stock.');
    }

    // Get cart (unique items only)
    $cart = session()->get('cart', []);

    // 🚫 If already exists, do NOT increase quantity
    if (isset($cart[$food->id])) {
        return redirect()->back()->with('info', 'This item is already in your cart.');
    }

    // Price calculation
    $price = $food->price;
    $discount = $food->discount ?? 0;

    $finalPrice = $discount > 0
        ? $price - (($price * $discount) / 100)
        : $price;

    // ✅ Add new product (quantity fixed = 1)
    $cart[$food->id] = [
        'food_id' => $food->id,
        'name'    => $food->name,
        'price'   => $finalPrice,
        'image'   => $food->image,
    ];

    // Save cart
    session()->put('cart', $cart);

    return redirect()->back()->with('success', 'Item added to cart successfully!');
}



}
