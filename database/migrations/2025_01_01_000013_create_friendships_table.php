<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('friendships', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
            $table->timestamps();
            $table->unique(['sender_id', 'receiver_id']);
            $table->index(['receiver_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('friendships'); }
};