<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Food;

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
}
