<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Review moderation.
 *
 * Reviews go live on submission — holding every one for approval means a shop
 * with no moderator ends up with an empty review section. An admin can hide
 * anything abusive, and hiding immediately re-syncs the food's cached average
 * through Review's model events.
 */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with(['food:id,name,image', 'customer:id,full_name,email'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->query('q') . '%';

                $query->where(function ($q) use ($term) {
                    $q->where('comment', 'like', $term)
                        ->orWhere('title', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhereHas('food', fn ($f) => $f->where('name', 'like', $term));
                });
            })
            ->when($request->query('status') === 'approved', fn ($q) => $q->approved())
            ->when($request->query('status') === 'hidden', fn ($q) => $q->hidden())
            ->when(
                in_array($request->query('rating'), ['1', '2', '3', '4', '5'], true),
                fn ($q) => $q->where('rating', (int) $request->query('rating'))
            )
            ->when($request->query('unanswered') === '1', fn ($q) => $q->whereNull('admin_reply'))
            ->latest('id')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        return view('backend.pages.reviews.index', [
            'reviews'   => $reviews,
            'stats'     => $this->stats(),
            'filters'   => $request->only(['q', 'status', 'rating', 'unanswered']),
            'canEdit'   => $admin->hasPermission('reviews.edit'),
            'canDelete' => $admin->hasPermission('reviews.delete'),
        ]);
    }

    public function updateStatus(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,hidden'],
        ]);

        $was = $review->status;
        $review->update(['status' => $validated['status']]);

        AuditLogger::system(
            AuditLogger::ACTION_STATUS_CHANGED,
            'Reviews',
            ($validated['status'] === Review::STATUS_HIDDEN ? 'Hid' : 'Restored')
                . " a {$review->rating}-star review of " . ($review->food?->name ?? 'a food'),
            $review,
            ['status' => $was],
            ['status' => $validated['status']],
        );

        return back()->with(
            'success',
            $validated['status'] === Review::STATUS_HIDDEN
                ? 'Review hidden. The food rating has been recalculated.'
                : 'Review is visible again.'
        );
    }

    public function reply(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => ['required', 'string', 'max:800'],
        ]);

        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        $review->update([
            'admin_reply'    => $validated['admin_reply'],
            'admin_reply_by' => $admin->name,
            'replied_at'     => now(),
        ]);

        return back()->with('success', 'Reply posted under the review.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $label = $review->activityLabel();

        $review->delete();

        AuditLogger::log(
            AuditLogger::ACTION_DELETED,
            "Deleted review: {$label}",
            null,
            null,
            null,
            'Reviews',
            $label,
        );

        return back()->with('success', 'Review deleted and the rating recalculated.');
    }

    /** @return array<string, mixed> */
    private function stats(): array
    {
        return [
            'total'      => Review::count(),
            'hidden'     => Review::hidden()->count(),
            'average'    => round((float) Review::approved()->avg('rating'), 2),
            'unanswered' => Review::approved()->whereNull('admin_reply')->count(),
        ];
    }
}
