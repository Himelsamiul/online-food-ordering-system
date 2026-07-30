<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviews) {}

    public function store(Request $request, Order $order): RedirectResponse
    {
        $customer = Auth::guard('frontend')->user();

        $validated = $request->validate([
            'food_id' => ['required', 'integer'],
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:120'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Pick a star rating.',
        ]);

        // One place decides eligibility — see ReviewService::canReview().
        $check = $this->reviews->canReview($customer, $order, (int) $validated['food_id']);

        if (!$check['ok']) {
            return back()->with('error', $check['message']);
        }

        $this->reviews->store($customer, $order, (int) $validated['food_id'], $validated);

        return back()->with('success', 'Thanks for the review!');
    }

    /** A customer may withdraw their own review. */
    public function destroy(Review $review): RedirectResponse
    {
        $customer = Auth::guard('frontend')->user();

        abort_unless((int) $review->registration_id === (int) $customer->id, 403);

        $review->delete();

        return back()->with('success', 'Your review was removed.');
    }
}
