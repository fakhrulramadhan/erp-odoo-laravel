<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_order_id')->constrained()->cascadeOnDelete();
            $table->integer('line_number')->default(1);
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('reserved_qty', 18, 4)->default(0);
            $table->decimal('consumed_qty', 18, 4)->default(0);
            $table->decimal('scrap_qty', 18, 4)->default(0);
            $table->string('line_type')->default('component'); // component, byproduct
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->foreignId('source_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['manufacturing_order_id', 'product_id'], 'mol_mo_id_prod_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_lines');
    }
};
