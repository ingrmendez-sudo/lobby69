<?php
$path = __DIR__ . '/app/Http/Controllers/DashboardController.php';
$content = file_get_contents($path);

// Fix 1: join usuarios en linea
$content = str_replace(
    "->join('profiles', 'users.id', '=', DB::raw('profiles.user_id::text'))",
    "->join('profiles', DB::raw('users.id::text'), '=', DB::raw('profiles.user_id::text'))",
    $content
);

file_put_contents($path, $content);
echo "OK DashboardController.php actualizado\n";

// Verificar
$check = file_get_contents($path);
$count = substr_count($check, "users.id::text");
echo "OK Encontradas $count ocurrencias del cast corregido\n";
