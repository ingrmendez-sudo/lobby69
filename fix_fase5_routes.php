<?php
/**
 * fix_fase5_routes.php
 * 1) Registra rutas de verificación (usuario + admin)
 * 2) Registra middleware CheckMembershipStatus
 * 3) Crea ruta para servir imágenes privadas de verificación
 * 4) Crea directorio storage/app/private/verifications
 */

// ══════════════════════════════════════════════════════
// 1. REGISTRAR MIDDLEWARE en bootstrap/app.php
// ══════════════════════════════════════════════════════
$bootstrapFile = __DIR__ . '/bootstrap/app.php';
$bootstrap = file_get_contents($bootstrapFile);

$middlewareToAdd = [
    "'check.membership' => \\App\\Http\\Middleware\\CheckMembershipStatus::class,",
];

foreach ($middlewareToAdd as $mw) {
    $alias = explode("'", $mw)[1];
    if (strpos($bootstrap, $alias) === false) {
        $bootstrap = str_replace(
            "'force.password.change'",
            "'force.password.change'\n            " . $mw,
            $bootstrap
        );
        echo "✅ Middleware '$alias' registrado en bootstrap/app.php\n";
    } else {
        echo "ℹ️  Middleware '$alias' ya existe\n";
    }
}
file_put_contents($bootstrapFile, $bootstrap);

// ══════════════════════════════════════════════════════
// 2. ACTUALIZAR routes/web.php
// ══════════════════════════════════════════════════════
$routesFile = __DIR__ . '/routes/web.php';
$routes = file_get_contents($routesFile);

// Verificar que no estén ya las rutas
if (strpos($routes, 'verification.show') !== false) {
    echo "ℹ️  Rutas de verificación ya existen en web.php\n";
} else {
    // Añadir use statements si no existen
    $useStatements = [
        'use App\Http\Controllers\Verification\VerificationController;',
        'use App\Http\Controllers\Admin\AdminVerificationController;',
    ];
    foreach ($useStatements as $use) {
        $class = substr($use, strrpos($use, '\\') + 1, -1);
        if (strpos($routes, $class) === false) {
            $routes = str_replace("<?php\n", "<?php\n$use\n", $routes);
            echo "✅ Use statement añadido: $class\n";
        }
    }

    // Nuevas rutas a añadir
    $newRoutes = <<<'ROUTES'

// ── VERIFICACIÓN DE IDENTIDAD ─────────────────────────
Route::middleware(['auth', 'force.password.change', 'profile.completed'])->group(function () {
    Route::get('/verificar',          [VerificationController::class, 'show'])->name('verification.show');
    Route::post('/verificar',         [VerificationController::class, 'store'])->name('verification.store');
    Route::get('/verificar/pendiente',[VerificationController::class, 'pending'])->name('verification.pending');
    Route::get('/verificar/estado',   [VerificationController::class, 'status'])->name('verification.status');
});

// ── IMAGEN PRIVADA DE VERIFICACIÓN (solo admin) ───────
Route::middleware(['auth', 'admin.only'])->group(function () {
    Route::get('/admin/verificaciones/imagen/{id}',
        [AdminVerificationController::class, 'serveImage'])->name('admin.verifications.image');
});

// ── ADMIN: VERIFICACIONES ─────────────────────────────
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/verificaciones',           [AdminVerificationController::class, 'index'])->name('verifications.index');
    Route::get('/verificaciones/{id}',      [AdminVerificationController::class, 'show'])->name('verifications.show');
    Route::post('/verificaciones/{id}/aprobar',  [AdminVerificationController::class, 'approve'])->name('verifications.approve');
    Route::post('/verificaciones/{id}/rechazar', [AdminVerificationController::class, 'reject'])->name('verifications.reject');
});
ROUTES;

    // Insertar antes del último cierre del archivo
    $routes = rtrim($routes) . "\n" . $newRoutes . "\n";
    echo "✅ Rutas de verificación añadidas\n";
}

file_put_contents($routesFile, $routes);

// ══════════════════════════════════════════════════════
// 3. AÑADIR serveImage() al AdminVerificationController
// ══════════════════════════════════════════════════════
$adminVerifController = __DIR__ . '/app/Http/Controllers/Admin/AdminVerificationController.php';
$controllerContent = file_get_contents($adminVerifController);

