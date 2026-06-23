<?php
/**
 * fix_fase5_v3.php
 * Reescribe TODAS las rutas de verificación con FQCN completo
 * para evitar problemas de resolución de clases
 */

$routesFile = __DIR__ . '/routes/web.php';
$routes = file_get_contents($routesFile);

// ── Eliminar bloques de verificación existentes (todos) ───────────────────
// Eliminar el bloque de rutas de verificación usuario
$routes = preg_replace(
    '/\/\/ ── VERIFICACIÓN DE IDENTIDAD.*?}\);/si',
    '',
    $routes
);

// Eliminar el bloque de imagen privada
$routes = preg_replace(
    '/\/\/ ── IMAGEN PRIVADA.*?}\);/si',
    '',
    $routes
);

// Eliminar el bloque admin verificaciones
$routes = preg_replace(
    '/\/\/ ── ADMIN: VERIFICACIONES.*?}\);/si',
    '',
    $routes
);

// Limpiar líneas vacías múltiples
$routes = preg_replace('/\n{3,}/', "\n\n", $routes);

// ── Añadir rutas limpias con FQCN completo ────────────────────────────────
$nuevasRutas = <<<'ROUTES'

// ── VERIFICACIÓN DE IDENTIDAD ─────────────────────────────────────────────
Route::middleware(['auth', 'force.password.change', 'profile.completed'])->group(function () {
    Route::get('/verificar',
        [\App\Http\Controllers\Verification\VerificationController::class, 'show'])
        ->name('verification.show');

    Route::post('/verificar',
        [\App\Http\Controllers\Verification\VerificationController::class, 'store'])
        ->name('verification.store');

    Route::get('/verificar/pendiente',
        [\App\Http\Controllers\Verification\VerificationController::class, 'pending'])
        ->name('verification.pending');

    Route::get('/verificar/estado',
        [\App\Http\Controllers\Verification\VerificationController::class, 'status'])
        ->name('verification.status');
});

// ── ADMIN: VERIFICACIONES ─────────────────────────────────────────────────
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/verificaciones',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'index'])
        ->name('verifications.index');

    Route::get('/verificaciones/{id}',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'show'])
        ->name('verifications.show');

    Route::post('/verificaciones/{id}/aprobar',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'approve'])
        ->name('verifications.approve');

    Route::post('/verificaciones/{id}/rechazar',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'reject'])
        ->name('verifications.reject');

    Route::get('/verificaciones/imagen/{id}',
        [\App\Http\Controllers\Admin\AdminVerificationController::class, 'serveImage'])
        ->name('verifications.image');
});

ROUTES;

$routes = rtrim($routes) . "\n" . $nuevasRutas;
file_put_contents($routesFile, $routes);
echo "✅ Rutas de verificación reescritas con FQCN\n";

// ── Verificar que los archivos de controladores existen ───────────────────
$controllers = [
    'app/Http/Controllers/Verification/VerificationController.php',
    'app/Http/Controllers/Admin/AdminVerificationController.php',
];

foreach ($controllers as $ctrl) {
    $path = __DIR__ . '/' . $ctrl;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        // Verificar namespace
        if (strpos($ctrl, 'Verification/Verification') !== false) {
            $expectedNS = 'namespace App\Http\Controllers\Verification;';
        } else {
            $expectedNS = 'namespace App\Http\Controllers\Admin;';
        }

        if (strpos($content, $expectedNS) !== false) {
            echo "✅ $ctrl — namespace OK\n";
        } else {
            echo "❌ $ctrl — namespace INCORRECTO\n";
            // Mostrar las primeras líneas
            $lines = array_slice(explode("\n", $content), 0, 5);
            foreach ($lines as $line) echo "   > $line\n";
        }
    } else {
        echo "❌ NO EXISTE: $ctrl\n";
    }
}

// ── Verificar storage privado ─────────────────────────────────────────────
$storageDir = __DIR__ . '/storage/app/private/verifications';
if (is_dir($storageDir)) {
    echo "✅ storage/app/private/verifications existe\n";
} else {
    mkdir($storageDir, 0755, true);
    echo "✅ storage/app/private/verifications creado\n";
}

// ── Verificar configuración de filesystem ─────────────────────────────────
$filesystemConfig = __DIR__ . '/config/filesystems.php';
if (file_exists($filesystemConfig)) {
    $fsContent = file_get_contents($filesystemConfig);
    if (strpos($fsContent, "'private'") !== false || strpos($fsContent, '"private"') !== false) {
        echo "✅ Disco 'private' configurado en filesystems.php\n";
    } else {
        echo "⚠️  Disco 'private' NO encontrado en filesystems.php — añadiendo...\n";
        $fsContent = str_replace(
            "'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],",
            "'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],
        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'throw' => false,
        ],",
            $fsContent
        );
        file_put_contents($filesystemConfig, $fsContent);
        echo "✅ Disco 'private' añadido\n";
    }
}

echo "\n✅ Todo corregido. Ejecuta:\n";
echo "   C:\\php\\php.exe artisan view:clear\n";
echo "   C:\\php\\php.exe artisan route:clear\n";
echo "   C:\\php\\php.exe artisan serve\n";
