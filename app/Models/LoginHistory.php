<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $fillable = [
        'registration_id',
        'ip_address',
        'country',
        'city',
        'user_agent',
        'logged_in_at',
        'logged_out_at',
    ];


        public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
