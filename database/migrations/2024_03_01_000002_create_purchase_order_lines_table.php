<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->foreignId('tax_id')->nullable()->constrained('tax_settings')->nullOnDelete();

            $table->integer('line_number')->default(1);
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('received_qty', 18, 4)->default(0);
            $table->decimal('price', 18, 4)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);

            $table->date('delivery_date')->nullable();
            $table->string('analytic_account', 100)->nullable();

            $table->timestamps();

            $table->index(['purchase_order_id', 'line_number']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_lines');
    }
};
