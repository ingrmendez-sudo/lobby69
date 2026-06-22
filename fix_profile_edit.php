<?php
/**
 * fix_profile_edit.php
 * 1) Crea resources/views/profile/edit.blade.php (reutiliza setup con modo edición)
 * 2) Corrige el {{ $profile->nickname }} que se muestra literal en dashboard
 */

// ══════════════════════════════════════════════════════
// ARCHIVO 1: profile/edit.blade.php
// Reutiliza el mismo formulario de setup pero con título diferente
// y sin forzar el redirect de perfil incompleto
// ══════════════════════════════════════════════════════
$editDir = __DIR__ . '/resources/views/profile';
if (!is_dir($editDir)) mkdir($editDir, 0755, true);

$editBlade = $editDir . '/edit.blade.php';

// La vista edit simplemente carga setup con una variable $editMode = true
// Esto evita duplicar todo el HTML
$editContent = <<<'BLADE'
@php
    $editMode = true;
    $pageTitle = 'Editar Perfil — LOBBY69';
@endphp
@include('profile.setup')
BLADE;

// Verificar si setup.blade.php existe
$setupBlade = $editDir . '/setup.blade.php';
if (!file_exists($setupBlade)) {
    die("❌ No se encontró profile/setup.blade.php. Ejecuta make_fase4.php primero.\n");
}

// Verificar si setup usa @extends (no puede ser @include'd directamente)
$setupContent = file_get_contents($setupBlade);
$usesExtends  = strpos($setupContent, '@extends') !== false;

if ($usesExtends) {
    // Si setup usa @extends, edit.blade.php debe ser una vista independiente
    // Copiamos setup y cambiamos el título
    $editFull = $setupContent;
    $editFull = str_replace(
        "@section('title', 'Configura tu Perfil — LOBBY69')",
        "@section('title', 'Editar Perfil — LOBBY69')",
        $editFull
    );
    $editFull = str_replace(
        '<h1 style="font-size:2rem;font-weight:800;color:var(--color-text);">Configura tu Perfil</h1>',
        '<h1 style="font-size:2rem;font-weight:800;color:var(--color-text);">Editar Perfil</h1>',
        $editFull
    );
    $editFull = str_replace(
        "action=\"{{ route('profile.store') }}\"",
        "action=\"{{ route('profile.update') }}\"",
        $editFull
    );
    // Cambiar texto del botón guardar
    $editFull = str_replace(
        '💾 Guardar Cambios',
        '💾 Actualizar Perfil',
        $editFull
    );

    file_put_contents($editBlade, $editFull);
    echo "✅ profile/edit.blade.php creado (copia de setup con route profile.update)\n";
} else {
    file_put_contents($editBlade, $editContent);
    echo "✅ profile/edit.blade.php creado (include de setup)\n";
}

// ══════════════════════════════════════════════════════
// ARCHIVO 2: Corregir {{ $profile->nickname }} en dashboard
// Aparece literal porque tiene @ que lo escapa mal
// ══════════════════════════════════════════════════════
$dashBlade = __DIR__ . '/resources/views/dashboard/index.blade.php';

if (file_exists($dashBlade)) {
    $dash = file_get_contents($dashBlade);

    // El bug: @{{ $profile->nickname }} — el @ escapa el blade y lo muestra literal
    // Debe ser simplemente {{ $profile->nickname }}
    $dash = str_replace(
        '@{{ $profile->nickname }}',
        '{{ $profile->nickname }}',
        $dash
    );

    file_put_contents($dashBlade, $dash);
    echo "✅ dashboard/index.blade.php: corregido \@{{ nickname }} → {{ nickname }}\n";
} else {
    echo "⚠️  No se encontró dashboard/index.blade.php\n";
}

// ══════════════════════════════════════════════════════
// VERIFICAR que route profile.update existe
// ══════════════════════════════════════════════════════
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $routes = file_get_contents($routesFile);
    if (strpos($routes, 'profile.update') === false) {
        echo "⚠️  Ruta 'profile.update' NO encontrada en routes/web.php\n";
        echo "   Añadiendo ruta POST /perfil/editar → profile.update...\n";

        // Buscar la línea de profile.edit y añadir update después
        $routes = str_replace(
            "Route::get('/perfil/editar', [ProfileController::class, 'edit'])->name('profile.edit');",
            "Route::get('/perfil/editar', [ProfileController::class, 'edit'])->name('profile.edit');\n    Route::post('/perfil/editar', [ProfileController::class, 'update'])->name('profile.update');",
            $routes
        );
        file_put_contents($routesFile, $routes);
        echo "✅ Ruta profile.update añadida\n";
    } else {
        echo "✅ Ruta profile.update ya existe\n";
    }
}

echo "\n✅ Todo listo. Ejecuta:\n";
echo "   C:\\php\\php.exe artisan view:clear\n";
echo "   C:\\php\\php.exe artisan route:clear\n";
echo "   C:\\php\\php.exe artisan serve\n";
