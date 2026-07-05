<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body');
            $table->string('category', 100)->nullable();
            $table->string('cover_path')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('author_id', 100)->nullable();
            $table->unsignedBigInteger('views')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'slug', 'excerpt', 'body', 'category',
                'cover_path', 'published', 'published_at', 'author_id', 'views'
            ]);
        });
    }
};
