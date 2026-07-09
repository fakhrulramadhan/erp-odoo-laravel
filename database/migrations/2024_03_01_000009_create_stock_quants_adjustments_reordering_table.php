<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stock Quant — current on-hand quantity per product per location
        Schema::create('stock_quants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('stock_locations')->cascadeOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();

            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('reserved_quantity', 18, 4)->default(0);
            $table->decimal('available_quantity', 18, 4)->default(0); // quantity - reserved
            $table->decimal('incoming_quantity', 18, 4)->default(0);
            $table->decimal('outgoing_quantity', 18, 4)->default(0);

            // Costing
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->decimal('total_value', 18, 2)->default(0);

            $table->timestamp('last_updated_at')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'location_id', 'lot_id']);
            $table->index(['location_id', 'product_id']);
        });

        // Inventory Adjustments
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index(); // draft, in_progress, validated, cancelled

            $table->date('adjustment_date');
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();

            // Approval
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_adjustment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_adjustment_id')->constrained('inventory_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('stock_locations')->restrictOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();

            $table->decimal('theoretical_qty', 18, 4)->default(0); // expected
            $table->decimal('actual_qty', 18, 4)->default(0); // counted
            $table->decimal('difference', 18, 4)->default(0); // actual - theoretical

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['inventory_adjustment_id', 'product_id']);
        });

        // Reordering Rules
        Schema::create('reordering_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();

            $table->string('name', 100)->nullable();
            $table->decimal('min_qty', 18, 4)->default(0);
            $table->decimal('max_qty', 18, 4)->default(0);
            $table->decimal('qty_multiple', 18, 4)->default(1);
            $table->integer('lead_days')->default(0);
            $table->integer('safety_stock_days')->default(0);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id'], 'reordering_rule_product_warehouse_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reordering_rules');
        Schema::dropIfExists('inventory_adjustment_lines');
        Schema::dropIfExists('inventory_adjustments');
        Schema::dropIfExists('stock_quants');
    }
};
