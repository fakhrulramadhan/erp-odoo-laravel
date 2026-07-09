<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 50)->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index();

            // Dates
            $table->date('quotation_date');
            $table->date('validity_date')->nullable();
            $table->date('expected_date')->nullable();

            // Financial
            $table->decimal('exchange_rate', 15, 6)->default(1.0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('tax_total', 18, 2)->default(0);
            $table->decimal('discount_total', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);

            $table->string('payment_term', 100)->nullable();
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Vendor Response
            $table->decimal('vendor_price', 18, 2)->nullable();
            $table->integer('vendor_lead_days')->nullable();
            $table->text('vendor_notes')->nullable();

            // PO Reference (after accepted)
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status']);
            $table->index(['company_id', 'quotation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_quotations');
    }
};
