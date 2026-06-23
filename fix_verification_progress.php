<?php
/**
 * fix_verification_progress.php
 * Añade barra de progreso real al upload de verificación
 */

$blade = __DIR__ . '/resources/views/verification/show.blade.php';
$content = file_get_contents($blade);

// Reemplazar el bloque <script> completo
$oldScript = <<<'JS'
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen supera los 5MB. Por favor elige una más pequeña.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewContainer').style.display = 'block';
            document.getElementById('dropzoneContent').innerHTML =
                '<p style="color:#10b981;font-weight:600;">✅ ' + file.name + '</p><p style="font-size:.8rem;color:#9ca3af;">Haz clic para cambiar la foto</p>';
            document.getElementById('dropzone').style.borderColor = '#10b981';
        };
        reader.readAsDataURL(file);
    }
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').style.borderColor = '#e5e7eb';
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('selfieInput');
        const dt    = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;
        previewImage(input);
    }
}

document.getElementById('profileForm') && null; // placeholder

// Validar antes de submit
document.getElementById('verificationForm') &&
document.getElementById('verificationForm').addEventListener('submit', function(e) {
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
</script>
JS;

$newScript = <<<'JS'
<script>
// ── Preview de imagen ─────────────────────────────────
function previewImage(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];

    if (file.size > 10 * 1024 * 1024) {
        showError('La imagen supera los 10MB. Por favor elige una más pequeña.');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('previewContainer').style.display = 'block';
        document.getElementById('dropzoneContent').innerHTML =
            '<p style="color:#10b981;font-weight:600;margin:0;">✅ ' + file.name + '</p>' +
            '<p style="font-size:.8rem;color:#9ca3af;margin:.25rem 0 0;">Haz clic para cambiar la foto</p>';
        document.getElementById('dropzone').style.borderColor = '#10b981';
        document.getElementById('dropzone').style.background  = '#f0fdf4';
    };
    reader.readAsDataURL(file);
}

// ── Drag & drop ───────────────────────────────────────
function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone').style.borderColor = '#e5e7eb';
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('selfieInput');
        const dt    = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;
        previewImage(input);
    }
}

// ── Mostrar error ─────────────────────────────────────
function showError(msg) {
    let box = document.getElementById('jsErrorBox');
    if (!box) {
        box = document.createElement('div');
        box.id = 'jsErrorBox';
        box.style.cssText = 'background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:.85rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem;';
        document.getElementById('verificationForm').prepend(box);
    }
    box.textContent = '⚠️ ' + msg;
    box.style.display = 'block';
}

