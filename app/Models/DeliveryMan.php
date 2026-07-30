<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A rider — now an account they can sign into, not just a record about them.
 *
 * Extends Authenticatable so the `rider` guard can use it. `status` doubles as
 * the active flag: a rider switched off cannot be assigned a run and cannot
 * sign in.
 */
class DeliveryMan extends Authenticatable
{
    use HasFactory, LogsActivity;

    protected $table = 'delivery_men';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'address',
        'nid_number',
        'photo',
        'note',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status'        => 'boolean',
            'password'      => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /** The password hash is never worth putting in the audit trail. */
    public function activityIgnoredAttributes(): array
    {
        return ['password', 'remember_token', 'last_login_at', 'last_login_ip'];
    }

    public function deliveryRuns()
    {
        return $this->hasMany(DeliveryRun::class);
    }

    /** Runs still out on the road. */
    public function activeRuns()
    {
        return $this->hasMany(DeliveryRun::class)->where('status', 'on_the_way');
    }

    public function isActive(): bool
    {
        return (bool) $this->status;
    }

    /** A rider with no password set has never been given portal access. */
    public function canSignIn(): bool
    {
        return $this->isActive() && filled($this->password);
    }
}
