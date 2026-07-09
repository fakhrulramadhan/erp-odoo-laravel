<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routing_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_id')->constrained('routings')->cascadeOnDelete();
            $table->integer('sequence')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('work_center_id')->constrained()->restrictOnDelete();
            $table->integer('duration_minutes')->default(0);
            $table->integer('setup_time_minutes')->default(0);
            $table->integer('expected_output')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['routing_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_operations');
    }
};
