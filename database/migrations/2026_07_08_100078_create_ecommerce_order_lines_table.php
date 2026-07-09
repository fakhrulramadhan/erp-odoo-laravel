<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_order_id')->constrained('ecommerce_orders')->cascadeOnDelete();
            $table->foreignId('ecommerce_product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('ecommerce_product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 18, 4);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total', 18, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_order_lines');
    }
};
