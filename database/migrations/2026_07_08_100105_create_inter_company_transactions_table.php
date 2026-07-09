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
        Schema::create('inter_company_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('from_company_id')->constrained('companies');
            $table->foreignId('to_company_id')->constrained('companies');
            $table->string('transaction_type');
            $table->decimal('amount', 18, 2);
            $table->foreignId('currency_id')->nullable()->constrained('currencies');
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->date('transaction_date');
            $table->foreignId('source_id')->nullable();
            $table->string('source_type')->nullable();

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
        Schema::dropIfExists('inter_company_transactions');
    }
};
