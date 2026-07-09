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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees');
            $table->date('attendance_date');
            $table->datetime('check_in')->nullable();
            $table->datetime('check_out')->nullable();
            $table->string('status')->default('draft'); // AttendanceStatus enum
            $table->integer('late_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('work_hours')->default(0); // in minutes
            $table->string('check_in_location')->nullable();
            $table->string('check_out_location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('shift_id')->nullable()->constrained('shifts');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['employee_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
