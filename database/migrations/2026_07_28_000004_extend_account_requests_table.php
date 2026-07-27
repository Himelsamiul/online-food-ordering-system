<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The request inbox now serves admins as well as customers.
     *
     * One table with a requester_type discriminator rather than two parallel
     * ones: the lifecycle (pending → approved/rejected, handled_by, audit,
     * export) is identical, and the superadmin dashboards are just filtered
     * views over it.
     */
    public function up(): void
    {
        Schema::table('account_requests', function (Blueprint $table) {
            $table->string('requester_type', 16)->default('customer')->after('type'); // customer | admin
            $table->unsignedBigInteger('user_id')->nullable()->after('registration_id'); // users.id for admins

            $table->string('username')->nullable()->after('name');
            $table->string('requested_role')->nullable()->after('phone');
            $table->text('reason')->nullable()->after('requested_role');

            $table->string('user_agent', 512)->nullable()->after('ip_address');

            $table->index(['requester_type', 'status']);
            $table->index('user_id');
        });

        // Existing rows all came from the storefront.
        DB::table('account_requests')->update(['requester_type' => 'customer']);

        // `message` was the free-text field; it is now explicitly the optional
        // "additional notes" slot, with `reason` carrying the required one.
        DB::statement('UPDATE account_requests SET reason = message WHERE reason IS NULL AND message IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('account_requests', function (Blueprint $table) {
            $table->dropIndex(['requester_type', 'status']);
            $table->dropIndex(['user_id']);
            $table->dropColumn([
                'requester_type', 'user_id', 'username',
                'requested_role', 'reason', 'user_agent',
            ]);
        });
    }
};
