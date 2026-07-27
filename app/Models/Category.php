<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'image',
        'status'
    ];

        public function subcategories()
    {
        return $this->hasMany(Subcategory::class, 'category_id');
    }

    /**
     * Foods reachable through this category's subcategories — used for the
     * "12 items" count on the storefront category cards.
     */
    public function foods()
    {
        return $this->hasManyThrough(Food::class, Subcategory::class);
    }
}
