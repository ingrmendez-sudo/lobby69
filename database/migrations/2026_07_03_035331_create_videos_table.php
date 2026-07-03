<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('video_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->string('user_id');
            $table->string('album_type')->default('public'); // public, private, vip
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();    // frame extraído del video
            $table->integer('duration_seconds')->nullable(); // duración en segundos
            $table->bigInteger('file_size_bytes')->nullable();
            $table->string('caption', 200)->nullable();
            $table->string('status')->default('pending');    // pending, approved, rejected
            $table->text('admin_note')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
