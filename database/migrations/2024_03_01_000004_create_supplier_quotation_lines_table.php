<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_quotation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_quotation_id')->constrained('supplier_quotations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();

            $table->integer('line_number')->default(1);
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('price', 18, 4)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);

            // Vendor Response
            $table->decimal('vendor_price', 18, 4)->nullable();
            $table->decimal('vendor_qty', 18, 4)->nullable();
            $table->integer('vendor_lead_days')->nullable();

            $table->timestamps();

            $table->index(['supplier_quotation_id', 'line_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_quotation_lines');
    }
};
