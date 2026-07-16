<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$photos = DB::table('photos')->get();
$ok = 0; $fail = 0;

foreach ($photos as $photo) {
    $localPath = storage_path('app/private/' . $photo->file_path);
    if (!file_exists($localPath)) { $fail++; continue; }
    
    $contents = file_get_contents($localPath);
    try {
        Storage::disk('supabase')->put($photo->file_path, $contents, 'public');
        $ok++;
        echo "OK: {$photo->file_path}\n";
    } catch (Exception $e) {
        $fail++;
        echo "FAIL: {$photo->file_path} - {$e->getMessage()}\n";
    }
}
echo "\nMigradas: $ok | Fallidas: $fail\n";
