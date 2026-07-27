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
        'is_featured' => 'boolean',
        'is_popular'  => 'boolean',
    ];

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
}
