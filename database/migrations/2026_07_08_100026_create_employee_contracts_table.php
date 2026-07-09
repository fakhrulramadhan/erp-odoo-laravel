<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->foreignId('employee_id')->constrained('employees');
            $table->string('contract_type'); // full_time, part_time, contract, internship
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft'); // ContractStatus enum
            $table->decimal('salary', 18, 2)->default(0);
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
