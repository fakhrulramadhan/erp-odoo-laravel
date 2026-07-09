<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_order_id')->constrained()->cascadeOnDelete();
            $table->string('work_order_number')->unique();
            $table->foreignId('work_center_id')->constrained()->restrictOnDelete();
            $table->integer('sequence')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, done, cancelled
            $table->integer('duration_minutes')->default(0);
            $table->integer('actual_duration_minutes')->default(0);
            $table->datetime('started_at')->nullable();
            $table->datetime('finished_at')->nullable();
            $table->decimal('cost', 18, 2)->default(0);
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['manufacturing_order_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};


