<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;

class MenuController extends Controller
{

public function show($id)
{
    $category = Category::with(['subcategories' => function ($q) {
        $q->where('status', 1);
    }])
    ->where('status', 1)
    ->findOrFail($id);

    return view(
        'frontend.pages.subcategory',
        compact('category')
    );
}

}
