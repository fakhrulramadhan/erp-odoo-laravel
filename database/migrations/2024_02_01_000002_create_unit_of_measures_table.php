<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_of_measures', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('unit'); // unit, weight, volume, length, area
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('uom_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_uom_id')->constrained('unit_of_measures')->cascadeOnDelete();
            $table->foreignId('to_uom_id')->constrained('unit_of_measures')->cascadeOnDelete();
            $table->decimal('ratio', 15, 6); // 1 from_uom = ratio to_uom
            $table->timestamps();
            $table->unique(['from_uom_id', 'to_uom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
        Schema::dropIfExists('unit_of_measures');
    }
};
