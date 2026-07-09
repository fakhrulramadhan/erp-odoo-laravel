<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_structure_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->constrained('salary_structures');
            $table->foreignId('payroll_component_id')->constrained('payroll_components');
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('percentage', 8, 2)->nullable();
            $table->string('calculation_type')->default('fixed');
            $table->integer('sequence')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_structure_lines');
    }
};
