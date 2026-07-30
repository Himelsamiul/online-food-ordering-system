<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory, LogsActivity;
protected $table = 'foods'; 
    protected $fillable = [
        'name',
        'sku',
        'subcategory_id',
        'unit_id',
        'price',
        'discount',
        'quantity',
        'low_stock_alert',
        'barcode',
        'image',
        'description',
        'status',
        'is_featured',
        'is_popular',
    ];

    protected $casts = [
        'is_featured'  => 'boolean',
        'is_popular'   => 'boolean',
        'rating_avg'   => 'decimal:2',
        'rating_count' => 'integer',
    ];

    /**
     * rating_avg / rating_count are a cache maintained by Review's model
     * events — they are never written by hand, so they stay out of $fillable
     * and out of the audit trail, where they would be pure noise.
     */
    public function activityIgnoredAttributes(): array
    {
        return ['rating_avg', 'rating_count'];
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }


        public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', Review::STATUS_APPROVED);
    }

    /**
     * Recompute the cached rating for one food.
     *
     * Written with a direct query builder update rather than a model save so
     * it cannot recurse back through Food's own model events, and so it never
     * bumps updated_at — a new review is not an edit of the food.
     */
    public static function syncRating(int $foodId): void
    {
        $stats = Review::query()
            ->where('food_id', $foodId)
            ->where('status', Review::STATUS_APPROVED)
            ->selectRaw('COUNT(*) as c, AVG(rating) as a')
            ->first();

        static::whereKey($foodId)->update([
            'rating_count' => (int) ($stats->c ?? 0),
            'rating_avg'   => round((float) ($stats->a ?? 0), 2),
        ]);
    }

    /** Rounded to the nearest half, which is what a star row can actually draw. */
    public function starValue(): float
    {
        return round(((float) $this->rating_avg) * 2) / 2;
    }

    public function hasRating(): bool
    {
        return $this->rating_count > 0;
    }
}
