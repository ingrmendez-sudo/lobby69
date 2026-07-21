<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('video_id');
            $table->string('user_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['video_id', 'user_id']);
            $table->index('video_id');
            $table->index('user_id');

            $table->foreign('video_id')
                  ->references('id')->on('videos')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_likes');
    }
};