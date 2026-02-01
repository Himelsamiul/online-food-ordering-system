<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'status',
    ];

    // Laravel-level relation (no foreign key constraint)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
