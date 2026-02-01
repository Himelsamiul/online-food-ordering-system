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
Schema::create('login_histories', function (Blueprint $table) {
    $table->id();

    // 🔑 just normal column, NO FK
    $table->unsignedBigInteger('registration_id');

    $table->string('ip_address')->nullable();
    $table->string('country')->nullable();
    $table->string('city')->nullable();
    $table->text('user_agent')->nullable();

    $table->timestamp('logged_in_at');
    $table->timestamp('logged_out_at')->nullable();

    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_histories');
    }
};
