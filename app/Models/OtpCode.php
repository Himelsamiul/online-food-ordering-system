<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * An issued one-time code. Never holds the plain code — only its hash.
 */
class OtpCode extends Model
{
    public const PURPOSE_REGISTER = 'register';
    public const PURPOSE_RESET    = 'password_reset';

    protected $fillable = [
        'guard', 'purpose', 'identifier', 'code_hash',
        'attempts', 'expires_at', 'consumed_at', 'ip_address',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function isUsable(): bool
    {
        return !$this->isConsumed() && !$this->isExpired();
    }

    public function scopeFor(Builder $query, string $guard, string $purpose, string $identifier): Builder
    {
        return $query->where('guard', $guard)
            ->where('purpose', $purpose)
            ->where('identifier', $identifier);
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query->whereNull('consumed_at')->where('expires_at', '>', now());
    }
}