// ── Barra de progreso ─────────────────────────────────
function showProgress() {
    const existing = document.getElementById('progressContainer');
    if (existing) existing.remove();

    const container = document.createElement('div');
    container.id = 'progressContainer';
    container.style.cssText = 'margin-top:1rem;';
    container.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
            <span style="font-size:.85rem;color:#6b7280;font-weight:600;">Subiendo foto...</span>
            <span id="progressPercent" style="font-size:.85rem;color:#8b5cf6;font-weight:700;">0%</span>
        </div>
        <div style="background:#f1f5f9;border-radius:999px;height:10px;overflow:hidden;">
            <div id="progressBar"
                 style="height:100%;width:0%;background:linear-gradient(90deg,#8b5cf6,#ec4899);border-radius:999px;transition:width .3s ease;"></div>
        </div>
        <p id="progressMsg" style="font-size:.8rem;color:#9ca3af;margin:.5rem 0 0;text-align:center;">
            Preparando envío...
        </p>
    `;

    document.getElementById('submitBtn').insertAdjacentElement('afterend', container);
    return container;
}

function updateProgress(percent) {
    const bar     = document.getElementById('progressBar');
    const pct     = document.getElementById('progressPercent');
    const msg     = document.getElementById('progressMsg');
    if (!bar) return;

    bar.style.width = percent + '%';
    pct.textContent = percent + '%';

    if (percent < 30)       msg.textContent = 'Iniciando subida...';
    else if (percent < 60)  msg.textContent = 'Subiendo imagen...';
    else if (percent < 90)  msg.textContent = 'Casi listo...';
    else if (percent < 100) msg.textContent = 'Procesando...';
    else                    msg.textContent = '✅ ¡Completado! Redirigiendo...';
}

// ── Submit con XHR para progreso real ─────────────────
document.getElementById('verificationForm') &&
document.getElementById('verificationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const input = document.getElementById('selfieInput');
    if (!input.files || input.files.length === 0) {
        showError('Por favor selecciona una foto antes de enviar.');
        return;
    }

    // Ocultar error previo
    const errBox = document.getElementById('jsErrorBox');
    if (errBox) errBox.style.display = 'none';

    // Deshabilitar botón
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.style.opacity = '0.7';
    btn.innerHTML = '⏳ Enviando...';

    // Mostrar barra de progreso
    showProgress();

    // Construir FormData
    const formData = new FormData(this);

    // XHR con progreso real
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 90); // 90% = subida
            updateProgress(percent);
        }
    });

    xhr.addEventListener('load', function() {
        updateProgress(100);

        // Laravel redirige con 302 — seguir la redirección
        if (xhr.status === 200 || xhr.status === 302 || xhr.responseURL) {
            setTimeout(function() {
                // Si hay redirección, ir a esa URL
                if (xhr.responseURL && xhr.responseURL !== window.location.href) {
                    window.location.href = xhr.responseURL;
                } else {
                    // Buscar redirección en la respuesta
                    window.location.href = '/verificar/pendiente';
                }
            }, 600);
        } else if (xhr.status === 422) {
            // Error de validación
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = '🛡️ Enviar para verificación';
            document.getElementById('progressContainer').remove();
            try {
                const resp = JSON.parse(xhr.responseText);
                const msgs = Object.values(resp.errors || {}).flat();
                showError(msgs[0] || 'Error de validación.');
            } catch(ex) {
                showError('Error al enviar. Intenta de nuevo.');
            }
        } else {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = '🛡️ Enviar para verificación';
            updateProgress(0);
            showError('Error al subir. Código: ' + xhr.status + '. Intenta de nuevo.');
        }
    });

    xhr.addEventListener('error', function() {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.innerHTML = '🛡️ Enviar para verificación';
        document.getElementById('progressContainer') &&
        document.getElementById('progressContainer').remove();
        showError('Error de conexión. Verifica tu internet e intenta de nuevo.');
    });

    xhr.addEventListener('timeout', function() {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.innerHTML = '🛡️ Enviar para verificación';
        showError('La subida tardó demasiado. Intenta con una imagen más pequeña.');
    });

    xhr.timeout = 60000; // 60 segundos máximo
    xhr.open('POST', this.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});
</script>
JS;

// Reemplazar el script
if (strpos($content, 'previewImage') !== false) {
    $content = str_replace($oldScript, $newScript, $content);

    // Si no hizo match exacto, buscar y reemplazar el bloque script
    if (strpos($content, 'showProgress') === false) {
        $content = preg_replace(
            '/<script>\s*function previewImage.*?<\/script>/si',
            $newScript,
            $content
        );
        echo "✅ Script reemplazado por regex\n";
    } else {
        echo "✅ Script reemplazado por coincidencia exacta\n";
    }
} else {
    // Insertar antes de @endsection
    $content = str_replace('@endsection', $newScript . "\n@endsection", $content);
    echo "✅ Script insertado antes de @endsection\n";
}

file_put_contents($blade, $content);

echo "✅ Barra de progreso añadida a verification/show.blade.php\n";
echo "\nEjecuta:\n";
echo "   C:\\php\\php.exe artisan view:clear\n";
echo "   C:\\php\\php.exe artisan serve\n";
