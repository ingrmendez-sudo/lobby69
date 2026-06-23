<?php
/**
 * fix_fase5_v4.php
 * 1) Corrige el upload de foto (JS bloqueaba el submit)
 * 2) Corrige cast UUID en AdminVerificationController
 * 3) Corrige nick en vista verification/show
 */

// ══════════════════════════════════════════════════════
// 1. CORREGIR AdminVerificationController — UUID cast
// ══════════════════════════════════════════════════════
$adminCtrl = __DIR__ . '/app/Http/Controllers/Admin/AdminVerificationController.php';
$content = file_get_contents($adminCtrl);

// Añadir cast ::uuid en todos los joins con users y profiles
$content = str_replace(
    "->join('users as u', 'u.id', '=', 'v.user_id')",
    "->join('users as u', DB::raw('u.id::text'), '=', DB::raw('v.user_id::text'))",
    $content
);
$content = str_replace(
    "->leftJoin('profiles as p', 'p.user_id', '=', 'v.user_id')",
    "->leftJoin('profiles as p', DB::raw('p.user_id::text'), '=', DB::raw('v.user_id::text'))",
    $content
);

// Corregir el where status (falta comillas en el valor)
// El error "where status = pending" sin comillas es otro bug
$content = str_replace(
    "->where('v.status', \$status)",
    "->whereRaw(\"v.status = ?\", [\$status])",
    $content
);
$content = str_replace(
    "->where('status', 'pending')",
    "->whereRaw(\"status::text = 'pending'\")",
    $content
);
$content = str_replace(
    "->where('status', 'approved')",
    "->whereRaw(\"status::text = 'approved'\")",
    $content
);
$content = str_replace(
    "->where('status', 'rejected')",
    "->whereRaw(\"status::text = 'rejected'\")",
    $content
);

file_put_contents($adminCtrl, $content);
echo "✅ AdminVerificationController — UUID cast corregido\n";

// ══════════════════════════════════════════════════════
// 2. CORREGIR VerificationController — UUID cast
// ══════════════════════════════════════════════════════
$verifCtrl = __DIR__ . '/app/Http/Controllers/Verification/VerificationController.php';
$content = file_get_contents($verifCtrl);

// Corregir wheres con user_id
$content = str_replace(
    "->where('user_id', auth()->id())",
    "->whereRaw('user_id::text = ?', [auth()->id()])",
    $content
);
$content = str_replace(
    "->where('user_id', \$userId)",
    "->whereRaw('user_id::text = ?', [\$userId])",
    $content
);

file_put_contents($verifCtrl, $content);
echo "✅ VerificationController — UUID cast corregido\n";

// ══════════════════════════════════════════════════════
// 3. CORREGIR vista verification/show.blade.php
//    Bug: @{{ $profile->nickname }} muestra literal
//    Bug: JS deshabilita el botón ANTES del submit
// ══════════════════════════════════════════════════════
$showBlade = __DIR__ . '/resources/views/verification/show.blade.php';
$blade = file_get_contents($showBlade);

// Fix 1: El nick muestra {{ 'TuNick' }} literal — problema con escape
$blade = str_replace(
    "LOBBY69 · @{{ \$profile->nickname ?? 'TuNick' }} · {{ date('d/m/Y') }}",
    "LOBBY69 · {{ \$profile->nickname ?? 'TuNick' }} · {{ date('d/m/Y') }}",
    $blade
);

// Fix 2: El JS deshabilitaba el botón y cancelaba el submit
// Reemplazar el evento click del botón por el evento submit del form
$oldScript = <<<'JS'
document.getElementById('submitBtn').addEventListener('click', function() {
    const input = document.getElementById('selfieInput');
    if (!input.files || input.files.length === 0) {
        alert('Por favor selecciona una foto antes de enviar.');
        return false;
    }
    this.innerHTML = '⏳ Enviando...';
    this.disabled = true;
});
JS;

$newScript = <<<'JS'
document.getElementById('profileForm') && null; // placeholder

// Validar antes de submit
document.querySelector('form[action*="verificar"]') &&
document.querySelector('form[action*="verificar"]').addEventListener('submit', function(e) {
    const input = document.getElementById('selfieInput');
    if (!input.files || input.files.length === 0) {
        e.preventDefault();
        alert('Por favor selecciona una foto antes de enviar.');
        return false;
    }
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '⏳ Enviando...';
    btn.disabled = true;
});
JS;

$blade = str_replace($oldScript, $newScript, $blade);

// Fix 3: Asegurar que el form tiene el id correcto para el JS
// y que el enctype está presente
$blade = str_replace(
    '<form method="POST" action="{{ route(\'verification.store\') }}" enctype="multipart/form-data">',
    '<form method="POST" action="{{ route(\'verification.store\') }}" enctype="multipart/form-data" id="verificationForm">',
    $blade
);

// Fix 4: Reemplazar el selector del form en el JS
$blade = str_replace(
    'document.querySelector(\'form[action*="verificar"]\') &&
document.querySelector(\'form[action*="verificar"]\').addEventListener',
    'document.getElementById(\'verificationForm\') &&
document.getElementById(\'verificationForm\').addEventListener',
    $blade
);

file_put_contents($showBlade, $blade);
echo "✅ verification/show.blade.php — JS y nick corregidos\n";

// ══════════════════════════════════════════════════════
// 4. VERIFICAR config/filesystems.php tiene disco private
// ══════════════════════════════════════════════════════
$fs = __DIR__ . '/config/filesystems.php';
$fsContent = file_get_contents($fs);
if (strpos($fsContent, "'private'") !== false) {
    echo "✅ Disco 'private' configurado en filesystems.php\n";
} else {
    echo "⚠️  Disco 'private' no encontrado\n";
}

// ══════════════════════════════════════════════════════
// 5. VERIFICAR PHP upload settings
// ══════════════════════════════════════════════════════
echo "\n── Configuración PHP de uploads ──\n";
echo "upload_max_filesize : " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size       : " . ini_get('post_max_size') . "\n";
echo "file_uploads        : " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "\n";

echo "\n✅ Correcciones aplicadas. Ejecuta:\n";
echo "   C:\\php\\php.exe artisan view:clear\n";
echo "   C:\\php\\php.exe artisan config:clear\n";
echo "   C:\\php\\php.exe artisan route:clear\n";
echo "   C:\\php\\php.exe artisan serve\n";
