<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // info, warning, success, error
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_broadcast')->default(false); // true = all users
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('is_broadcast');
        });

        Schema::create('notification_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('global_notification_id')->constrained('global_notifications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_user');
        Schema::dropIfExists('global_notifications');
    }
};
