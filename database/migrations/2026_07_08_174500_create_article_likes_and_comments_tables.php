<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('article_likes')) {
            Schema::create('article_likes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('article_id');
                $table->uuid('user_id');
                $table->timestamp('created_at')->nullable();
                $table->unique(['article_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('article_comments')) {
            Schema::create('article_comments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('article_id');
                $table->uuid('user_id');
                $table->text('body');
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('article_likes');
        Schema::dropIfExists('article_comments');
    }
};
