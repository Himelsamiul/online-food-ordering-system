<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delivery_men', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 11)->unique(); // BD number
            $table->text('address');

            // Smart NID = 9 digit, Old NID = 13 digit
            $table->string('nid_number', 13)->unique();

            $table->string('photo'); // image path
            $table->text('note')->nullable();

            // status: 1 = active, 0 = inactive
            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_men');
    }
};
