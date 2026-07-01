<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Photo::selectRaw('photos.*')
    ->selectSub(
        \DB::table('photo_likes')->selectRaw('count(*)')->whereRaw('photo_likes.photo_id::text = photos.id::text'),
        'likes_count'
    )
    ->approved()->where('album_type','public')->first();

print_r($p->toArray());
