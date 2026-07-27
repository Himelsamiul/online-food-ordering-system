<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The storefront notification bell.
 *
 * A real table rather than a feed derived from current state, because the
 * events are the point: once an order reaches "delivered", a derived feed can
 * no longer show that it was confirmed and picked up along the way. Each
 * transition is written once, when it happens, and stays.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();

            $table->string('type', 40);                 // order | chat | account | promo
            $table->string('title');
            $table->string('body', 400)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('icon', 60)->nullable();
            $table->string('tone', 20)->nullable();     // success | info | warning | danger

            // What the row is about, so a duplicate can be recognised.
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // Nullable: a non-nullable TIMESTAMP silently picks up
            // ON UPDATE CURRENT_TIMESTAMP on this MariaDB.
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // The bell's two queries: unread count, and the newest N.
            $table->index(['registration_id', 'read_at']);
            $table->index(['registration_id', 'id']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notifications');
    }
};
