<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * total_amount stays the amount actually payable. subtotal keeps the
     * pre-discount figure so an old order can still be explained after the
     * coupon itself is edited or deleted — hence coupon_code is copied too.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->nullable()->after('address');
            $table->unsignedBigInteger('coupon_id')->nullable()->after('subtotal');
            $table->string('coupon_code')->nullable()->after('coupon_id');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'coupon_id', 'coupon_code', 'discount_amount']);
        });
    }
};
