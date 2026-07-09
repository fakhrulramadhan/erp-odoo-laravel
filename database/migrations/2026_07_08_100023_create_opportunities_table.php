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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('opportunity_number')->unique();
            $table->foreignId('lead_id')->nullable()->constrained('leads');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('name');
            $table->string('stage')->default('new'); // OpportunityStage enum
            $table->integer('probability')->default(0);
            $table->decimal('expected_revenue', 18, 2)->nullable();
            $table->decimal('actual_revenue', 18, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('opportunities');
    }
};
