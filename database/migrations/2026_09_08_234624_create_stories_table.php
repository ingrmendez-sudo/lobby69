<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');                              // ← uuid, igual que users.id
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('category')->default('general');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('reading_time')->default(1);
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index(['status', 'created_at']);
            $table->index('user_id');
            $table->index('views');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
