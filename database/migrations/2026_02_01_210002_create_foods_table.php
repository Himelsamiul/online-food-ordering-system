<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('sku')->unique();

            $table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();

            $table->integer('quantity');
            $table->integer('low_stock_alert')->nullable();

            $table->string('barcode')->nullable();
            $table->string('image')->nullable();

            $table->text('description')->nullable();
            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
