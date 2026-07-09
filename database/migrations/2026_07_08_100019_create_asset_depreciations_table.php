<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->date('depreciation_date');
            $table->integer('period_number')->default(1);
            $table->decimal('depreciation_amount', 18, 2)->default(0);
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->decimal('book_value', 18, 2)->default(0);
            $table->string('status')->default('draft'); // draft, posted
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['asset_id', 'depreciation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};


