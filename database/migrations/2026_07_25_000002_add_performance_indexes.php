<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Desactivar transaccion automatica de Laravel
    // para poder usar CREATE INDEX sin CONCURRENTLY
    public $withinTransaction = false;

    public function up(): void
    {
        $indexes = [
            // videos
            'CREATE INDEX IF NOT EXISTS idx_videos_user_id         ON videos (user_id)',
            'CREATE INDEX IF NOT EXISTS idx_videos_status          ON videos (status)',
            'CREATE INDEX IF NOT EXISTS idx_videos_status_created  ON videos (status, created_at DESC)',
            'CREATE INDEX IF NOT EXISTS idx_videos_views           ON videos (views_count DESC)',
            // photos
            'CREATE INDEX IF NOT EXISTS idx_photos_user_id         ON photos (user_id)',
            'CREATE INDEX IF NOT EXISTS idx_photos_profile_photo   ON photos (user_id, is_profile_photo) WHERE is_profile_photo = true',
            'CREATE INDEX IF NOT EXISTS idx_photos_user_status     ON photos (user_id, status, album_type)',
            // video_likes
            'CREATE INDEX IF NOT EXISTS idx_video_likes_user_id    ON video_likes (user_id)',
            'CREATE INDEX IF NOT EXISTS idx_video_likes_video_id   ON video_likes (video_id)',
            // video_comments
            'CREATE INDEX IF NOT EXISTS idx_video_comments_video   ON video_comments (video_id)',
            'CREATE INDEX IF NOT EXISTS idx_video_comments_user    ON video_comments (user_id)',
            // follows
            'CREATE INDEX IF NOT EXISTS idx_follows_follower       ON follows (follower_id)',
            'CREATE INDEX IF NOT EXISTS idx_follows_following      ON follows (following_id)',
            // profiles
            'CREATE INDEX IF NOT EXISTS idx_profiles_user_id       ON profiles (user_id)',
            'CREATE INDEX IF NOT EXISTS idx_profiles_nickname      ON profiles (nickname)',
            // profile_views
            'CREATE INDEX IF NOT EXISTS idx_profile_views_viewed   ON profile_views (viewed_id, viewed_at DESC)',
            'CREATE INDEX IF NOT EXISTS idx_profile_views_viewer   ON profile_views (viewer_id)',
            // friendships
            'CREATE INDEX IF NOT EXISTS idx_friendships_sender     ON friendships (sender_id, status)',
        ];

        foreach ($indexes as $sql) {
            DB::statement($sql);
        }
    }

    public function down(): void
    {
        $indexes = [
            'idx_videos_user_id','idx_videos_status','idx_videos_status_created','idx_videos_views',
            'idx_photos_user_id','idx_photos_profile_photo','idx_photos_user_status',
            'idx_video_likes_user_id','idx_video_likes_video_id',
            'idx_video_comments_video','idx_video_comments_user',
            'idx_follows_follower','idx_follows_following',
            'idx_profiles_user_id','idx_profiles_nickname',
            'idx_profile_views_viewed','idx_profile_views_viewer',
            'idx_friendships_sender',
        ];
        foreach ($indexes as $idx) {
            DB::statement("DROP INDEX IF EXISTS $idx");
        }
    }
};
