<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Food;
use App\Models\Promotion;

class HomeController extends Controller
{
        public function index()
    {
        $categories = Category::where('status', 1)
            ->withCount(['foods' => fn ($q) => $q->where('foods.status', 1)
                                                 ->where('foods.quantity', '>', 0)])
            ->orderBy('name')
            ->get();

        // Both rows are admin-curated via the checkboxes on the food form.
        $featuredFoods = $this->highlightRow('is_featured');
        $popularFoods  = $this->highlightRow('is_popular');

        $promotions = Promotion::live()->with('coupon')->get();

        return view('frontend.pages.home', compact(
            'categories', 'featuredFoods', 'popularFoods', 'promotions'
        ));
    }

    /**
     * One row of highlighted dishes — only in-stock, sellable items.
     */
    private function highlightRow(string $flag)
    {
        return Food::with('subcategory')
            ->where('status', 1)
            ->where('quantity', '>', 0)
            ->where($flag, true)
            ->orderByDesc('id')
            ->take(8)
            ->get();
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


public function adminContactDelete($id)
{
    $message = ContactMessage::findOrFail($id);
    $message->delete();

    return back()->with('success', 'Contact message deleted successfully.');
}
}