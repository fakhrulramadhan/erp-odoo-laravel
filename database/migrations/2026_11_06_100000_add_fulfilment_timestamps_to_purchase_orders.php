<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('purchase_orders', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('sent_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['sent_at', 'cancelled_at']);
        });
    }
};
