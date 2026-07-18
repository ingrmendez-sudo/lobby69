<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->string('title');
            $table->text('looking_for');
            $table->date('event_date')->nullable();
            $table->text('proposal')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('announcements'); }
};
