<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Food ratings, tied to a delivered order.
 *
 * The (order_id, food_id) unique key is the anti-spam rule made structural:
 * a customer can review each food once per order they actually received, so
 * nobody can stack ten five-star reviews on the same purchase, and a genuine
 * repeat buyer can still leave a fresh opinion next time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');              // 1..5
            $table->string('title')->nullable();
            $table->text('comment')->nullable();

            // Reviews go live immediately but can be pulled by an admin.
            $table->string('status', 20)->default('approved');  // approved | hidden

            $table->string('customer_name');                    // snapshot
            $table->text('admin_reply')->nullable();
            $table->string('admin_reply_by')->nullable();

            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'food_id']);
            $table->index(['food_id', 'status']);
            $table->index(['registration_id', 'status']);
        });

        // Denormalised so a menu listing 40 items does not run 40 AVG queries.
        Schema::table('foods', function (Blueprint $table) {
            $table->decimal('rating_avg', 3, 2)->default(0)->after('is_popular');
            $table->unsignedInteger('rating_count')->default(0)->after('rating_avg');
        });
    }

    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn(['rating_avg', 'rating_count']);
        });

        Schema::dropIfExists('reviews');
    }
};
