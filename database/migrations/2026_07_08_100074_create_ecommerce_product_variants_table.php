<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->string('name');
            $table->json('attributes')->nullable();
            $table->decimal('price', 18, 2);
            $table->decimal('compare_price', 18, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sequence')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_variants');
    }
};
