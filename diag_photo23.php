<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$photo = DB::table('photos')->where('id', 23)->first();
if ($photo) {
    echo "FOTO 23:\n";
    echo "  user_id:    " . $photo->user_id . "\n";
    echo "  file_path:  " . $photo->file_path . "\n";
    echo "  status:     " . $photo->status . "\n";
    echo "  album_type: " . $photo->album_type . "\n";
} else {
    echo "❌ Foto id=23 NO EXISTE en la tabla photos\n";
}

// Ver fotos con ids bajos
$fotos = DB::table('photos')->orderBy('id')->take(5)->get(['id','user_id','status','album_type']);
echo "\nPrimeras 5 fotos:\n";
foreach($fotos as $f) {
    echo "  id={$f->id} user={$f->user_id} status={$f->status} album={$f->album_type}\n";
}
