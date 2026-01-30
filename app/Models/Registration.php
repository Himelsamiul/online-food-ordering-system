<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'full_name',
        'username',
        'phone',
        'email',
        'dob',
        'password',
        'image',
        'address',
    ];
}
