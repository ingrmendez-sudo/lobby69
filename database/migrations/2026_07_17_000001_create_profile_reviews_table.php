<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('profile_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('reviewer_id');
            $table->uuid('reviewed_id');
            $table->enum('type', ['positive', 'negative']);
            $table->text('body')->nullable();
            $table->timestamps();
            $table->unique(['reviewer_id', 'reviewed_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('profile_reviews'); }
};
