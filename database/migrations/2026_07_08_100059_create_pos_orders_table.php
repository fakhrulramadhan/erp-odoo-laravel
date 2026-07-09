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
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('pos_session_id')->constrained('pos_sessions');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('paid', 18, 2)->default(0);
            $table->decimal('change_amount', 18, 2)->default(0);
            $table->string('status')->default('draft');
            $table->foreignId('sales_order_id')->nullable();
            $table->foreignId('invoice_id')->nullable();
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
        Schema::dropIfExists('pos_orders');
    }
};
