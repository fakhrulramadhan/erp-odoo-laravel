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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('source')->default('other'); // LeadSource enum
            $table->string('status')->default('new'); // LeadStatus enum
            $table->integer('score')->default(0);
            $table->decimal('expected_revenue', 18, 2)->nullable();
            $table->date('expected_close_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
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
        Schema::dropIfExists('leads');
    }
};
