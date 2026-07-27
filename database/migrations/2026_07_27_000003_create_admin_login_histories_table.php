<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Admin sign-ins. Kept separate from login_histories, which tracks
     * customers (registration_id) and would otherwise need a polymorphic
     * column and a rewrite of every existing query against it.
     */
    public function up(): void
    {
        Schema::create('admin_login_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();   // snapshot, survives deletion
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();

            $table->string('ip_address', 64)->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('user_agent')->nullable();

            $table->boolean('successful')->default(true);  // failed attempts too

            $table->timestamp('logged_in_at')->nullable();
            $table->timestamp('logged_out_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('logged_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_histories');
    }
};
