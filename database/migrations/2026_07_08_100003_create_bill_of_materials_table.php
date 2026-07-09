<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('bom_number')->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->integer('version')->default(1);
            $table->string('revision')->nullable();
            $table->string('bom_type')->default('manufacture'); // manufacture, kit
            $table->enum('status', \App\Enums\BomStatus::values())->default('draft')->index();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->decimal('scrap_percentage', 8, 2)->default(0);
            $table->string('routing_id')->nullable();
            $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['company_id', 'product_id', 'status']);
            $table->index(['product_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
    }
};


