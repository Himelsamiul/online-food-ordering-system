<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-use password reset links.
     *
     * A plain-text password must never travel by email, so approving a password
     * assistance request mints one of these instead: a random token stored only
     * as a hash, delivered inside a signed URL that expires. The recipient picks
     * their own password at the other end, so nothing reusable ever sits in an
     * inbox.
     *
     * `guard` + `account_id` rather than a foreign key, because admins live in
     * `users` and customers in `registrations`.
     */
    public function up(): void
    {
        Schema::create('password_reset_links', function (Blueprint $table) {
            $table->id();

            $table->string('guard', 32);                 // web (admin) | frontend (customer)
            $table->unsignedBigInteger('account_id');
            $table->string('email');

            $table->string('token_hash');

            $table->unsignedBigInteger('account_request_id')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();   // users.id of the approver
            $table->string('issued_by_name')->nullable();          // snapshot

            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('used_ip', 64)->nullable();

            $table->timestamps();

            $table->index(['guard', 'account_id']);
            $table->index('email');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_links');
    }
};
