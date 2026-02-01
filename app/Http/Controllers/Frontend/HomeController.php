<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class HomeController extends Controller
{
        public function index()
    {
          $categories = Category::where('status', 1)->get();
        return view('frontend.pages.home', compact('categories'));
    }

    public function Aboutus()
    {
        return view('frontend.pages.aboutus');
    }
}
