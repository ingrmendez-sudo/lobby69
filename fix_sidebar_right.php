<?php
$content = file_get_contents(__DIR__ . '/resources/views/layouts/sidebar-right.blade.php');

// Reemplazar App\Models\Photo por DB facade
$content = str_replace(
    '\App\Models\Photo::where(\'user_id\', $rUser->id)
                            ->where(\'status\', \'pending\')->count();',
    '\Illuminate\Support\Facades\DB::table(\'photos\')
                            ->where(\'user_id\', $rUser->id)
                            ->where(\'status\', \'pending\')->count();',
    $content
);
$content = str_replace(
    '\App\Models\Photo::where(\'user_id\', $rUser->id)
                             ->where(\'status\', \'approved\')->count();',
    '\Illuminate\Support\Facades\DB::table(\'photos\')
                             ->where(\'user_id\', $rUser->id)
                             ->where(\'status\', \'approved\')->count();',
    $content
);
file_put_contents(__DIR__ . '/resources/views/layouts/sidebar-right.blade.php', $content);
echo "✓ sidebar-right.blade.php actualizado\n";
