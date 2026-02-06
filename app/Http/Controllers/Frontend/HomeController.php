<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ContactMessage;

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


    public function contactPage()
{
    return view('frontend.pages.contactus');
}
public function contactStore(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'phone' => 'required|string|max:20',
        'name'  => 'nullable|string|max:100',
        'message' => 'nullable|string|max:1000',
    ]);

    ContactMessage::create($request->only([
        'name',
        'email',
        'phone',
        'message',
    ]));

    return back()->with('success', 'Thank you! Your message has been sent.');
}


public function adminContactList()
{
    $messages = ContactMessage::latest()->paginate(20);
    return view('backend.pages.contactus', compact('messages'));
}

}
