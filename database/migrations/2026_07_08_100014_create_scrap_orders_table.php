<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrap_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('scrap_number')->unique();
            $table->string('scrap_type'); // raw_material, production, finished_goods, return
            $table->morphs('source'); // manufacturing_order, stock_picking, etc.
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->string('status')->default('draft'); // draft, confirmed, done, cancelled
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['company_id', 'status']);
            // morphs() already creates index on source_type + source_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrap_orders');
    }
};


