<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_check_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_check_id')->constrained()->cascadeOnDelete();
            $table->integer('line_number')->default(1);
            $table->string('check_item');
            $table->text('description')->nullable();
            $table->string('result')->nullable(); // pass, fail, na
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_check_lines');
    }
};
