<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Delivery areas and what each one costs to reach.
 *
 * Until now the system charged nothing for delivery at all — a 50 taka order
 * ten kilometres away cost the business the same as one next door.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();

            $table->string('name');                                  // "Dhanmondi"
            $table->string('areas', 500)->nullable();                // helper text for the customer
            $table->decimal('charge', 10, 2)->default(0);

            // Below this subtotal the zone refuses the order — some areas are
            // not worth a rider's time for a single drink.
            $table->decimal('min_order', 10, 2)->nullable();

            // At or above this subtotal delivery is free.
            $table->decimal('free_above', 10, 2)->nullable();

            $table->unsignedSmallInteger('eta_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
