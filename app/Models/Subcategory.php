<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use LogsActivity;

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
