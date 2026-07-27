<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A single-use password reset link.
 *
 * Only the hash of the token is stored; the plain token exists in the emailed
 * URL and nowhere else.
 */
class PasswordResetLink extends Model
{
    protected $fillable = [
        'guard', 'account_id', 'email', 'token_hash',
        'account_request_id', 'issued_by', 'issued_by_name',
        'expires_at', 'used_at', 'used_ip',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function accountRequest()
    {
        return $this->belongsTo(AccountRequest::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query->whereNull('used_at')->where('expires_at', '>', now());
    }

    public function scopeFor(Builder $query, string $guard, string $email): Builder
    {
        return $query->where('guard', $guard)->where('email', $email);
    }
}
