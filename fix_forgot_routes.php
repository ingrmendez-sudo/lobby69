<?php
$routesPath = __DIR__ . '/routes/web.php';
$routes = file_get_contents($routesPath);

$old = "// Debug temporal";
$new = "// Recuperacion de contrasena (publicas)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password',          [App\Http\Controllers\Auth\ForgotPasswordController::class, 'show'])
        ->name('password.forgot');
    Route::post('/forgot-password',         [App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])
        ->name('password.forgot.store');
    Route::get('/reset-password/{token}',   [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showReset'])
        ->name('password.reset');
    Route::post('/reset-password',          [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])
        ->name('password.reset.store');
});

// Debug temporal";

if (strpos($routes, $old) !== false) {
    $routes = str_replace($old, $new, $routes);
    file_put_contents($routesPath, $routes);
    echo "[OK] Rutas forgot/reset password agregadas." . PHP_EOL;
} else {
    echo "[ERROR] Patron no encontrado." . PHP_EOL;
}

// Verificar tabla password_reset_tokens
echo PHP_EOL . "Verificando tabla password_reset_tokens..." . PHP_EOL;
?>
