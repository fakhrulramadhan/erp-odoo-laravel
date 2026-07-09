<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('applicant_number')->unique();
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('stage')->default('new');
            $table->integer('score')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->foreignId('company_id')->nullable()->constrained('companies');

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
