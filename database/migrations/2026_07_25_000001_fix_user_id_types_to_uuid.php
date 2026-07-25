<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convertir photos.user_id de varchar a uuid
        DB::statement('ALTER TABLE photos ALTER COLUMN user_id TYPE uuid USING user_id::uuid');
        
        // Convertir videos.user_id de varchar a uuid
        DB::statement('ALTER TABLE videos ALTER COLUMN user_id TYPE uuid USING user_id::uuid');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE photos ALTER COLUMN user_id TYPE character varying USING user_id::text');
        DB::statement('ALTER TABLE videos ALTER COLUMN user_id TYPE character varying USING user_id::text');
    }
};
