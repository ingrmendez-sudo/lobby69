<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public bool $withinTransaction = false;

    public function up(): void
    {
        Schema::table('availability', function (Blueprint $table) {
            // Slot textual: reemplaza duration_hours como selector principal
            $table->string('slot', 20)->default('hoy')->after('user_id');
            // duration_hours pasa a nullable (compatibilidad con registros viejos)
            $table->integer('duration_hours')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('availability', function (Blueprint $table) {
            $table->dropColumn('slot');
            $table->integer('duration_hours')->nullable(false)->change();
        });
    }
};