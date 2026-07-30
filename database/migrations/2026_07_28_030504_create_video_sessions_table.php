<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_sessions', function (Blueprint $table) {
            // ID propio como bigint normal (no necesita ser UUID)
            $table->id();

            // Foreign keys como UUID para compatibilidad con users.id (Supabase)
            $table->uuid('initiator_id');
            $table->uuid('receiver_id');

            $table->string('session_token', 80)->unique();
            $table->enum('type', ['private_1to1'])->default('private_1to1');
            $table->unsignedSmallInteger('max_duration_minutes');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedSmallInteger('actual_minutes')->default(0);
            $table->enum('ended_by', [
                'initiator',
                'receiver',
                'timeout',
                'system',
                'rejected',
            ])->nullable();
            $table->timestamps();

            // Foreign key constraints manuales (UUID -> UUID)
            $table->foreign('initiator_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('receiver_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            // Índices de rendimiento
            $table->index(['initiator_id', 'started_at']);
            $table->index(['receiver_id', 'started_at']);
            $table->index('session_token');
            $table->index('ended_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_sessions');
    }
};
