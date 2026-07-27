<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'status',
    ];

    public function foods()
{
    return $this->hasMany(Food::class);
}

}
