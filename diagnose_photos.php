<?php
// Ejecutar: C:\php\php.exe diagnose_photos.php
// Carga Laravel manualmente
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n══════════════════════════════════\n";
echo "  DIAGNÓSTICO DE FOTOS - LOBBY69\n";
echo "══════════════════════════════════\n\n";

// Columnas de la tabla photos
$cols = Schema::getColumnListing('photos');
echo "Columnas en 'photos': " . implode(', ', $cols) . "\n\n";

// Conteos básicos
echo "Total fotos:           " . DB::table('photos')->count() . "\n";
echo "Status = approved:     " . DB::table('photos')->where('status','approved')->count() . "\n";
echo "Status = pending:      " . DB::table('photos')->where('status','pending')->count() . "\n";
echo "Status = rejected:     " . DB::table('photos')->where('status','rejected')->count() . "\n";

if (in_array('album_type', $cols)) {
    echo "\nalbum_type = public:   " . DB::table('photos')->where('album_type','public')->count() . "\n";
    echo "album_type = private:  " . DB::table('photos')->where('album_type','private')->count() . "\n";
    echo "album_type = NULL:     " . DB::table('photos')->whereNull('album_type')->count() . "\n";
}

if (in_array('is_public', $cols)) {
    echo "\nis_public = 1:         " . DB::table('photos')->where('is_public',1)->count() . "\n";
    echo "is_public = 0:         " . DB::table('photos')->where('is_public',0)->count() . "\n";
}

// Muestra de 3 fotos
echo "\nMuestra de 3 fotos:\n";
$sample = DB::table('photos')->limit(3)->get();
foreach ($sample as $p) {
    $row = (array) $p;
    echo "  ID: " . ($row['id'] ?? '?');
    echo " | status: " . ($row['status'] ?? '?');
    echo " | album_type: " . ($row['album_type'] ?? 'N/A');
    echo " | is_public: " . ($row['is_public'] ?? 'N/A');
    echo "\n";
}

echo "\n══════════════════════════════════\n";