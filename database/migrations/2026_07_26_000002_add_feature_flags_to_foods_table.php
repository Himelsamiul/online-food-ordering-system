<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Both flags are admin-controlled on purpose — the shop is new and
     * real order counts are too thin to rank "popular" automatically.
     */
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('is_popular')->default(false)->after('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'is_popular']);
        });
    }
};
