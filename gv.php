<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$path = __DIR__ . '/app/Http/Controllers/FollowController.php';
$src  = file_get_contents($path);
$orig = $src;

// Reemplazar el bloque map() de avatares en followers() y following()
// Ambos tienen el mismo patrón de map — lo reemplazamos los dos

$oldMap = <<<'OLDMAP'
        // Avatar para cada seguidor
        $followers = $followers->map(function ($f) {
            $avatar = DB::table('photos')
                ->where('user_id', $f->user_id)
                ->where('is_profile_photo', true)
                ->where('status', 'approved')
                ->value('file_path');

            if (!$avatar) {
                $avatar = DB::table('photos')
                    ->where('user_id', $f->user_id)
                    ->where('album_type', 'public')
                    ->where('status', 'approved')
                    ->orderBy('sort_order')
                    ->value('file_path');
            }

            $f->avatar_url = $avatar
                ? route('photos.serve', DB::table('photos')
                    ->where('file_path', $avatar)
                    ->value('id'))
                : null;

            return $f;
        });
OLDMAP;

$newMap = <<<'NEWMAP'
        // Avatar para cada seguidor — ID directo, sin file_path
        $followers = $followers->map(function ($f) {
            $avatarId = DB::table('photos')
                ->whereRaw('user_id::text = ?', [(string)$f->user_id])
                ->where('is_profile_photo', true)
                ->where('status', 'approved')
                ->value('id');

            if (!$avatarId) {
                $avatarId = DB::table('photos')
                    ->whereRaw('user_id::text = ?', [(string)$f->user_id])
                    ->where('album_type', 'public')
                    ->where('status', 'approved')
                    ->orderBy('sort_order')
                    ->orderBy('created_at')
                    ->value('id');
            }

            $f->avatar_photo_id = $avatarId;
            $f->avatar_url      = $avatarId
                ? route('photos.serve', $avatarId)
                : null;

            return $f;
        });
NEWMAP;

if (strpos($src, $oldMap) !== false) {
    $src = str_replace($oldMap, $newMap, $src);
    echo "Fix D1: followers() avatar map patched" . PHP_EOL;
} else {
    echo "WARN Fix D1: followers() map pattern not found" . PHP_EOL;
}

$oldMap2 = <<<'OLDMAP2'
        // Avatar para cada seguido
        $following = $following->map(function ($f) {
            $avatar = DB::table('photos')
                ->where('user_id', $f->user_id)
                ->where('is_profile_photo', true)
                ->where('status', 'approved')
                ->value('file_path');

            if (!$avatar) {
                $avatar = DB::table('photos')
                    ->where('user_id', $f->user_id)
                    ->where('album_type', 'public')
                    ->where('status', 'approved')
                    ->orderBy('sort_order')
                    ->value('file_path');
            }

            $f->avatar_url = $avatar
                ? route('photos.serve', DB::table('photos')
                    ->where('file_path', $avatar)
                    ->value('id'))
                : null;

            return $f;
        });
OLDMAP2;

$newMap2 = <<<'NEWMAP2'
        // Avatar para cada seguido — ID directo, sin file_path
        $following = $following->map(function ($f) {
            $avatarId = DB::table('photos')
                ->whereRaw('user_id::text = ?', [(string)$f->user_id])
                ->where('is_profile_photo', true)
                ->where('status', 'approved')
                ->value('id');

            if (!$avatarId) {
                $avatarId = DB::table('photos')
                    ->whereRaw('user_id::text = ?', [(string)$f->user_id])
                    ->where('album_type', 'public')
                    ->where('status', 'approved')
                    ->orderBy('sort_order')
                    ->orderBy('created_at')
                    ->value('id');
            }

            $f->avatar_photo_id = $avatarId;
            $f->avatar_url      = $avatarId
                ? route('photos.serve', $avatarId)
                : null;

            return $f;
        });
NEWMAP2;

if (strpos($src, $oldMap2) !== false) {
    $src = str_replace($oldMap2, $newMap2, $src);
    echo "Fix D2: following() avatar map patched" . PHP_EOL;
} else {
    echo "WARN Fix D2: following() map pattern not found" . PHP_EOL;
}

if ($src !== $orig) {
    file_put_contents($path, $src);
    echo "FollowController.php saved." . PHP_EOL;
} else {
    echo "WARN: FollowController.php unchanged." . PHP_EOL;
}
