<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ver foto id=23
$photo = DB::table('photos')->where('id', 23)->first();
echo "=== FOTO id=23 ===\n";
if ($photo) {
    echo "  user_id:    " . $photo->user_id . "\n";
    echo "  file_path:  " . $photo->file_path . "\n";
    echo "  status:     " . $photo->status . "\n";
    echo "  album_type: " . $photo->album_type . "\n";
} else {
    echo "NO EXISTE\n";
}

// Ver las primeras 5 fotos
echo "\n=== PRIMERAS 5 FOTOS ===\n";
$fotos = DB::table('photos')->orderBy('id')->take(5)->get(['id','user_id','status','album_type','file_path']);
foreach ($fotos as $f) {
    echo "  id={$f->id} status={$f->status} album={$f->album_type} path=" . substr($f->file_path ?? '', 0, 60) . "\n";
}

// Ver qué avatar_photo_id tienen los usuarios online
echo "\n=== AVATARES DE USUARIOS CON PRESENCIA ===\n";
$users = DB::table('users')
    ->join('profiles', 'users.id', '=', 'profiles.user_id')
    ->leftJoin('photos', 'profiles.avatar_photo_id', '=', 'photos.id')
    ->take(5)
    ->get(['users.id','profiles.display_name','profiles.avatar_photo_id','photos.file_path','photos.status']);
foreach ($users as $u) {
    echo "  {$u->display_name}: avatar_id={$u->avatar_photo_id} status=" . ($u->status ?? 'NULL') . " path=" . substr($u->file_path ?? 'NULL', 0, 50) . "\n";
}
