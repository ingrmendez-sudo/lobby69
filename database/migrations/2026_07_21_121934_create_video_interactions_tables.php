<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('video_likes')) {
            Schema::create('video_likes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('video_id');
                $table->string('user_id');
                $table->timestamps();
                $table->unique(['video_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('video_comments')) {
            Schema::create('video_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('video_id');
                $table->string('user_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->text('body');
                $table->timestamps();
                $table->index('video_id');
                $table->index('parent_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_comments');
        Schema::dropIfExists('video_likes');
    }
};