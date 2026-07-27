<?php

namespace App\Models;

use App\Support\Agent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Someone who cannot get in, asking for help.
 *
 * One table serves four inboxes because the lifecycle is identical — pending →
 * approved/rejected, handled by whom, audited, exported. The dashboards are
 * filtered views over `type` × `requester_type`:
 *
 *   password_reset + admin     → superadmin issues a signed reset link
 *   activation     + admin     → superadmin switches the admin back on
 *   password_reset + customer  → admin issues a customer reset link
 *   activation     + customer  → admin switches the customer back on
 *
 * The requester cannot sign in when they send this, so the form is public and
 * the row keeps the typed name/email rather than trusting a session. The email
 * is validated against the account it names before the row is ever written.
 */
class AccountRequest extends Model
{
    public const TYPE_PASSWORD   = 'password_reset';
    public const TYPE_ACTIVATION = 'activation';

    public const FROM_ADMIN    = 'admin';
    public const FROM_CUSTOMER = 'customer';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'type', 'requester_type', 'registration_id', 'user_id',
        'name', 'username', 'email', 'phone', 'requested_role',
        'reason', 'message',
        'status', 'admin_note', 'handled_by', 'handled_by_name', 'handled_at',
        'ip_address', 'user_agent', 'read_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
        'read_at'    => 'datetime',
    ];

    public const TYPES = [
        self::TYPE_PASSWORD   => ['label' => 'Password Reset', 'icon' => 'feather-key',        'tone' => 'warning'],
        self::TYPE_ACTIVATION => ['label' => 'Reactivation',   'icon' => 'feather-user-check', 'tone' => 'info'],
    ];

    public const STATUSES = [
        self::STATUS_PENDING  => ['label' => 'Pending',  'tone' => 'warning'],
        self::STATUS_RESOLVED => ['label' => 'Approved', 'tone' => 'success'],
        self::STATUS_REJECTED => ['label' => 'Rejected', 'tone' => 'danger'],
    ];

    /* --------------------------------------------------------- relations */

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function passwordResetLinks()
    {
        return $this->hasMany(PasswordResetLink::class);
    }

    /**
     * The account this request is about, whichever table it lives in.
     *
     * @return Registration|User|null
     */
    public function account()
    {
        return $this->isFromAdmin() ? $this->adminUser : $this->registration;
    }

    /* ------------------------------------------------------------ display */

    public function typeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst(str_replace('_', ' ', (string) $this->type));
    }

    public function typeIcon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'feather-help-circle';
    }

    public function typeTone(): string
    {
        return self::TYPES[$this->type]['tone'] ?? 'secondary';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst((string) $this->status);
    }

    public function statusTone(): string
    {
        return self::STATUSES[$this->status]['tone'] ?? 'secondary';
    }

    public function requesterLabel(): string
    {
        return $this->isFromAdmin() ? 'Admin' : 'Customer';
    }

    public function clientSummary(): string
    {
        $agent = Agent::parse($this->user_agent);

        return Agent::summary($agent['browser'], $agent['platform'], $agent['device']);
    }

    /* ------------------------------------------------------------- state */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPasswordReset(): bool
    {
        return $this->type === self::TYPE_PASSWORD;
    }

    public function isActivation(): bool
    {
        return $this->type === self::TYPE_ACTIVATION;
    }

    public function isFromAdmin(): bool
    {
        return $this->requester_type === self::FROM_ADMIN;
    }

    /** True when the named account was found and can actually be acted on. */
    public function hasAccount(): bool
    {
        return $this->isFromAdmin()
            ? $this->user_id !== null
            : $this->registration_id !== null;
    }

    /* ------------------------------------------------------------- scopes */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFromAdmins(Builder $query): Builder
    {
        return $query->where('requester_type', self::FROM_ADMIN);
    }

    public function scopeFromCustomers(Builder $query): Builder
    {
        return $query->where('requester_type', self::FROM_CUSTOMER);
    }

    public function scopePasswordResets(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PASSWORD);
    }

    public function scopeActivations(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_ACTIVATION);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeOfRequester(Builder $query, ?string $requester): Builder
    {
        return $requester ? $query->where('requester_type', $requester) : $query;
    }

    public function scopeOfStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }
}
