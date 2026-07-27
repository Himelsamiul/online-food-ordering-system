<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per issued OTP, for every guard and every purpose.
     *
     * Replaces the ad-hoc use of password_reset_tokens (which is keyed on email
     * alone and so could not tell an admin apart from a customer with the same
     * address) and the session-stored registration code.
     *
     * Single use is enforced by consumed_at, not by deletion, so a replayed code
     * can be distinguished from an unknown one in the audit trail.
     */
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            $table->string('guard', 32);            // web (admin) | frontend (customer)
            $table->string('purpose', 32);          // register | password_reset
            $table->string('identifier');           // the email the code was sent to

            $table->string('code_hash');            // never the plain code
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();

            $table->string('ip_address', 64)->nullable();

            $table->timestamps();

            $table->index(['guard', 'purpose', 'identifier']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
