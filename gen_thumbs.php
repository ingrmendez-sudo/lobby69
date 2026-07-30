<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$photos = \Illuminate\Support\Facades\DB::table('photos')
    ->where('status', 'approved')
    ->whereNull('thumbnail_path')
    ->get();

echo "Fotos a procesar: " . count($photos) . PHP_EOL;

$ok = 0; $fail = 0;

foreach ($photos as $photo) {
    $thumb = \App\Services\ThumbnailService::generate($photo->file_path);

    if ($thumb) {
        \Illuminate\Support\Facades\DB::table('photos')
            ->where('id', $photo->id)
            ->update(['thumbnail_path' => $thumb]);
        echo "OK: " . basename($photo->file_path) . " -> " . $thumb . PHP_EOL;
        $ok++;
    } else {
        echo "FAIL: " . basename($photo->file_path) . PHP_EOL;
        $fail++;
    }
}

echo PHP_EOL . "Completado: {$ok} OK, {$fail} fallidos" . PHP_EOL;
