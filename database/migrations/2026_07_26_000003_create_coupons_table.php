<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();          // always stored uppercase
            $table->string('description')->nullable();

            $table->enum('type', ['percent', 'fixed']); // 20% off  /  ৳100 off
            $table->decimal('value', 10, 2);

            $table->decimal('min_order_amount', 10, 2)->nullable(); // cart must reach this
            $table->decimal('max_discount', 10, 2)->nullable();     // caps a percent coupon

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();

            $table->unsignedInteger('usage_limit')->nullable();    // total redemptions allowed
            $table->unsignedInteger('per_user_limit')->nullable(); // per customer
            $table->unsignedInteger('used_count')->default(0);

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
