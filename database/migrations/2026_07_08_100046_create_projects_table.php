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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('manager_id')->nullable()->constrained('employees');
            $table->string('status')->default('draft');
            $table->string('priority')->default('medium');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('budget', 18, 2)->default(0);
            $table->decimal('actual_cost', 18, 2)->default(0);
            $table->decimal('billing_rate', 18, 2)->nullable();
            $table->string('billing_type')->nullable();
            $table->integer('progress')->default(0);
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
        Schema::dropIfExists('projects');
    }
};
