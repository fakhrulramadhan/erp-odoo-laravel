<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('bill_of_materials')->cascadeOnDelete();
            $table->integer('revision_number');
            $table->string('revision_code')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable(); // tracks what changed
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_revisions');
    }
};


