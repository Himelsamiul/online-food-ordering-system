<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Registration extends Authenticatable
{
    use Notifiable;

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function loginHistories()
{
    return $this->hasMany(LoginHistory::class);
}

}
