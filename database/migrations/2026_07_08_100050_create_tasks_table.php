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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_number')->unique();
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('parent_id')->nullable()->constrained('tasks');
            $table->foreignId('milestone_id')->nullable()->constrained('milestones');
            $table->foreignId('sprint_id')->nullable()->constrained('sprints');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('backlog');
            $table->string('priority')->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('employees');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->default(0);
            $table->decimal('progress', 5, 2)->default(0);
            $table->integer('sequence')->default(0);
            $table->string('task_type')->nullable();
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
        Schema::dropIfExists('tasks');
    }
};
