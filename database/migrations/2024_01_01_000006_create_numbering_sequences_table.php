<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numbering_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Invoice", "Purchase Order"
            $table->string('code', 50)->unique(); // e.g. "INV", "PO"
            $table->string('prefix')->nullable(); // e.g. "INV-"
            $table->string('suffix')->nullable();
            $table->unsignedBigInteger('next_number')->default(1);
            $table->unsignedSmallInteger('padding')->default(5); // e.g. 00001
            $table->boolean('reset_yearly')->default(true);
            $table->string('current_year', 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_sequences');
    }
};
