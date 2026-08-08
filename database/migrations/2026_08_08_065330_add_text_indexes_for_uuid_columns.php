<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Laravel 12 declara $withinTransaction como readonly en la clase padre.
     * Para deshabilitar la transaccion, sobreescribimos el metodo.
     */
    public function withinTransaction(): bool
    {
        return false;
    }

    public function up(): void
    {
        $indexes = [
            'idx_pv_viewed_id_text'     => 'CREATE INDEX IF NOT EXISTS idx_pv_viewed_id_text     ON profile_views ((viewed_id::text))',
            'idx_pv_viewer_id_text'     => 'CREATE INDEX IF NOT EXISTS idx_pv_viewer_id_text     ON profile_views ((viewer_id::text))',
            'idx_pv_viewed_viewer_at'   => 'CREATE INDEX IF NOT EXISTS idx_pv_viewed_viewer_at   ON profile_views ((viewed_id::text), (viewer_id::text), viewed_at DESC)',
            'idx_photos_user_id_text'   => 'CREATE INDEX IF NOT EXISTS idx_photos_user_id_text   ON photos ((user_id::text))',
            'idx_photos_uuid_text'      => 'CREATE INDEX IF NOT EXISTS idx_photos_uuid_text       ON photos ((photo_uuid::text))',
            'idx_photos_status_appr'    => "CREATE INDEX IF NOT EXISTS idx_photos_status_appr    ON photos (status) WHERE status = 'approved'",
            'idx_photos_pfp_approved'   => "CREATE INDEX IF NOT EXISTS idx_photos_pfp_approved   ON photos (user_id, is_profile_photo) WHERE is_profile_photo = true AND status = 'approved'",
            'idx_pl_photo_id_text'      => 'CREATE INDEX IF NOT EXISTS idx_pl_photo_id_text      ON photo_likes ((photo_id::text))',
            'idx_pl_user_id_text'       => 'CREATE INDEX IF NOT EXISTS idx_pl_user_id_text       ON photo_likes ((user_id::text))',
            'idx_pc_photo_id_text'      => 'CREATE INDEX IF NOT EXISTS idx_pc_photo_id_text      ON photo_comments ((photo_id::text))',
            'idx_pc_approved'           => "CREATE INDEX IF NOT EXISTS idx_pc_approved            ON photo_comments (photo_id) WHERE status = 'approved'",
            'idx_follows_follower_txt'  => 'CREATE INDEX IF NOT EXISTS idx_follows_follower_txt  ON follows ((follower_id::text))',
            'idx_follows_following_txt' => 'CREATE INDEX IF NOT EXISTS idx_follows_following_txt ON follows ((following_id::text))',
            'idx_profiles_uid_text'     => 'CREATE INDEX IF NOT EXISTS idx_profiles_uid_text     ON profiles ((user_id::text))',
            'idx_profiles_city'         => 'CREATE INDEX IF NOT EXISTS idx_profiles_city         ON profiles (city) WHERE city IS NOT NULL',
        ];

        foreach ($indexes as $name => $sql) {
            try {
                DB::statement($sql);
                echo "  OK: {$name}" . PHP_EOL;
            } catch (\Throwable $e) {
                echo "  SKIP: {$name} — " . $e->getMessage() . PHP_EOL;
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            'idx_pv_viewed_id_text',    'idx_pv_viewer_id_text',
            'idx_pv_viewed_viewer_at',  'idx_photos_user_id_text',
            'idx_photos_uuid_text',     'idx_photos_status_appr',
            'idx_photos_pfp_approved',  'idx_pl_photo_id_text',
            'idx_pl_user_id_text',      'idx_pc_photo_id_text',
            'idx_pc_approved',          'idx_follows_follower_txt',
            'idx_follows_following_txt','idx_profiles_uid_text',
            'idx_profiles_city',
        ];

        foreach ($indexes as $index) {
            try {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            } catch (\Throwable $e) {
                // ignorar si no existe
            }
        }
    }
};