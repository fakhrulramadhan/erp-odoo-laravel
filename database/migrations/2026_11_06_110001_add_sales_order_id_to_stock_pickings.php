<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stock_pickings', 'sales_order_id')) {
            Schema::table('stock_pickings', function (Blueprint $table) {
                $table->foreignId('sales_order_id')
                    ->nullable()
                    ->after('purchase_order_id')
                    ->constrained('sales_orders')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('stock_pickings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sales_order_id');
        });
    }
};
