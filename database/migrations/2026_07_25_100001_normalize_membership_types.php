<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar el constraint antiguo
        DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS memberships_tier_check');

        // 2. Actualizar valores viejos que puedan existir en la tabla
        DB::statement("UPDATE memberships SET tier = 'invitado'  WHERE tier = 'free'");
        DB::statement("UPDATE memberships SET tier = 'connectors' WHERE tier = 'premium'");
        DB::statement("UPDATE memberships SET tier = 'vip_elite'  WHERE tier = 'vip'");

        // 3. Crear el nuevo constraint con todos los slugs actuales
        DB::statement("
            ALTER TABLE memberships
            ADD CONSTRAINT memberships_tier_check
            CHECK (tier IN (
                'invitado',
                'explorer',
                'connectors',
                'influencer',
                'vip_elite',
                'Fundador'
            ))
        ");
    }

    public function down(): void
    {
        // Revertir al constraint original
        DB::statement('ALTER TABLE memberships DROP CONSTRAINT IF EXISTS memberships_tier_check');

        DB::statement("UPDATE memberships SET tier = 'free'    WHERE tier = 'invitado'");
        DB::statement("UPDATE memberships SET tier = 'premium' WHERE tier = 'connectors'");
        DB::statement("UPDATE memberships SET tier = 'vip'     WHERE tier = 'vip_elite'");

        DB::statement("
            ALTER TABLE memberships
            ADD CONSTRAINT memberships_tier_check
            CHECK (tier IN ('free', 'premium', 'vip'))
        ");
    }
};

