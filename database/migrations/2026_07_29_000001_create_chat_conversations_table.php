<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One live-support thread per customer.
 *
 * The unique key on registration_id is the whole design decision: a customer
 * never accumulates threads, they have one conversation that gets closed and
 * reopened. That keeps the admin inbox a flat list and makes "find my thread"
 * a single indexed lookup on every poll.
 *
 * Unread counters are denormalised on purpose. The customer widget polls every
 * few seconds and the admin inbox lists every thread; counting messages each
 * time would be a table scan per poll per user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();

            $table->string('status', 20)->default('open');          // open | closed

            // Whoever replied last owns it in the inbox header; not an assignment
            // queue, just a "who has been handling this" hint.
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->string('assigned_admin_name')->nullable();

            // NULLABLE deliberately — a non-nullable TIMESTAMP silently picks up
            // ON UPDATE CURRENT_TIMESTAMP on this MariaDB and would be rewritten
            // every time an unread counter moved.
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_message_preview', 160)->nullable();
            $table->string('last_message_from', 20)->nullable();     // customer | admin

            $table->unsignedInteger('customer_unread')->default(0);
            $table->unsignedInteger('admin_unread')->default(0);

            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->string('closed_by_name')->nullable();

            $table->timestamps();

            $table->unique('registration_id');
            $table->index(['status', 'last_message_at']);
            $table->index('admin_unread');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
