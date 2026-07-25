<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Normalizar valores existentes en users.membership_type
        //    trial / trial_verified → explorer (ya tienen acceso de prueba)
        DB::statement("
            UPDATE users
            SET membership_type = 'explorer'
            WHERE membership_type IN ('trial', 'trial_verified')
        ");

        // 2. Usuarios sin membership_type → invitado
        DB::statement("
            UPDATE users
            SET membership_type = 'invitado'
            WHERE membership_type IS NULL
        ");

        // 3. Agregar slug 'invitado' a membership_plans si no existe
        $exists = DB::table('membership_plans')
            ->where('slug', 'invitado')
            ->exists();

        if (!$exists) {
            DB::table('membership_plans')->insert([
                'slug'         => 'invitado',
                'name'         => 'Invitado',
                'description'  => 'Acceso gratuito con funciones básicas',
                'price_promo'  => 0,
                'price_normal' => 0,
                'duration_days'=> null,
                'is_lifetime'  => true,
                'is_active'    => true,
                'promo_active' => false,
                'sort_order'   => 0,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // 4. Agregar índice a membership_type si no existe
        DB::statement("
            CREATE INDEX IF NOT EXISTS idx_users_membership_type
            ON users (membership_type)
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE users
            SET membership_type = 'trial'
            WHERE membership_type = 'explorer'
        ");
        DB::statement("
            UPDATE users
            SET membership_type = NULL
            WHERE membership_type = 'invitado'
        ");
    }
};