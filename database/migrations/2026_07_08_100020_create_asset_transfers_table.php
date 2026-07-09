<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('from_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('transfer_date');
            $table->text('reason')->nullable();
            $table->string('status')->default('draft'); // draft, approved, done, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};


