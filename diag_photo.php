<?php
$photo = DB::table('photos')->where('id', 32)->first();
if ($photo) {
    echo 'user_id='        . $photo->user_id        . PHP_EOL;
    echo 'file_path='      . $photo->file_path       . PHP_EOL;
    echo 'is_profile='     . $photo->is_profile_photo . PHP_EOL;
    echo 'album_type='     . $photo->album_type       . PHP_EOL;
    echo 'status='         . $photo->status           . PHP_EOL;
} else {
    echo 'FOTO id=32 NO EXISTE EN LA BASE DE DATOS' . PHP_EOL;
}
