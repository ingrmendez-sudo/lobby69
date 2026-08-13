<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->decimal('recommendation_score', 3, 2)->default(0.00)->after('profile_completed_at');
            $table->timestamp('score_updated_at')->nullable()->after('recommendation_score');
            $table->timestamp('boost_until')->nullable()->after('score_updated_at');
            $table->decimal('boost_amount', 3, 2)->default(0.00)->after('boost_until');
        });

        Schema::create('profile_score_history', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->decimal('score', 3, 2)->default(0.00);
            $table->decimal('factor_photos', 3, 2)->default(0.00);
            $table->decimal('factor_visits', 3, 2)->default(0.00);
            $table->decimal('factor_activity', 3, 2)->default(0.00);
            $table->decimal('factor_responses', 3, 2)->default(0.00);
            $table->decimal('factor_completeness', 3, 2)->default(0.00);
            $table->timestamp('calculated_at')->useCurrent();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_score_history');
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['recommendation_score', 'score_updated_at', 'boost_until', 'boost_amount']);
        });
    }
};