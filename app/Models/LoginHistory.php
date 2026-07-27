<?php

namespace App\Models;

use App\Support\Agent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Customer sign-ins. The admin-side equivalent is AdminLoginHistory.
 */
class LoginHistory extends Model
{
    protected $fillable = [
        'registration_id',
        'ip_address',
        'country',
        'city',
        'user_agent',
        'browser',
        'device',
        'platform',
        'session_id',
        'successful',
        'logged_in_at',
        'logged_out_at',
    ];

    protected $casts = [
        'successful'    => 'boolean',
        'logged_in_at'  => 'datetime',
        'logged_out_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    public function durationSeconds(): ?int
    {
        if (!$this->logged_in_at || !$this->logged_out_at) {
            return null;
        }

        return (int) $this->logged_in_at->diffInSeconds($this->logged_out_at);
    }

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
        return $this->logged_in_at && !$this->logged_out_at;
    }

    /** Falls back to parsing user_agent for rows written before the columns existed. */
    public function client(): string
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
