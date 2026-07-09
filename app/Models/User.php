<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Permissions granted directly to this admin.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Superadmin can do everything; a normal admin only what was granted.
     */
    public function hasPermission(string $name): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        // Uses the loaded relation (cached after first access) to avoid
        // a query per check when the sidebar asks about many permissions.
        return $this->permissions->contains('name', $name);
    }
}
