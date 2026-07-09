<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('bom_id')->nullable()->constrained('bill_of_materials')->nullOnDelete();
            $table->foreignId('routing_id')->nullable()->constrained('routings')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('source_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->enum('status', \App\Enums\ManufacturingOrderStatus::values())->default('draft')->index();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('produced_qty', 18, 4)->default(0);
            $table->decimal('scrap_qty', 18, 4)->default(0);
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->date('planned_date')->nullable();
            $table->date('planned_start')->nullable();
            $table->date('planned_finish')->nullable();
            $table->datetime('actual_start')->nullable();
            $table->datetime('actual_finish')->nullable();
            $table->decimal('material_cost', 18, 2)->default(0);
            $table->decimal('labor_cost', 18, 2)->default(0);
            $table->decimal('machine_cost', 18, 2)->default(0);
            $table->decimal('overhead_cost', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->text('production_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('origin')->nullable(); // source reference
            $table->softDeletes();
            $table->timestamps();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['company_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index('planned_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_orders');
    }
};


