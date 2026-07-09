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
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type'); // ActivityType enum
            $table->string('subject');
            $table->text('description')->nullable();
            $table->morphs('activityable'); // activityable_type, activityable_id — polymorphic to leads/opportunities
            $table->datetime('due_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->boolean('is_done')->default(false);
            $table->datetime('completed_at')->nullable();
            $table->text('result')->nullable();
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
        Schema::dropIfExists('crm_activities');
    }
};
