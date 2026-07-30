<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The delivery charge an order actually paid.
 *
 * The zone name is snapshotted alongside the id for the same reason the audit
 * trail snapshots: renaming or deleting a zone next year must not rewrite what
 * a historical invoice says.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_zone_id')->nullable()->after('address');
            $table->string('delivery_zone_name')->nullable()->after('delivery_zone_id');
            $table->decimal('delivery_charge', 10, 2)->default(0)->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_zone_id', 'delivery_zone_name', 'delivery_charge']);
        });
    }
};
