<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();

            $table->decimal('quality_rating', 3, 1)->nullable(); // 1.0 - 5.0
            $table->decimal('delivery_rating', 3, 1)->nullable();
            $table->decimal('price_rating', 3, 1)->nullable();
            $table->decimal('service_rating', 3, 1)->nullable();
            $table->decimal('overall_rating', 3, 1)->nullable();
            $table->text('notes')->nullable();
            $table->date('rating_date');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vendor_id', 'rating_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_ratings');
    }
};
