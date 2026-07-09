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
        Schema::create('workflow_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows');
            $table->foreignId('workflow_step_id')->nullable()->constrained('workflow_steps');
            $table->string('loggable_type');
            $table->unsignedBigInteger('loggable_id');
            $table->string('action');
            $table->string('status');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['loggable_type', 'loggable_id']);
            // No soft deletes for logs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_logs');
    }
};
