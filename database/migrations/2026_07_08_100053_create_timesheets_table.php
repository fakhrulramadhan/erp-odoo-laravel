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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->string('timesheet_number')->unique();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('task_id')->nullable()->constrained('tasks');
            $table->date('work_date');
            $table->decimal('hours', 8, 2);
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->string('status')->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users');
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
        Schema::dropIfExists('timesheets');
    }
};
