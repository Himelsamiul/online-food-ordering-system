<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Individual chat lines.
 *
 * sender_id is NOT a foreign key: admins live in `users` and customers in
 * `registrations`, so the column points at whichever table sender_type names.
 * sender_name is a snapshot for the same reason the audit trail snapshots —
 * a transcript must still read correctly after the account is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('chat_conversation_id')
                ->constrained('chat_conversations')
                ->cascadeOnDelete();

            $table->string('sender_type', 20);                  // customer | admin | system
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('sender_name')->nullable();

            $table->text('body');

            $table->timestamp('read_at')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            // Every poll is "give me rows in this thread with id > N".
            $table->index(['chat_conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
