<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lots / Serial Numbers
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number', 100);
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->enum('tracking_type', ['lot', 'serial'])->default('lot');
            $table->date('expiration_date')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['lot_number', 'product_id']);
            $table->index('expiration_date');
        });

        // Stock Pickings
        Schema::create('stock_pickings', function (Blueprint $table) {
            $table->id();
            $table->string('picking_number', 50)->unique();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('picking_type', 30)->index(); // incoming, outgoing, internal, dropship
            $table->string('status', 30)->default('draft')->index();
            $table->string('origin', 100)->nullable(); // 'purchase:5', 'sale:3', 'internal:2'

            // Locations
            $table->foreignId('source_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();

            // Partners
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            // Dates
            $table->date('scheduled_date');
            $table->date('effective_date')->nullable();

            // Related PO
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['picking_type', 'status']);
            $table->index('scheduled_date');
        });

        // Stock Moves
        Schema::create('stock_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_picking_id')->nullable()->constrained('stock_pickings')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();

            $table->string('move_number', 50)->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->string('origin', 100)->nullable();

            // Locations
            $table->foreignId('source_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();

            // Quantities
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('reserved_qty', 18, 4)->default(0);
            $table->decimal('done_qty', 18, 4)->default(0);

            // Costing
            $table->decimal('unit_cost', 18, 4)->nullable();
            $table->decimal('total_cost', 18, 2)->nullable();

            // Dates
            $table->date('scheduled_date')->nullable();
            $table->date('effective_date')->nullable();

            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['source_location_id', 'destination_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_moves');
        Schema::dropIfExists('stock_pickings');
        Schema::dropIfExists('lots');
    }
};
