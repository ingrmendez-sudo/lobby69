<?php
$path = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($path);

// Fix statsViews
$content = str_replace(
    "DB::table('profile_views')->where('viewed_id', \$sbUser->id)->count();",
    "DB::table('profile_views')->whereRaw('viewed_id::text = ?', [\$sbUser->id])->count();",
    $content
);

// Fix statsPhotos
$content = str_replace(
    "DB::table('photos')->where('user_id', \$sbUser->id)->where('status','approved')->count();",
    "DB::table('photos')->whereRaw('user_id::text = ?', [\$sbUser->id])->where('status','approved')->count();",
    $content
);

// Fix statsLikes
$content = str_replace(
    "->join('photos', 'photo_likes.photo_id', '=', 'photos.id')
                        ->where('photos.user_id', \$sbUser->id)->count();",
    "->join('photos', DB::raw('photo_likes.photo_id::text'), '=', DB::raw('photos.id::text'))
                        ->whereRaw('photos.user_id::text = ?', [\$sbUser->id])->count();",
    $content
);

file_put_contents($path, $content);
echo "OK Casts UUID corregidos en dashboard\n";
