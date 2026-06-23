<?php
$path = __DIR__ . '/routes/web.php';
$content = file_get_contents($path);

$content = str_replace(
    'use App\Http\Controllers\Dashboard\DashboardController;',
    'use App\Http\Controllers\DashboardController;',
    $content
);

file_put_contents($path, $content);
echo "OK routes/web.php actualizado\n";

$ctrlPath = __DIR__ . '/app/Http/Controllers/DashboardController.php';
if (file_exists($ctrlPath)) {
    echo "OK DashboardController.php existe\n";
    $ctrl = file_get_contents($ctrlPath);
    if (str_contains($ctrl, 'namespace App\Http\Controllers;')) {
        echo "OK Namespace correcto\n";
    } else {
        echo "FAIL Namespace incorrecto\n";
    }
} else {
    echo "FAIL DashboardController.php NO existe\n";
}
