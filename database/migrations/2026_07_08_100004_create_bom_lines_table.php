<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('bill_of_materials')->cascadeOnDelete();
            $table->integer('line_number')->default(1);
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measures')->nullOnDelete();
            $table->decimal('quantity', 18, 4)->default(1);
            $table->string('quantity_formula')->nullable(); // e.g. "2 * parent_qty + 1"
            $table->decimal('scrap_percentage', 8, 2)->default(0);
            $table->boolean('is_alternative')->default(false);
            $table->foreignId('alternative_for_id')->nullable()->constrained('bom_lines')->nullOnDelete();
            $table->decimal('cost_per_unit', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['bom_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_lines');
    }
};
