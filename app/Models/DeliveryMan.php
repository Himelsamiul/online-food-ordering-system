<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryMan extends Model
{
    use HasFactory;

    protected $table = 'delivery_men';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'nid_number',
        'photo',
        'note',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

 
public function deliveryRuns()
{
    return $this->hasMany(DeliveryRun::class);
}

}
