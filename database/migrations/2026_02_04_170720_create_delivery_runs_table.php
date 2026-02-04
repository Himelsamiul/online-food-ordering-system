<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_runs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('delivery_man_id');
            // FK intentionally NOT used

            // max 5 orders (JSON array of order ids)
            $table->json('order_ids');

            // food niye ber hoise ei time
            $table->dateTime('departed_at');

            // delivery shesh kore fire ashar time
            $table->dateTime('returned_at')->nullable();

            // on_the_way | completed
            $table->string('status')->default('on_the_way');

            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_runs');
    }
};
