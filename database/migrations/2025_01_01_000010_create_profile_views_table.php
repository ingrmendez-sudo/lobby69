<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('viewer_id');
            $table->uuid('viewed_id');
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['viewed_id', 'viewed_at']);
            $table->index(['viewer_id', 'viewed_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('profile_views'); }
};