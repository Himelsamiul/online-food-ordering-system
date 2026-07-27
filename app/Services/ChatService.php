<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Registration;
use App\Models\User;
use App\Services\CustomerNotifier;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Everything that writes to a support thread goes through here.
 *
 * The unread counters are denormalised, so every write that touches them runs
 * inside a transaction with the conversation row locked. Two admins replying
 * at the same instant, or a customer sending while an admin marks the thread
 * read, would otherwise lose an increment and leave a badge stuck on a number
 * that no longer matches the messages.
 */
class ChatService
{
    public const MODULE = 'Support Chat';

    /**
     * The customer's one thread, created on first use.
     *
     * firstOrCreate races on a genuinely concurrent first message, so the
     * unique index on registration_id is the real guard — we catch the
     * duplicate and re-read rather than letting a 500 escape.
     */
    public function threadFor(Registration $customer): ChatConversation
    {
        $existing = ChatConversation::where('registration_id', $customer->id)->first();

        if ($existing) {
            return $existing;
        }

        try {
            $conversation = ChatConversation::create([
                'registration_id' => $customer->id,
                'status'          => ChatConversation::STATUS_OPEN,
            ]);

            AuditLogger::system(
                AuditLogger::ACTION_CREATED,
                self::MODULE,
                "{$customer->full_name} started a support conversation",
                $conversation,
            );

            return $conversation;
        } catch (\Illuminate\Database\QueryException) {
            // Lost the race — the other request created it a moment ago.
            return ChatConversation::where('registration_id', $customer->id)->firstOrFail();
        }
    }

    /* ---------------------------------------------------------------- posting */

    /**
     * Customer sends a line. A closed thread reopens: a customer writing in is
     * by definition a new problem, and making them find a "reopen" button
     * before they can type would be hostile.
     */
    public function postFromCustomer(ChatConversation $conversation, Registration $customer, string $body, ?string $ip = null): ChatMessage
    {
        return DB::transaction(function () use ($conversation, $customer, $body, $ip) {
            $locked   = ChatConversation::whereKey($conversation->getKey())->lockForUpdate()->firstOrFail();
            $reopened = $locked->isClosed();

            $message = $this->write($locked, ChatMessage::FROM_CUSTOMER, $customer->id, $customer->full_name, $body, $ip);

            $locked->forceFill([
                'status'               => ChatConversation::STATUS_OPEN,
                'closed_at'            => null,
                'closed_by'            => null,
                'closed_by_name'       => null,
                'admin_unread'         => $locked->admin_unread + 1,
                'last_message_at'      => $message->created_at,
                'last_message_preview' => $this->preview($body),
                'last_message_from'    => ChatMessage::FROM_CUSTOMER,
            ])->save();

            if ($reopened) {
                AuditLogger::system(
                    AuditLogger::ACTION_STATUS_CHANGED,
                    self::MODULE,
                    "{$customer->full_name} reopened their support conversation",
                    $locked,
                    ['status' => ChatConversation::STATUS_CLOSED],
                    ['status' => ChatConversation::STATUS_OPEN],
                );
            }

            return $message;
        });
    }

    /** Admin replies. Also stamps who is handling the thread. */
    public function postFromAdmin(ChatConversation $conversation, User $admin, string $body, ?string $ip = null): ChatMessage
    {
        return DB::transaction(function () use ($conversation, $admin, $body, $ip) {
            $locked = ChatConversation::whereKey($conversation->getKey())->lockForUpdate()->firstOrFail();

            $message = $this->write($locked, ChatMessage::FROM_ADMIN, $admin->id, $admin->name, $body, $ip);

            $locked->forceFill([
                'status'               => ChatConversation::STATUS_OPEN,
                'closed_at'            => null,
                'closed_by'            => null,
                'closed_by_name'       => null,
                'assigned_admin_id'    => $admin->id,
                'assigned_admin_name'  => $admin->name,
                'customer_unread'      => $locked->customer_unread + 1,
                // The admin is looking at the thread as they type, so anything
                // that arrived before this reply has necessarily been read.
                'admin_unread'         => 0,
                'last_message_at'      => $message->created_at,
                'last_message_preview' => $this->preview($body),
                'last_message_from'    => ChatMessage::FROM_ADMIN,
            ])->save();

            // Ring the customer's bell too — they may well have the chat panel
            // shut, or be on another page entirely.
            app(CustomerNotifier::class)->chatReply($locked, $admin->name, $this->preview($body));

            return $message;
        });
    }

