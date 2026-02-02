<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number','user_id','name','phone','address',
        'total_amount','payment_method','payment_status',
        'transaction_number','order_status'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }


        public function user()
    {
        return $this->belongsTo(Registration::class, 'user_id');
    }
}