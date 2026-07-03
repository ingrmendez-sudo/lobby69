<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('follower_id');   // quien sigue
            $table->uuid('following_id');  // a quien sigue
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['follower_id', 'following_id']);

            $table->foreign('follower_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('following_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
