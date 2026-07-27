<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Registration extends Authenticatable
{
    use Notifiable, LogsActivity;

    protected $fillable = [
        'full_name',
        'username',
        'phone',
        'email',
        'dob',
        'password',
        'image',
        'address',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    public function activityLabel(): string
    {
        return $this->full_name ?: $this->username;
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function accountRequests()
    {
        return $this->hasMany(AccountRequest::class);
    }

    /** Latest still-unanswered help request, if any. */
    public function pendingAccountRequest()
    {
        return $this->hasOne(AccountRequest::class)
            ->where('status', AccountRequest::STATUS_PENDING)
            ->latestOfMany();
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /** Switch the account off; the customer is told to contact an admin. */
    public function deactivate(?string $reason = null): void
    {
        $this->forceFill([
            'is_active'           => false,
            'deactivated_at'      => now(),
            'deactivation_reason' => $reason,
        ])->save();
    }

    public function activate(): void
    {
        $this->forceFill([
            'is_active'           => true,
            'deactivated_at'      => null,
            'deactivation_reason' => null,
        ])->save();
    }
}