if (strpos($controllerContent, 'serveImage') === false) {
    $serveImageMethod = <<<'PHP'

    public function serveImage($id)
    {
        $verification = \Illuminate\Support\Facades\DB::table('verifications')
            ->where('id', $id)->first();

        if (!$verification) abort(404);

        $path = storage_path('app/private/' . $verification->selfie_path);

        if (!file_exists($path)) {
            abort(404, 'Imagen no encontrada');
        }

        $mimeType = mime_content_type($path);
        return response()->file($path, [
            'Content-Type'  => $mimeType,
            'Cache-Control' => 'no-store, no-cache',
            'X-Robots-Tag'  => 'noindex',
        ]);
    }
PHP;

    // Insertar antes del último }
    $controllerContent = preg_replace('/}\s*$/', $serveImageMethod . "\n}", $controllerContent);
    file_put_contents($adminVerifController, $controllerContent);
    echo "✅ Método serveImage() añadido al AdminVerificationController\n";
} else {
    echo "ℹ️  serveImage() ya existe\n";
}

// ══════════════════════════════════════════════════════
// 4. CREAR DIRECTORIOS DE STORAGE PRIVADO
// ══════════════════════════════════════════════════════
$dirs = [
    __DIR__ . '/storage/app/private',
    __DIR__ . '/storage/app/private/verifications',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Directorio creado: " . str_replace(__DIR__ . '/', '', $dir) . "\n";
    } else {
        echo "ℹ️  Ya existe: " . str_replace(__DIR__ . '/', '', $dir) . "\n";
    }
}

// Crear .gitignore para no subir fotos de verificación
$gitignore = __DIR__ . '/storage/app/private/verifications/.gitignore';
if (!file_exists($gitignore)) {
    file_put_contents($gitignore, "*\n!.gitignore\n");
    echo "✅ .gitignore creado en verifications/\n";
}

// ══════════════════════════════════════════════════════
// 5. AÑADIR 'check.membership' al grupo auth en web.php
// ══════════════════════════════════════════════════════
$routes = file_get_contents($routesFile);

// Añadir check.membership al middleware del dashboard
if (strpos($routes, 'check.membership') === false) {
    $routes = str_replace(
        "['auth', 'force.password.change', 'profile.completed'])->group(function () {\n    Route::get('/dashboard'",
        "['auth', 'force.password.change', 'profile.completed', 'check.membership'])->group(function () {\n    Route::get('/dashboard'",
        $routes
    );
    file_put_contents($routesFile, $routes);
    echo "✅ Middleware check.membership añadido al grupo del dashboard\n";
} else {
    echo "ℹ️  check.membership ya está en el grupo del dashboard\n";
}

// ══════════════════════════════════════════════════════
// 6. AÑADIR BANNER DE VERIFICACIÓN al dashboard
// ══════════════════════════════════════════════════════
$dashBlade = __DIR__ . '/resources/views/dashboard/index.blade.php';
$dash = file_get_contents($dashBlade);

