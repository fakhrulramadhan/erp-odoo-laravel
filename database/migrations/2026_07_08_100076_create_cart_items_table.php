<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_cart_id')->constrained('shopping_carts')->cascadeOnDelete();
            $table->foreignId('ecommerce_product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('ecommerce_product_variants')->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('total', 18, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
