<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->string('payslip_number')->unique();
            $table->foreignId('payroll_id')->constrained('payrolls');
            $table->foreignId('employee_id')->constrained('employees');
            $table->decimal('basic_salary', 18, 2)->default(0);
            $table->decimal('total_allowances', 18, 2)->default(0);
            $table->decimal('total_deductions', 18, 2)->default(0);
            $table->decimal('total_overtime', 18, 2)->default(0);
            $table->decimal('total_bonus', 18, 2)->default(0);
            $table->decimal('total_tax', 18, 2)->default(0);
            $table->decimal('total_insurance', 18, 2)->default(0);
            $table->decimal('gross_salary', 18, 2)->default(0);
            $table->decimal('net_salary', 18, 2)->default(0);
            $table->integer('work_days')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('overtime_hours')->default(0);
            $table->integer('unpaid_leave_days')->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
