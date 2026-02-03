<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'image',
        'status',
    ];

    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    public function foods()
{
    return $this->hasMany(Food::class);
}
}
