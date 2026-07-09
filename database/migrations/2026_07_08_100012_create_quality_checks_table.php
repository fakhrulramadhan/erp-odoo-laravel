<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('check_number')->unique();
            $table->string('inspection_type'); // incoming, production, outgoing
            $table->foreignId('quality_control_point_id')->nullable()->constrained('quality_control_points')->nullOnDelete();
            $table->morphs('source'); // manufacturing_order, stock_picking, etc.
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->enum('status', \App\Enums\QualityCheckStatus::values())->default('draft')->index();
            $table->string('result')->nullable(); // pass, fail
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('inspection_date')->nullable();
            $table->text('checklist_result')->nullable(); // JSON results
            $table->text('notes')->nullable();
            $table->text('defect_type')->nullable();
            $table->text('corrective_action')->nullable();
            $table->decimal('rejected_qty', 18, 4)->default(0);
            $table->foreignId('quarantine_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
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
        Schema::dropIfExists('quality_checks');
    }
};


