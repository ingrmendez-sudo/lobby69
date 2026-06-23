<?php
/**
 * fix_fase5_v5.php
 * 1) Sube límite de upload PHP a 5MB
 * 2) Añade disco 'private' a filesystems.php
 */

// ══════════════════════════════════════════════════════
// 1. CREAR/ACTUALIZAR php.ini local del proyecto
// ══════════════════════════════════════════════════════
// Laravel con artisan serve usa el php.ini del sistema
// Creamos uno local que override los valores
$phpIniPath = 'C:\php\php.ini';

if (file_exists($phpIniPath)) {
    $phpIni = file_get_contents($phpIniPath);

    // upload_max_filesize
    if (preg_match('/^upload_max_filesize\s*=/m', $phpIni)) {
        $phpIni = preg_replace('/^upload_max_filesize\s*=.*/m', 'upload_max_filesize = 10M', $phpIni);
    } else {
        $phpIni .= "\nupload_max_filesize = 10M\n";
    }

    // post_max_size
    if (preg_match('/^post_max_size\s*=/m', $phpIni)) {
        $phpIni = preg_replace('/^post_max_size\s*=.*/m', 'post_max_size = 12M', $phpIni);
    } else {
        $phpIni .= "post_max_size = 12M\n";
    }

    file_put_contents($phpIniPath, $phpIni);
    echo "✅ php.ini actualizado: upload_max_filesize=10M, post_max_size=12M\n";
} else {
    echo "⚠️  No se encontró C:\\php\\php.ini\n";
    // Buscar php.ini en otras ubicaciones
    $locations = [
        'C:\php\php.ini-development',
        'C:\php\php.ini-production',
    ];
    foreach ($locations as $loc) {
        if (file_exists($loc)) {
            echo "   Encontrado: $loc — cópialo a C:\\php\\php.ini\n";
        }
    }
    // Crear .user.ini en el proyecto para artisan serve
    $userIni = __DIR__ . '/.user.ini';
    file_put_contents($userIni, "upload_max_filesize = 10M\npost_max_size = 12M\n");
    echo "✅ .user.ini creado en el proyecto\n";
}

// ══════════════════════════════════════════════════════
// 2. CORREGIR config/filesystems.php
// ══════════════════════════════════════════════════════
$fsFile = __DIR__ . '/config/filesystems.php';
$fsContent = file_get_contents($fsFile);

if (strpos($fsContent, "'private'") !== false) {
    echo "✅ Disco 'private' ya existe en filesystems.php\n";
} else {
    // Reescribir el archivo completo con el disco private añadido
    $newFs = <<<'PHP'
<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'report' => false,
        ],

        'private' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        's3' => [
            'driver'   => 's3',
            'key'      => env('AWS_ACCESS_KEY_ID'),
            'secret'   => env('AWS_SECRET_ACCESS_KEY'),
            'region'   => env('AWS_DEFAULT_REGION'),
            'bucket'   => env('AWS_BUCKET'),
            'url'      => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'    => false,
            'report'   => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
PHP;
    file_put_contents($fsFile, $newFs);
    echo "✅ filesystems.php reescrito con disco 'private'\n";
}

// ══════════════════════════════════════════════════════
// 3. VERIFICAR directorio de verifications
// ══════════════════════════════════════════════════════
$dirs = [
    storage_path('app/private'),
    storage_path('app/private/verifications'),
];
// storage_path no disponible fuera de Laravel, usar ruta directa
$dirs = [
    __DIR__ . '/storage/app/private',
    __DIR__ . '/storage/app/private/verifications',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Creado: " . basename($dir) . "\n";
    } else {
        echo "✅ Existe: " . basename($dir) . "\n";
    }
}

// ══════════════════════════════════════════════════════
// 4. VERIFICAR que VerificationController usa disco correcto
// ══════════════════════════════════════════════════════
$verifCtrl = __DIR__ . '/app/Http/Controllers/Verification/VerificationController.php';
$ctrl = file_get_contents($verifCtrl);

// Asegurar que usa disco 'private' no 'local'
if (strpos($ctrl, "storeAs('verifications'") !== false) {
    // Corregir para usar disco private explícitamente
    $ctrl = str_replace(
        "\$path = \$file->storeAs('verifications', \$filename, 'private');",
        "\$path = \$file->storeAs('verifications', \$filename, 'private');",
        $ctrl
    );
    // Verificar que dice 'private'
    if (strpos($ctrl, "'private'") !== false) {
        echo "✅ VerificationController usa disco 'private' correctamente\n";
    } else {
        // Corregir el disco
        $ctrl = str_replace(
            "storeAs('verifications', \$filename, 'local')",
            "storeAs('verifications', \$filename, 'private')",
            $ctrl
        );
        $ctrl = str_replace(
            "storeAs('verifications', \$filename)",
            "storeAs('verifications', \$filename, 'private')",
            $ctrl
        );
        file_put_contents($verifCtrl, $ctrl);
        echo "✅ VerificationController — disco corregido a 'private'\n";
    }
}

// ══════════════════════════════════════════════════════
// 5. VERIFICAR configuración final
// ══════════════════════════════════════════════════════
echo "\n── Verificación final ──\n";
echo "upload_max_filesize : " . ini_get('upload_max_filesize') . " (se aplica al reiniciar artisan)\n";
echo "post_max_size       : " . ini_get('post_max_size') . "\n";
echo "Disco private       : " . (strpos(file_get_contents($fsFile), "'private'") !== false ? '✅ OK' : '❌ FALTA') . "\n";
echo "Dir verifications   : " . (is_dir(__DIR__ . '/storage/app/private/verifications') ? '✅ OK' : '❌ FALTA') . "\n";

echo "\n⚠️  IMPORTANTE: Reinicia artisan serve para aplicar cambios de php.ini\n";
echo "✅ Ejecuta:\n";
echo "   C:\\php\\php.exe artisan config:clear\n";
echo "   C:\\php\\php.exe artisan serve\n";
echo "   Prueba subir una imagen pequeña (menos de 2MB por ahora)\n";
