<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riders become accounts, not just records.
 *
 * Until now a delivery man had no password and no way in, so every status
 * change had to be typed by an admin sitting at a desk — the rider who
 * actually knows the food arrived could not say so.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('password')->nullable()->after('email');

            // NULLABLE deliberately — a non-nullable TIMESTAMP silently picks
            // up ON UPDATE CURRENT_TIMESTAMP on this MariaDB.
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_men', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'password', 'last_login_at', 'last_login_ip', 'remember_token',
            ]);
        });
    }
};
