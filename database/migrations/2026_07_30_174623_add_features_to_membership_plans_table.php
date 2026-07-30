<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->jsonb('features')->nullable()->after('sort_order');
        });

        // Poblar features por defecto para cada plan
        $plans = [
            'invitado' => [
                'max_photos'               => 3,
                'max_videos'               => 0,
                'max_messages_day'         => 5,
                'max_direct_messages_day'  => 2,
                'can_view_private_photos'  => false,
                'can_video_call'           => false,
                'can_see_visitors'         => false,
                'can_send_friend_request'  => false,
                'profile_boost'            => false,
                'priority_support'         => false,
                'grace_period_hours'       => 1,
            ],
            'explorer' => [
                'max_photos'               => 20,
                'max_videos'               => 3,
                'max_messages_day'         => 30,
                'max_direct_messages_day'  => 10,
                'can_view_private_photos'  => false,
                'can_video_call'           => false,
                'can_see_visitors'         => true,
                'can_send_friend_request'  => true,
                'profile_boost'            => false,
                'priority_support'         => false,
                'grace_period_hours'       => 0,
            ],
            'connectors' => [
                'max_photos'               => 50,
                'max_videos'               => 10,
                'max_messages_day'         => 100,
                'max_direct_messages_day'  => 30,
                'can_view_private_photos'  => true,
                'can_video_call'           => false,
                'can_see_visitors'         => true,
                'can_send_friend_request'  => true,
                'profile_boost'            => false,
                'priority_support'         => false,
                'grace_period_hours'       => 0,
            ],
            'influencer' => [
                'max_photos'               => 150,
                'max_videos'               => 30,
                'max_messages_day'         => 300,
                'max_direct_messages_day'  => 100,
                'can_view_private_photos'  => true,
                'can_video_call'           => true,
                'can_see_visitors'         => true,
                'can_send_friend_request'  => true,
                'profile_boost'            => false,
                'priority_support'         => false,
                'grace_period_hours'       => 0,
            ],
            'vip_elite' => [
                'max_photos'               => 500,
                'max_videos'               => 100,
                'max_messages_day'         => 999,
                'max_direct_messages_day'  => 999,
                'can_view_private_photos'  => true,
                'can_video_call'           => true,
                'can_see_visitors'         => true,
                'can_send_friend_request'  => true,
                'profile_boost'            => true,
                'priority_support'         => false,
                'grace_period_hours'       => 0,
            ],
            'vitalicio' => [
                'max_photos'               => 9999,
                'max_videos'               => 9999,
                'max_messages_day'         => 9999,
                'max_direct_messages_day'  => 9999,
                'can_view_private_photos'  => true,
                'can_video_call'           => true,
                'can_see_visitors'         => true,
                'can_send_friend_request'  => true,
                'profile_boost'            => true,
                'priority_support'         => true,
                'grace_period_hours'       => 0,
            ],
        ];

        foreach ($plans as $slug => $features) {
            DB::table('membership_plans')
                ->where('slug', $slug)
                ->update(['features' => json_encode($features)]);
        }
    }

    public function down(): void
    {
        Schema::table('membership_plans', function (Blueprint $table) {
            $table->dropColumn('features');
        });
    }
};