if (strpos($dash, 'verification_status') === false) {
    $verificationBanner = <<<'BLADE'

        {{-- Banner verificación --}}
        @php
            $vStatus = $user->verification_status ?? 'unverified';
            $mType   = $user->membership_type ?? 'trial';
            $trialDays = $user->trial_started_at
                ? \Carbon\Carbon::parse($user->trial_started_at)->diffInDays(\Carbon\Carbon::now())
                : 0;
            $trialLeft = max(0, 7 - $trialDays);
        @endphp

        @if($vStatus === 'unverified')
        <div style="background:linear-gradient(135deg,#fef3c7,#fff);border:2px solid #f59e0b;border-radius:12px;padding:1.25rem 1.5rem;margin-top:1.5rem;display:flex;gap:1rem;align-items:center;">
            <span style="font-size:2rem;flex-shrink:0;">⚠️</span>
            <div style="flex:1;">
                <strong style="color:#92400e;font-size:.95rem;">Verifica tu identidad</strong>
                <p style="color:#78350f;font-size:.85rem;margin:.3rem 0 0;">
                    @if($trialLeft > 0)
                        Te quedan <strong>{{ $trialLeft }} día{{ $trialLeft !== 1 ? 's' : '' }}</strong> de prueba.
                    @else
                        Tu período de prueba ha terminado.
                    @endif
                    Verifica tu identidad para seguir usando LOBBY69.
                </p>
            </div>
            <a href="{{ route('verification.show') }}"
               style="flex-shrink:0;padding:.6rem 1.2rem;background:#f59e0b;color:white;border-radius:8px;font-weight:700;font-size:.85rem;text-decoration:none;white-space:nowrap;">
                Verificar ahora →
            </a>
        </div>
        @elseif($vStatus === 'pending')
        <div style="background:linear-gradient(135deg,#eff6ff,#fff);border:2px solid #3b82f6;border-radius:12px;padding:1.25rem 1.5rem;margin-top:1.5rem;display:flex;gap:1rem;align-items:center;">
            <span style="font-size:2rem;flex-shrink:0;">⏳</span>
            <div>
                <strong style="color:#1e40af;font-size:.95rem;">Verificación en revisión</strong>
                <p style="color:#1d4ed8;font-size:.85rem;margin:.3rem 0 0;">El equipo revisará tu foto en las próximas 24-48 horas.</p>
            </div>
        </div>
        @elseif($vStatus === 'approved')
        <div style="background:linear-gradient(135deg,#f0fdf4,#fff);border:2px solid #10b981;border-radius:12px;padding:1rem 1.5rem;margin-top:1.5rem;display:flex;gap:1rem;align-items:center;">
            <span style="font-size:1.5rem;">✅</span>
            <strong style="color:#065f46;font-size:.9rem;">Identidad verificada — Badge activo en tu perfil</strong>
        </div>
        @elseif($vStatus === 'rejected')
        <div style="background:linear-gradient(135deg,#fff1f2,#fff);border:2px solid #ef4444;border-radius:12px;padding:1.25rem 1.5rem;margin-top:1.5rem;display:flex;gap:1rem;align-items:center;">
            <span style="font-size:2rem;flex-shrink:0;">❌</span>
            <div style="flex:1;">
                <strong style="color:#991b1b;font-size:.95rem;">Verificación rechazada</strong>
                <p style="color:#7f1d1d;font-size:.85rem;margin:.3rem 0 0;">Revisa el motivo y envía una nueva foto.</p>
            </div>
            <a href="{{ route('verification.show') }}"
               style="flex-shrink:0;padding:.6rem 1.2rem;background:#ef4444;color:white;border-radius:8px;font-weight:700;font-size:.85rem;text-decoration:none;">
                Reintentar →
            </a>
        </div>
        @endif
BLADE;

    // Insertar después del mensaje de bienvenida en el feed
    $dash = str_replace(
        "{{-- Alerta si perfil incompleto --}}",
        $verificationBanner . "\n        {{-- Alerta si perfil incompleto --}}",
        $dash
    );
    file_put_contents($dashBlade, $dash);
    echo "✅ Banner de verificación añadido al dashboard\n";
} else {
    echo "ℹ️  Banner de verificación ya existe en dashboard\n";
}

// ══════════════════════════════════════════════════════
// 7. AÑADIR ENLACE DE VERIFICACIONES AL PANEL ADMIN
// ══════════════════════════════════════════════════════
$adminIndex = __DIR__ . '/resources/views/admin/invitations/index.blade.php';
if (file_exists($adminIndex)) {
    $adminContent = file_get_contents($adminIndex);
    if (strpos($adminContent, 'verifications') === false) {
        $adminContent = str_replace(
            '🛡️ Panel Admin',
            '🛡️ Panel Admin</h1>
    <a href="{{ route(\'admin.verifications.index\') }}"
       style="display:inline-block;margin-top:.5rem;padding:.5rem 1rem;background:#8b5cf6;color:white;border-radius:8px;font-size:.85rem;text-decoration:none;font-weight:600;">
        🛡️ Cola de Verificaciones
        @php $pending = \Illuminate\Support\Facades\DB::table(\'verifications\')->where(\'status\',\'pending\')->count(); @endphp
        @if($pending > 0)
            <span style="background:#ef4444;color:white;border-radius:50%;padding:.1rem .4rem;font-size:.75rem;margin-left:.3rem;">{{ $pending }}</span>
        @endif
    </a>
    <span style="display:none',
            $adminContent
        );
        file_put_contents($adminIndex, $adminContent);
        echo "✅ Link a verificaciones añadido en panel admin\n";
    } else {
        echo "ℹ️  Link de verificaciones ya existe en panel admin\n";
    }
}

echo "\n✅ fix_fase5_routes.php completado\n";
echo "══════════════════════════════════════\n";
echo "Ejecuta:\n";
echo "  C:\\php\\php.exe artisan view:clear\n";
echo "  C:\\php\\php.exe artisan route:clear\n";
echo "  C:\\php\\php.exe artisan serve\n";
echo "\nPrueba:\n";
echo "  http://localhost:8000/dashboard        → banner verificación\n";
echo "  http://localhost:8000/verificar        → formulario selfie\n";
echo "  http://localhost:8000/admin/verificaciones → cola admin\n";
