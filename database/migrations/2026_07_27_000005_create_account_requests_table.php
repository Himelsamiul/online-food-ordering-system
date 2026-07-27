<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Help requests a locked-out customer sends to the admin.
     *
     * Two kinds:
     *   password_reset — "I forgot my password, please issue a new one"
     *   activation     — "my account is switched off, please turn it back on"
     *
     * The customer may not be able to log in at all, so the form is public
     * and the row stores the typed email rather than relying on a session.
     * registration_id is resolved when the email matches an account, and is
     * nullable so a typo still lands in the admin's inbox instead of vanishing.
     */
    public function up(): void
    {
        Schema::create('account_requests', function (Blueprint $table) {
            $table->id();

            $table->string('type');                                  // password_reset | activation
            $table->unsignedBigInteger('registration_id')->nullable();

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();

            $table->string('status')->default('pending');            // pending | resolved | rejected
            $table->text('admin_note')->nullable();

            $table->unsignedBigInteger('handled_by')->nullable();    // users.id
            $table->string('handled_by_name')->nullable();           // snapshot
            $table->timestamp('handled_at')->nullable();

            $table->string('ip_address', 64)->nullable();
            $table->timestamp('read_at')->nullable();                // seen in the notification centre

            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index('registration_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_requests');
    }
};
