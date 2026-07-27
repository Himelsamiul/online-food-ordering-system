<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line in a support thread.
 *
 * Not audit-logged: the messages ARE the record, and mirroring every one into
 * activity_logs would double the write cost and drown the trail.
 */
class ChatMessage extends Model
{
    public const FROM_CUSTOMER = 'customer';
    public const FROM_ADMIN    = 'admin';
    public const FROM_SYSTEM   = 'system';

    protected $fillable = [
        'chat_conversation_id',
        'sender_type',
        'sender_id',
        'sender_name',
        'body',
        'read_at',
        'ip_address',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'chat_conversation_id');
    }

    public function scopeAfter(Builder $query, int $id): Builder
    {
        return $id > 0 ? $query->where('id', '>', $id) : $query;
    }

    public function isFromCustomer(): bool
    {
        return $this->sender_type === self::FROM_CUSTOMER;
    }

    public function isFromAdmin(): bool
    {
        return $this->sender_type === self::FROM_ADMIN;
    }

    public function isSystem(): bool
    {
        return $this->sender_type === self::FROM_SYSTEM;
    }

    /**
     * The shape both the widget and the admin pane consume.
     *
     * Kept in one place so the two front-ends can never drift on field names,
     * and so `body` is only ever handed over raw — every consumer escapes it
     * client-side rather than trusting a server-side sanitiser.
     *
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'id'       => $this->id,
            'from'     => $this->sender_type,
            'name'     => $this->sender_name,
            'body'     => $this->body,
            'time'     => $this->created_at?->format('g:i A'),
            'date'     => $this->created_at?->format('d M Y'),
            'iso'      => $this->created_at?->toIso8601String(),
            'read'     => (bool) $this->read_at,
        ];
    }
}
