<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recrear video_likes con user_id uuid
        Schema::dropIfExists('video_likes');
        Schema::create('video_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->uuid('user_id');
            $table->timestamps();
            $table->unique(['video_id', 'user_id']);
        });

        // Recrear video_comments con user_id uuid
        Schema::dropIfExists('video_comments');
        Schema::create('video_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('video_id');
            $table->uuid('user_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('body');
            $table->timestamps();
            $table->index('video_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_comments');
        Schema::dropIfExists('video_likes');
    }
};