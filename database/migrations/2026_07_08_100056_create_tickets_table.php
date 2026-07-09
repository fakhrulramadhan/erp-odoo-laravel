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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories');
            $table->foreignId('sla_id')->nullable()->constrained('slas');
            $table->string('subject');
            $table->text('description');
            $table->string('status')->default('new');
            $table->string('priority')->default('medium');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('assigned_team_id')->nullable();
            $table->datetime('first_response_at')->nullable();
            $table->datetime('resolved_at')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
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
        Schema::dropIfExists('tickets');
    }
};
