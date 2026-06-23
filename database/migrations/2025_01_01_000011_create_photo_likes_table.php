<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('photo_likes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->uuid('photo_id');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'photo_id']);
            $table->index('photo_id');
        });
    }
    public function down(): void { Schema::dropIfExists('photo_likes'); }
};