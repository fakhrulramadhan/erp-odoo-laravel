<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_blog_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('cms_blogs')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('cms_tags')->cascadeOnDelete();
            $table->unique(['blog_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_blog_tags');
    }
};
