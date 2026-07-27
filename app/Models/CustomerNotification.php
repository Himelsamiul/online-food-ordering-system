<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line in the storefront notification bell.
 *
 * Not audit-logged — these are derived from actions that are already in the
 * trail, and mirroring them would double every write.
 */
class CustomerNotification extends Model
{
    public const TYPE_ORDER   = 'order';
    public const TYPE_CHAT    = 'chat';
    public const TYPE_ACCOUNT = 'account';
    public const TYPE_PROMO   = 'promo';

    protected $fillable = [
        'registration_id',
        'type',
        'title',
        'body',
        'url',
        'icon',
        'tone',
        'subject_type',
        'subject_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeFor(Builder $query, int $registrationId): Builder
    {
        return $query->where('registration_id', $registrationId);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * The shape the bell dropdown consumes.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'id'     => $this->id,
            'type'   => $this->type,
            'title'  => $this->title,
            'body'   => $this->body,
            'icon'   => $this->icon ?: 'fa-bell',
            'tone'   => $this->tone ?: 'info',
            'url'    => $this->url ? route('notifications.open', $this->id) : null,
            'unread' => $this->isUnread(),
            'ago'    => $this->created_at?->diffForHumans(null, true),
            'iso'    => $this->created_at?->toIso8601String(),
        ];
    }
}
