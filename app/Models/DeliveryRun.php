<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_man_id',
        'order_ids',
        'departed_at',
        'returned_at',
        'status',
        'note',
    ];

    protected $casts = [
        'order_ids'   => 'array',
        'departed_at'=> 'datetime',
        'returned_at'=> 'datetime',
    ];

    /**
     * Relation: Delivery Man
     */
    public function deliveryMan()
    {
        return $this->belongsTo(DeliveryMan::class);
    }
}
