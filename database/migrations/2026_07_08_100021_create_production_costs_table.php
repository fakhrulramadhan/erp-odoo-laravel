<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_order_id')->constrained()->cascadeOnDelete();
            $table->string('cost_type'); // material, labor, machine, overhead
            $table->string('description')->nullable();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['manufacturing_order_id', 'cost_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_costs');
    }
};
