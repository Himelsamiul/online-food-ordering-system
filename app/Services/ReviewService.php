<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Registration;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

/**
 * Who is allowed to review what.
 *
 * Every rule lives here rather than in the controller, because the same
 * question gets asked in three places — when rendering the "Rate your order"
 * block, when accepting the form, and when deciding whether to show the
 * prompt at all — and they must not be allowed to disagree.
 */
class ReviewService
{
    /**
     * A customer may review a food only if they bought it on an order that
     * actually reached them, and only once per order.
     *
     * @return array{ok: bool, message: string}
     */
    public function canReview(Registration $customer, Order $order, int $foodId): array
    {
        if ((int) $order->user_id !== (int) $customer->id) {
            return ['ok' => false, 'message' => 'That order is not yours.'];
        }

        if ($order->order_status !== 'delivered') {
            return ['ok' => false, 'message' => 'You can review an order once it has been delivered.'];
        }

        $boughtIt = $order->items()->where('food_id', $foodId)->exists();

        if (!$boughtIt) {
            return ['ok' => false, 'message' => 'That item was not part of this order.'];
        }

        $already = Review::where('order_id', $order->id)->where('food_id', $foodId)->exists();

        if ($already) {
            return ['ok' => false, 'message' => 'You have already reviewed this item for this order.'];
        }

        return ['ok' => true, 'message' => ''];
    }

    /**
     * Items on a delivered order that still have no review.
     *
     * @return Collection<int, \App\Models\OrderItem>
     */
    public function reviewableItems(Order $order): Collection
    {
        if ($order->order_status !== 'delivered') {
            return new Collection();
        }

        $reviewed = Review::where('order_id', $order->id)->pluck('food_id')->all();

        return $order->items()
            ->with('food:id,name,image')
            ->whereNotIn('food_id', $reviewed)
            ->get();
    }

    /**
     * @param  array{rating: int, title?: ?string, comment?: ?string}  $data
     */
    public function store(Registration $customer, Order $order, int $foodId, array $data): Review
    {
        return Review::create([
            'registration_id' => $customer->id,
            'food_id'         => $foodId,
            'order_id'        => $order->id,
            'rating'          => $data['rating'],
            'title'           => $data['title'] ?? null,
            'comment'         => $data['comment'] ?? null,
            // Snapshot: the review must still read correctly if the account
            // is later renamed or deleted.
            'customer_name'   => $customer->full_name,
            'status'          => Review::STATUS_APPROVED,
        ]);
    }

    /**
     * Star distribution for a food, always 5 keys so the bar chart never has
     * a gap where nobody picked that score.
     *
     * @return array<int, int>
     */
    public function distributionFor(int $foodId): array
    {
        $rows = Review::approved()
            ->forFood($foodId)
            ->selectRaw('rating, COUNT(*) as c')
            ->groupBy('rating')
            ->pluck('c', 'rating')
            ->all();

        $out = [];

        for ($star = 5; $star >= 1; $star--) {
            $out[$star] = (int) ($rows[$star] ?? 0);
        }

        return $out;
    }
}
