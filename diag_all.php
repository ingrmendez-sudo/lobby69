<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = DB::table('users')
    ->whereIn('username', ['Luna_MX', 'ParejaCDMX2', 'Single_uno'])
    ->select('id', 'username', 'membership_type', 'email')
    ->get();

foreach ($users as $u) {
    echo $u->username . ' | ' . ($u->membership_type ?? 'NULL') . ' | id=' . $u->id . PHP_EOL;
}

$photo = DB::table('photos')->where('id', 32)->first();
if ($photo) {
    echo PHP_EOL . '--- FOTO id=32 ---' . PHP_EOL;
    echo 'user_id='    . $photo->user_id         . PHP_EOL;
    echo 'file_path='  . $photo->file_path        . PHP_EOL;
    echo 'is_profile=' . $photo->is_profile_photo . PHP_EOL;
    echo 'album_type=' . $photo->album_type        . PHP_EOL;
    echo 'status='     . $photo->status            . PHP_EOL;
} else {
    echo PHP_EOL . 'FOTO id=32 NO EXISTE' . PHP_EOL;
}
