<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_product_id')->constrained('ecommerce_products')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->integer('sequence')->default(0);
            $table->boolean('is_primary')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_product_images');
    }
};
