<?php
/**
 * fix_fase5_v2.php
 * 1) Corrige días decimales en dashboard
 * 2) Añade use statements correctos en routes/web.php
 * 3) Verifica namespaces de los controladores
 */

// ══════════════════════════════════════════════════════
// 1. CORREGIR DÍAS DECIMALES en dashboard
// ══════════════════════════════════════════════════════
$dash = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content = file_get_contents($dash);

// diffInDays ya devuelve entero, el problema es diffInRealDays o casting
// Forzamos intval() para garantizar entero
$content = str_replace(
    '$trialLeft = max(0, 7 - $trialDays);',
    '$trialLeft = max(0, 7 - (int) $trialDays);',
    $content
);
$content = str_replace(
    '$trialDays = $user->trial_started_at
                ? \Carbon\Carbon::parse($user->trial_started_at)->diffInDays(\Carbon\Carbon::now())
                : 0;',
    '$trialDays = $user->trial_started_at
                ? (int) \Carbon\Carbon::parse($user->trial_started_at)->diffInDays(\Carbon\Carbon::now())
                : 0;',
    $content
);

file_put_contents($dash, $content);
echo "✅ Días de trial corregidos a entero\n";

// ══════════════════════════════════════════════════════
// 2. VERIFICAR NAMESPACES DE CONTROLADORES
// ══════════════════════════════════════════════════════
$verifController = __DIR__ . '/app/Http/Controllers/Verification/VerificationController.php';
$adminVerifController = __DIR__ . '/app/Http/Controllers/Admin/AdminVerificationController.php';

// Verificar que el namespace sea correcto
$verif = file_get_contents($verifController);
if (strpos($verif, 'namespace App\Http\Controllers\Verification;') === false) {
    echo "⚠️  Namespace incorrecto en VerificationController\n";
} else {
    echo "✅ Namespace VerificationController OK\n";
}

$adminVerif = file_get_contents($adminVerifController);
if (strpos($adminVerif, 'namespace App\Http\Controllers\Admin;') === false) {
    echo "⚠️  Namespace incorrecto en AdminVerificationController\n";
} else {
    echo "✅ Namespace AdminVerificationController OK\n";
}

// ══════════════════════════════════════════════════════
// 3. CORREGIR routes/web.php — use statements con FQCN
// ══════════════════════════════════════════════════════
$routesFile = __DIR__ . '/routes/web.php';
$routes = file_get_contents($routesFile);

// Verificar si los use statements están bien
$hasVerifUse      = strpos($routes, 'use App\Http\Controllers\Verification\VerificationController;') !== false;
$hasAdminVerifUse = strpos($routes, 'use App\Http\Controllers\Admin\AdminVerificationController;') !== false;

echo $hasVerifUse      ? "✅ Use VerificationController presente\n"      : "⚠️  Use VerificationController FALTA\n";
echo $hasAdminVerifUse ? "✅ Use AdminVerificationController presente\n" : "⚠️  Use AdminVerificationController FALTA\n";

// Si las rutas usan el nombre corto pero no tienen el use, reemplazar por FQCN
if (!$hasVerifUse) {
    $routes = str_replace(
        '[VerificationController::class,',
        '[\App\Http\Controllers\Verification\VerificationController::class,',
        $routes
    );
    echo "✅ VerificationController → FQCN aplicado en rutas\n";
}

if (!$hasAdminVerifUse) {
    $routes = str_replace(
        '[AdminVerificationController::class,',
        '[\App\Http\Controllers\Admin\AdminVerificationController::class,',
        $routes
    );
    echo "✅ AdminVerificationController → FQCN aplicado en rutas\n";
}

file_put_contents($routesFile, $routes);

// ══════════════════════════════════════════════════════
// 4. MOSTRAR routes/web.php — verificar use statements
// ══════════════════════════════════════════════════════
echo "\n── Primeras 20 líneas de routes/web.php ──\n";
$lines = explode("\n", $routes);
for ($i = 0; $i < min(20, count($lines)); $i++) {
    echo ($i+1) . ": " . $lines[$i] . "\n";
}

echo "\n✅ Fix completado. Ejecuta:\n";
echo "   C:\\php\\php.exe artisan view:clear\n";
echo "   C:\\php\\php.exe artisan route:clear\n";
echo "   C:\\php\\php.exe artisan serve\n";
