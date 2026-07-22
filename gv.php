<?php
$path = 'resources/views/verification/show.blade.php';
$content = file_get_contents($path);
$content = str_replace(
    "window.location.href = '/verificar/pendiente';",
    "window.location.href = '{{ route(''verification.pending'') }}';",
    $content
);
file_put_contents($path, $content);
echo "Fixed\n";
