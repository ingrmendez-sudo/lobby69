<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('announcements', function (Blueprint $table) {
            // Público objetivo: ["singles","parejas","unicornio"]
            $table->json('directed_to')->nullable()->after('title');
            // Qué busca: ["intercambios","cuckold","trio_mhm", ...]
            $table->json('what_looking')->nullable()->after('directed_to');
            // Expiración automática 4 días después de publicar
            $table->timestamp('expires_at')->nullable()->after('status');
        });
    }
    public function down(): void {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['directed_to', 'what_looking', 'expires_at']);
        });
    }
};
