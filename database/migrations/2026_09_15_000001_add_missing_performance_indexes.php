<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function withinTransaction(): bool { return false; }

    public function up(): void
    {
        $indexes = [
            // Friendships: indice en receiver_id::text para queries de amigos en comun
            // El existente idx_friendships_sender cubre sender_id pero falta receiver_id en texto
            'idx_friendships_receiver_txt' =>
                'CREATE INDEX IF NOT EXISTS idx_friendships_receiver_txt ON friendships ((receiver_id::text), status)',

            // profiles: indice compuesto para el ORDER BY last_active_at de recomendados
            'idx_profiles_active_city' =>
                "CREATE INDEX IF NOT EXISTS idx_profiles_active_city ON profiles (last_active_at DESC) WHERE profile_completed = true",

            // profile_views: indice compuesto texto para DISTINCT ON viewer+viewed
            'idx_pv_viewer_viewed_at' =>
                'CREATE INDEX IF NOT EXISTS idx_pv_viewer_viewed_at ON profile_views ((viewer_id::text), (viewed_id::text), viewed_at DESC)',
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
        foreach ([
            'idx_friendships_receiver_txt',
            'idx_profiles_active_city',
            'idx_pv_viewer_viewed_at',
        ] as $idx) {
            try { DB::statement("DROP INDEX IF EXISTS {$idx}"); } catch (\Throwable $e) {}
        }
    }
};
