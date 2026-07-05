<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('address', 300)->nullable();
            $table->string('organized_by', 200)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_published')->default(false);
            $table->string('created_by', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'description', 'address', 'organized_by',
                'starts_at', 'ends_at', 'image_path',
                'is_online', 'is_published', 'created_by'
            ]);
        });
    }
};
