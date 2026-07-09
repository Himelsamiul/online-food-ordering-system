<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();

            // dine_in / takeaway / delivery
            $table->string('order_type')->default('dine_in');
            $table->string('table_no')->nullable();      // dine-in only
            $table->string('customer_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();          // delivery only

            // money
            $table->decimal('subtotal', 10, 2)->default(0);

            $table->string('discount_type')->default('flat'); // flat / percent
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);

            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);

            $table->decimal('service_charge_rate', 5, 2)->default(0);
            $table->decimal('service_charge_amount', 10, 2)->default(0);

            $table->decimal('total', 10, 2)->default(0);

            // payment
            $table->string('payment_method')->default('cash'); // cash / card
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->string('payment_status')->default('paid'); // paid / pending

            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // admin user id

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_sales');
    }
};
