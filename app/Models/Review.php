<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use LogsActivity;

    public const STATUS_APPROVED = 'approved';
    public const STATUS_HIDDEN   = 'hidden';

    protected $fillable = [
        'registration_id',
        'food_id',
        'order_id',
        'rating',
        'title',
        'comment',
        'status',
        'customer_name',
        'admin_reply',
        'admin_reply_by',
        'replied_at',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'replied_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_APPROVED,
    ];

    /* ------------------------------------------------------------ relations */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /* --------------------------------------------------------------- scopes */

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeHidden(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_HIDDEN);
    }

    public function scopeForFood(Builder $query, int $foodId): Builder
    {
        return $query->where('food_id', $foodId);
    }

    /* -------------------------------------------------------------- helpers */

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function hasReply(): bool
    {
        return filled($this->admin_reply);
    }

    public function activityLabel(): string
    {
        return $this->rating . '★ on ' . ($this->food?->name ?? "food #{$this->food_id}");
    }

    /**
     * The rating cache on foods must follow every write, including the ones
     * that go through the admin moderation screen — hiding a one-star review
     * has to move the average, or the number on the menu stops being true.
     */
    protected static function booted(): void
    {
        $sync = fn (Review $review) => Food::syncRating($review->food_id);

        static::created($sync);
        static::updated($sync);
        static::deleted($sync);
    }
}
