<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One support thread per customer.
 *
 * Deliberately does NOT use LogsActivity — a chat thread's updated_at moves on
 * every single message and would bury the audit trail. Chat events that matter
 * (opened, closed, reopened, deleted) are logged explicitly by ChatService.
 */
class ChatConversation extends Model
{
    public const STATUS_OPEN   = 'open';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'registration_id',
        'status',
        'assigned_admin_id',
        'assigned_admin_name',
        'last_message_at',
        'last_message_preview',
        'last_message_from',
        'customer_unread',
        'admin_unread',
        'closed_at',
        'closed_by',
        'closed_by_name',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'closed_at'       => 'datetime',
        'customer_unread' => 'integer',
        'admin_unread'    => 'integer',
    ];

    /**
     * Mirror the schema defaults on the model.
     *
     * Without these a just-created instance reports null for the counters —
     * the database default only lands on the row, not on the object in hand —
     * so any caller reading them before a refresh() sees null where it expects
     * an integer.
     */
    protected $attributes = [
        'status'          => self::STATUS_OPEN,
        'customer_unread' => 0,
        'admin_unread'    => 0,
    ];

    /* ------------------------------------------------------------ relations */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    /* --------------------------------------------------------------- scopes */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    /** Threads with something the admin side has not read yet. */
    public function scopeAwaitingReply(Builder $query): Builder
    {
        return $query->where('admin_unread', '>', 0);
    }

    /**
     * Inbox ordering: newest activity first, and threads that have never been
     * written to fall back to when they were created.
     */
    public function scopeRecentFirst(Builder $query): Builder
    {
        return $query->orderByRaw('COALESCE(last_message_at, created_at) DESC');
    }

    /* --------------------------------------------------------------- helpers */

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /** Label used by the audit trail. */
    public function activityLabel(): string
    {
        return 'Chat with ' . ($this->customer?->full_name ?? "customer #{$this->registration_id}");
    }
}
