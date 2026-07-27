<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_number','user_id','name','phone','address',
        'subtotal','coupon_id','coupon_code','discount_amount',
        'total_amount','payment_method','payment_status',
        'transaction_number','order_status'
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }


        public function user()
    {
        return $this->belongsTo(Registration::class, 'user_id');
    }

    public function deliveryRun()
{
    return $this->belongsTo(DeliveryRun::class);
}

}