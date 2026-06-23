<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('sender_id');
            $table->uuid('receiver_id');
            $table->text('body');
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['receiver_id', 'read']);
            $table->index(['sender_id', 'receiver_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('messages'); }
};