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
        Schema::create('expense_claim_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_claim_id')->constrained('expense_claims');
            $table->string('category');
            $table->text('description');
            $table->decimal('amount', 18, 2);
            $table->string('receipt_path')->nullable();
            $table->date('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_claim_lines');
    }
};
