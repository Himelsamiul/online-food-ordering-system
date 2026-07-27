<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_ADMIN      = 'admin';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_admin',
        'role',
        'is_active',
        'deactivated_at',
        'deactivation_reason',
        'password_changed_at',
        'notifications_seen_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'is_admin'              => 'boolean',
            'is_active'             => 'boolean',
            'deactivated_at'        => 'datetime',
            'password_changed_at'   => 'datetime',
            'notifications_seen_at' => 'datetime',
        ];
    }

    /* --------------------------------------------------------- relations */

    /** Permissions granted directly to this admin. */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(AdminLoginHistory::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function accountRequests()
    {
        return $this->hasMany(AccountRequest::class);
    }

    /** Reset links issued for this admin. Keyed by guard, not a foreign key. */
    public function passwordResetLinks()
    {
        return $this->hasMany(PasswordResetLink::class, 'account_id')
            ->where('guard', 'web');
    }

    /* ------------------------------------------------------------- status */

    public function isSuperadmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function roleLabel(): string
    {
        return $this->isSuperadmin() ? 'Super Admin' : 'Admin';
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Switch the account off. The admin is blocked at login and pointed at the
     * activation request form.
     */
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

    public function activityLabel(): string
    {
        return $this->name . ' (' . $this->email . ')';
    }

    /** notifications_seen_at moves on every bell open — pure noise in the trail. */
    public function activityIgnoredAttributes(): array
    {
        return ['notifications_seen_at', 'password_changed_at'];
    }

    /* -------------------------------------------------------- permissions */

    /**
     * Superadmin can do everything; a normal admin only what was granted.
     *
     * Two shapes are accepted:
     *   "foods.delete" — an exact granted permission
     *   "foods"        — module shorthand, true when ANY action on that module
     *                    is granted. The sidebar uses this to decide whether a
     *                    menu entry is worth showing at all.
     */
    public function hasPermission(string $name): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        // Uses the loaded relation (cached after first access) to avoid a query
        // per check when the sidebar asks about many permissions.
        $granted = $this->permissions->pluck('name');

        if (str_contains($name, '.')) {
            return $granted->contains($name);
        }

        return $granted->contains(fn ($p) => str_starts_with($p, $name . '.'));
    }

    /**
     * True when at least one of the given permissions is held.
     *
     * @param  list<string>  $names
     */
    public function hasAnyPermission(array $names): bool
    {
        foreach ($names as $name) {
            if ($this->hasPermission($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Permission names held, for the admin-users edit form.
     *
     * @return list<string>
     */
    public function permissionNames(): array
    {
        return $this->permissions->pluck('name')->all();
    }

    /**
     * How many of a module's actions this admin holds — drives the "3 / 4"
     * counter on the manage-admins list.
     */
    public function moduleGrantCount(string $module): int
    {
        if ($this->isSuperadmin()) {
            return count(Permission::namesForModule($module));
        }

        $held = $this->permissions->pluck('name')->all();

        return count(array_intersect(Permission::namesForModule($module), $held));
    }

    /* ------------------------------------------------------------- scopes */

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('is_admin', true);
    }

    public function scopeSuperadmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_SUPERADMIN);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
