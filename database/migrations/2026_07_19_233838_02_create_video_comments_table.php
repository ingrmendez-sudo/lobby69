<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('video_id');
            $table->string('user_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('video_id');
            $table->index('user_id');
            $table->index('parent_id');

            $table->foreign('video_id')
                  ->references('id')->on('videos')
                  ->onDelete('cascade');
            $table->foreign('parent_id')
                  ->references('id')->on('video_comments')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_comments');
    }
};