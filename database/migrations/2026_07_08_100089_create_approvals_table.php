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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approval_number')->unique();
            $table->string('approvalable_type');
            $table->unsignedBigInteger('approvalable_id');
            $table->foreignId('workflow_id')->nullable()->constrained('workflows');
            $table->foreignId('workflow_step_id')->nullable()->constrained('workflow_steps');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->datetime('requested_at');
            $table->datetime('responded_at')->nullable();
            $table->integer('level')->default(1);
            $table->foreignId('company_id')->nullable()->constrained('companies');

            $table->index(['approvalable_type', 'approvalable_id']);

            // Standard audit
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
        Schema::dropIfExists('approvals');
    }
};
