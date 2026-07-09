<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('import_type');
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->json('errors')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('company_id')->nullable()->constrained('companies');
            $table->timestamps();
            // Timestamps only
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