    /** A note neither side typed — "conversation closed by support", etc. */
    public function postSystemNote(ChatConversation $conversation, string $body): ChatMessage
    {
        return DB::transaction(function () use ($conversation, $body) {
            $locked  = ChatConversation::whereKey($conversation->getKey())->lockForUpdate()->firstOrFail();
            $message = $this->write($locked, ChatMessage::FROM_SYSTEM, null, null, $body, null);

            $locked->forceFill([
                'customer_unread'      => $locked->customer_unread + 1,
                'last_message_at'      => $message->created_at,
                'last_message_preview' => $this->preview($body),
                'last_message_from'    => ChatMessage::FROM_SYSTEM,
            ])->save();

            return $message;
        });
    }

    /* ----------------------------------------------------------------- status */

    public function close(ChatConversation $conversation, User $admin): void
    {
        if ($conversation->isClosed()) {
            return;
        }

        DB::transaction(function () use ($conversation, $admin) {
            $conversation->forceFill([
                'status'         => ChatConversation::STATUS_CLOSED,
                'closed_at'      => now(),
                'closed_by'      => $admin->id,
                'closed_by_name' => $admin->name,
                'admin_unread'   => 0,
            ])->save();

            $this->postSystemNote($conversation, 'This conversation was marked resolved by support. Send a message any time to reopen it.');
        });

        AuditLogger::system(
            AuditLogger::ACTION_STATUS_CHANGED,
            self::MODULE,
            "Closed the support conversation with {$conversation->customer?->full_name}",
            $conversation,
            ['status' => ChatConversation::STATUS_OPEN],
            ['status' => ChatConversation::STATUS_CLOSED],
        );
    }

    public function reopen(ChatConversation $conversation, User $admin): void
    {
        if ($conversation->isOpen()) {
            return;
        }

        $conversation->forceFill([
            'status'              => ChatConversation::STATUS_OPEN,
            'closed_at'           => null,
            'closed_by'           => null,
            'closed_by_name'      => null,
            // Whoever reopens it is now handling it, so the inbox header shows
            // the right name instead of the admin who closed it days ago.
            'assigned_admin_id'   => $admin->id,
            'assigned_admin_name' => $admin->name,
        ])->save();

        AuditLogger::system(
            AuditLogger::ACTION_STATUS_CHANGED,
            self::MODULE,
            "Reopened the support conversation with {$conversation->customer?->full_name}",
            $conversation,
            ['status' => ChatConversation::STATUS_CLOSED],
            ['status' => ChatConversation::STATUS_OPEN],
        );
    }

    /** The acting admin is not a parameter — AuditLogger reads it from the session. */
    public function delete(ChatConversation $conversation): void
    {
        // Read the label and count BEFORE the delete — afterwards the customer
        // relation and the message rows are gone and the trail would say "#12".
        $label = $conversation->activityLabel();
        $count = $conversation->messages()->count();

        $conversation->delete();   // messages cascade

        AuditLogger::log(
            AuditLogger::ACTION_DELETED,
            "Deleted a support conversation and its {$count} message(s)",
            null,
            null,
            null,
            self::MODULE,
            $label,
        );
    }

    /* ------------------------------------------------------------------- read */

    /** Customer opened the widget: everything the admin sent is now read. */
    public function markReadForCustomer(ChatConversation $conversation): void
    {
        if ($conversation->customer_unread === 0) {
            return;
        }

        $conversation->messages()
            ->whereIn('sender_type', [ChatMessage::FROM_ADMIN, ChatMessage::FROM_SYSTEM])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->forceFill(['customer_unread' => 0])->save();
    }

    /** Admin opened the thread: everything the customer sent is now read. */
    public function markReadForAdmin(ChatConversation $conversation): void
    {
        if ($conversation->admin_unread === 0) {
            return;
        }

        $conversation->messages()
            ->where('sender_type', ChatMessage::FROM_CUSTOMER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->forceFill(['admin_unread' => 0])->save();
    }

    /* ---------------------------------------------------------------- internal */

    private function write(ChatConversation $conversation, string $type, ?int $senderId, ?string $senderName, string $body, ?string $ip): ChatMessage
    {
        return $conversation->messages()->create([
            'sender_type' => $type,
            'sender_id'   => $senderId,
            'sender_name' => $senderName,
            'body'        => $body,
            'ip_address'  => $ip,
        ]);
    }

    private function preview(string $body): string
    {
        return Str::limit(preg_replace('/\s+/u', ' ', trim($body)), 150);
    }
}
