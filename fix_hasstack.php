<?php
$path = __DIR__ . '/resources/views/layouts/app.blade.php';
$content = file_get_contents($path);

$content = str_replace(
    '            $hasLeftSidebar  = $__env->hasStack(\'sidebar-left\');
            $hasRightSidebar = $__env->hasStack(\'sidebar-right\');',
    '',
    $content
);

file_put_contents($path, $content);
echo "OK app.blade.php corregido\n";
