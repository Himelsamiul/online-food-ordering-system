<?php

namespace App\Models;

use App\Support\Agent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin panel sign-ins, successful and failed.
 *
 * Separate from login_histories, which is keyed to registration_id and tracks
 * customers only; merging them would need a polymorphic column and a rewrite of
 * every existing query against that table.
 */
class AdminLoginHistory extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'user_role',
        'ip_address', 'country', 'city',
        'user_agent', 'browser', 'device', 'platform', 'session_id',
        'successful', 'failure_reason',
        'logged_in_at', 'logged_out_at', 'logout_type',
    ];

    protected $casts = [
        'successful'    => 'boolean',
        'logged_in_at'  => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ------------------------------------------------------------ session */

    /** Seconds the session lasted, or null while it is still open. */
    public function durationSeconds(): ?int
    {
        if (!$this->logged_in_at || !$this->logged_out_at) {
            return null;
        }

        return (int) $this->logged_in_at->diffInSeconds($this->logged_out_at);
    }

    /** "1h 12m" — compact enough for a table cell. */
    public function duration(): ?string
    {
        $seconds = $this->durationSeconds();

        if ($seconds === null) {
            return null;
        }

        if ($seconds < 60) {
            return $seconds . 's';
        }

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function isOnline(): bool
    {
        return $this->successful && $this->logged_in_at && !$this->logged_out_at;
    }

    public function statusLabel(): string
    {
        if (!$this->successful) {
            return 'Failed';
        }

        return $this->isOnline() ? 'Active' : 'Completed';
    }

    public function statusTone(): string
    {
        if (!$this->successful) {
            return 'danger';
        }

        return $this->isOnline() ? 'success' : 'secondary';
    }

    /* ------------------------------------------------------------ display */

    /**
     * Browser/OS/device read-out. Falls back to parsing the stored user agent
     * for rows written before those columns existed.
     */
    public function device(): string
    {
        if ($this->browser || $this->platform) {
            return Agent::summary($this->browser, $this->platform, $this->device);
        }

        $agent = Agent::parse($this->user_agent);

        return Agent::summary($agent['browser'], $agent['platform'], $agent['device']);
    }

    public function location(): string
    {
        $parts = array_filter([$this->city, $this->country]);

        return $parts ? implode(', ', $parts) : 'Unknown';
    }

    /* ------------------------------------------------------------- scopes */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('user_name', 'like', "%{$term}%")
                ->orWhere('user_email', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%")
                ->orWhere('country', 'like', "%{$term}%");
        });
    }

    public function scopeOutcome(Builder $query, ?string $outcome): Builder
    {
        return match ($outcome) {
            'success' => $query->where('successful', true),
            'failed'  => $query->where('successful', false),
            'online'  => $query->where('successful', true)->whereNull('logged_out_at'),
            default   => $query,
        };
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    public function scopeOfRole(Builder $query, ?string $role): Builder
    {
        return $role ? $query->where('user_role', $role) : $query;
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('logged_in_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('logged_in_at', '<=', $to);
        }

        return $query;
    }
}
