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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->datetime('opened_at');
            $table->datetime('closed_at')->nullable();
            $table->decimal('opening_cash', 18, 2)->default(0);
            $table->decimal('closing_cash', 18, 2)->nullable();
            $table->decimal('total_sales', 18, 2)->default(0);
            $table->decimal('total_cash', 18, 2)->default(0);
            $table->decimal('total_non_cash', 18, 2)->default(0);
            $table->decimal('cash_difference', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('pos_sessions');
    }
};
