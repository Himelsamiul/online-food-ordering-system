<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Customers can now be switched off by an admin. An inactive customer
     * keeps their row (and their order history) but cannot sign in — the
     * login screen sends them to the reactivation request form instead.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('address');
            $table->timestamp('deactivated_at')->nullable()->after('is_active');
            $table->string('deactivation_reason')->nullable()->after('deactivated_at');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'deactivated_at', 'deactivation_reason']);
        });
    }
};
