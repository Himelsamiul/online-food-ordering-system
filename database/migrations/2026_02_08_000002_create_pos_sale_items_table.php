<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_sale_id')
                  ->constrained('pos_sales')
                  ->cascadeOnDelete();
            $table->unsignedBigInteger('food_id');
            $table->string('name');                 // snapshot (food may be renamed/deleted later)
            $table->decimal('price', 10, 2);        // unit price charged (after food discount)
            $table->integer('quantity');
            $table->decimal('total', 10, 2);        // price * quantity
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sale_items');
    }
};
