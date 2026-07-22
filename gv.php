<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ctrl = file_get_contents('app/Http/Controllers/Video/VideoGalleryController.php');

// 1. Verificar si totalVisitors está en compact()
echo "totalVisitors en compact: " . (str_contains($ctrl, "'totalVisitors'") ? "SI\n" : "NO\n");
echo "totalVisitors como variable: " . (str_contains($ctrl, '$totalVisitors') ? "SI\n" : "NO\n");

// 2. Asegurar que $totalVisitors tiene valor default antes del if($user)
if(!str_contains($ctrl, '$totalVisitors       = 0') && !str_contains($ctrl, '$totalVisitors = 0')) {
    $ctrl = str_replace(
        '$likedIds            = [];',
        '$likedIds            = [];' . "\n" . '        $totalVisitors       = 0;',
        $ctrl
    );
    echo "Default totalVisitors=0 agregado\n";
} else {
    echo "Default ya existe\n";
}

// 3. Agregar al compact si falta
if(!str_contains($ctrl, "'totalVisitors'")) {
    $ctrl = str_replace(
        "'myVideoCount', 'myLikesReceived', 'myCommentsReceived', 'likedIds'",
        "'myVideoCount', 'myLikesReceived', 'myCommentsReceived', 'likedIds', 'totalVisitors'",
        $ctrl
    );
    echo "totalVisitors agregado al compact\n";
} else {
    echo "totalVisitors ya estaba en compact\n";
}

// 4. Verificar que la query de profileVisitors calcula totalVisitors
if(!str_contains($ctrl, '$totalVisitors = DB::table')) {
    // Buscar el fin de la query profileVisitors y agregar el count ahí
    $ctrl = str_replace(
        '->orderByDesc(\'pv.viewed_at\')
                ->limit(5)
                ->get();',
        '->orderByDesc(\'pv.viewed_at\')
                ->limit(5)
                ->get();
            $totalVisitors = DB::table(\'profile_views\')
                ->where(DB::raw(\'viewed_id::text\'), $uid)
                ->where(DB::raw(\'viewer_id::text\'), \'!=\', $uid)
                ->distinct()
                ->count(\'viewer_id\');',
        $ctrl
    );
    echo "Query totalVisitors agregada\n";
} else {
    echo "Query totalVisitors ya existe\n";
}

file_put_contents('app/Http/Controllers/Video/VideoGalleryController.php', $ctrl);
$syntax = shell_exec('php -l app/Http/Controllers/Video/VideoGalleryController.php');
echo $syntax;

// Mostrar zona del compact para verificar
preg_match('/return view\(.*?\)\s*;/s', $ctrl, $m);
echo "\nreturn view():\n" . ($m[0] ?? 'no encontrado') . "\n";
?>
