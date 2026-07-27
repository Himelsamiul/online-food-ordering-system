<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title', 'subtitle', 'image', 'coupon_id', 'link_url',
        'starts_at', 'ends_at', 'sort_order', 'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'status'    => 'boolean',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Active banners inside their scheduled window, in display order.
     */
    public function scopeLive($query)
    {
        $now = now();

        return $query->where('status', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }
}
