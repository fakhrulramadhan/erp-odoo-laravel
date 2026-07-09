<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('email');
            $table->foreignId('branch_id')->nullable()->after('company_id');
            $table->foreignId('department_id')->nullable()->after('branch_id');
            $table->foreignId('position_id')->nullable()->after('department_id');
            $table->string('avatar')->nullable()->after('name');
            $table->string('phone')->nullable()->after('avatar');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('password');
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_id', 'branch_id', 'department_id',
                'position_id', 'avatar', 'phone', 'status',
                'last_login_at',
            ]);
        });
    }
};
