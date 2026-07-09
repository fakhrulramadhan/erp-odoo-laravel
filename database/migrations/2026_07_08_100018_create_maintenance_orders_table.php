<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->string('maintenance_type'); // preventive, corrective
            $table->foreignId('equipment_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('draft'); // draft, requested, scheduled, in_progress, completed, cancelled
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->text('description')->nullable();
            $table->text('checklist')->nullable(); // JSON checklist
            $table->date('scheduled_date')->nullable();
            $table->date('due_date')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->integer('downtime_minutes')->default(0);
            $table->decimal('cost', 18, 2)->default(0);
            $table->decimal('parts_cost', 18, 2)->default(0);
            $table->decimal('labor_cost', 18, 2)->default(0);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->text('resolution')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['company_id', 'status']);
            $table->index(['equipment_id', 'maintenance_type']);
            $table->index('scheduled_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_orders');
    }
};


