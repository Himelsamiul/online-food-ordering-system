<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id','food_id','price','quantity','total'
    ];

    // Item belongs to order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Item belongs to food
    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}