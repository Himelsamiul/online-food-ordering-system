<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;
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
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
