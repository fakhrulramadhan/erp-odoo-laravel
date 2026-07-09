<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('vacancy_number')->unique();
            $table->string('title');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('position_id')->nullable()->constrained('positions');
            $table->string('employment_type')->default('full_time');
            $table->integer('number_of_openings')->default(1);
            $table->decimal('salary_min', 18, 2)->nullable();
            $table->decimal('salary_max', 18, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->date('posting_date')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('branch_id')->nullable()->constrained('branches');

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};
