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
        Schema::create('password_policies', function (Blueprint $table) {
            $table->id();
            $table->integer('min_length')->default(8);
            $table->boolean('require_uppercase')->default(true);
            $table->boolean('require_lowercase')->default(true);
            $table->boolean('require_number')->default(true);
            $table->boolean('require_special_char')->default(true);
            $table->integer('max_age_days')->nullable();
            $table->integer('history_count')->default(5);
            $table->integer('max_login_attempts')->default(5);
            $table->integer('lockout_minutes')->default(30);
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
        Schema::dropIfExists('password_policies');
    }
};
