<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('organized_by');
            $table->string('price_notes', 300)->nullable()->after('price');
            $table->string('currency', 10)->default('MXN')->after('price_notes');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['price', 'price_notes', 'currency']);
        });
    }
};
