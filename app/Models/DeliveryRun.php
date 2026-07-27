<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRun extends Model
{
    use HasFactory, LogsActivity;

    /** Runs have no name of their own — identify them by number. */
    public function activityLabel(): string
    {
        return 'Run #' . $this->id;
    }

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


    public function orders()
{
    return $this->hasMany(Order::class);
}

}
