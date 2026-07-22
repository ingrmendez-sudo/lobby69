@extends('layouts.app')
@section('title', 'Verificar Identidad — LOBBY69')
@section('content')

<div style="max-width:680px;margin:2rem auto;padding:0 1rem;">

  {{-- Header --}}
  <div style="text-align:center;margin-bottom:2rem;">
    <div style="font-size:4rem;margin-bottom:1rem;">🛡️</div>
    <h1 style="font-size:1.8rem;font-weight:800;color:var(--color-text);">Verifica tu Identidad</h1>
    <p style="color:#64748b;font-size:1rem;max-width:480px;margin:0 auto;">
      LOBBY69 es una comunidad de personas reales. La verificación garantiza la seguridad y confianza de todos los miembros.
    </p>
  </div>

  {{-- Alerta si fue rechazado --}}
  @if($lastVerification && $lastVerification->status === 'rejected')
  <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1.25rem;border-radius:12px;margin-bottom:1.5rem;">
    <strong>⚠️ Tu verificación anterior fue rechazada</strong>
    <p style="margin:.5rem 0 0;font-size:.9rem;">
      Motivo: <em>{{ $lastVerification->admin_note ?? 'Sin nota adicional' }}</em>
    </p>
    <p style="margin:.5rem 0 0;font-size:.9rem;">Por favor lee las instrucciones y envía una nueva foto.</p>
  </div>
  @endif

  {{-- Mensajes --}}
  @if(session('warning'))
  <div style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    ⚠️ {{ session('warning') }}
  </div>
  @endif

  @if($errors->any())
  <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">
    @foreach($errors->all() as $e)<p style="margin:.2rem 0;">{{ $e }}</p>@endforeach
  </div>
  @endif

  {{-- Instrucciones --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">
      📋 Cómo tomar la foto de verificación
    </h2>

    <div style="display:grid;gap:1rem;">

      <div style="display:flex;gap:1rem;align-items:flex-start;">
        <div style="background:#8b5cf6;color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">1</div>
        <div>
          <strong>Escribe en un papel:</strong>
          <div style="background:#f8fafc;border:2px dashed #8b5cf6;border-radius:8px;padding:.75rem 1rem;margin-top:.5rem;font-family:monospace;font-size:1rem;text-align:center;color:#6d28d9;">
            LOBBY69 · @{{ 'TuNick' }} · {{ date('d/m/Y') }}
          </div>
          <p style="font-size:.85rem;color:#6b7280;margin-top:.4rem;">Escribe exactamente ese texto con bolígrafo, letra clara y legible.</p>
        </div>
      </div>

      <div style="display:flex;gap:1rem;align-items:flex-start;">
        <div style="background:#8b5cf6;color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">2</div>
        <div>
          <strong>Tómate una selfie</strong>
          <p style="font-size:.85rem;color:#6b7280;margin:.4rem 0 0;">Sostén el papel junto a tu rostro. Tu cara y el texto deben verse claramente en la misma foto.</p>
        </div>
      </div>

      <div style="display:flex;gap:1rem;align-items:flex-start;">
        <div style="background:#8b5cf6;color:white;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">3</div>
        <div>
          <strong>Sube la foto</strong>
          <p style="font-size:.85rem;color:#6b7280;margin:.4rem 0 0;">Formatos aceptados: JPG o PNG. Máximo 5MB. La foto es confidencial y solo la ve el equipo de administración.</p>
        </div>
      </div>
    </div>

    {{-- Ejemplo visual --}}
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:1rem;margin-top:1.25rem;text-align:center;">
      <p style="font-size:.85rem;color:#166534;margin:0;">
        🔒 <strong>Confidencialidad garantizada:</strong> Tu foto de verificación nunca será publicada ni compartida. Se usa exclusivamente para validar que eres una persona real.
      </p>
    </div>
  </div>

  {{-- Formulario --}}
  @if($canRetry)
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">
      📸 Subir foto de verificación
      @if($attemptNumber > 1)
        <span style="font-size:.8rem;color:#f59e0b;font-weight:400;">(Intento #{{ $attemptNumber }})</span>
      @endif
    </h2>

    <form method="POST" action="{{ route('verification.store') }}" enctype="multipart/form-data" id="verificationForm">
      @csrf

      {{-- Preview de imagen --}}
      <div id="previewContainer" style="display:none;margin-bottom:1rem;text-align:center;">
        <img id="previewImg" src="" alt="Preview"
             style="max-width:100%;max-height:300px;border-radius:10px;border:2px solid #8b5cf6;">
        <p style="font-size:.8rem;color:#6b7280;margin-top:.5rem;">Vista previa de tu foto</p>
      </div>

      <div style="border:2px dashed #e5e7eb;border-radius:10px;padding:2rem;text-align:center;cursor:pointer;transition:border-color .2s;"
           id="dropzone"
           onclick="document.getElementById('selfieInput').click()"
           ondragover="event.preventDefault();this.style.borderColor='#8b5cf6'"
           ondragleave="this.style.borderColor='#e5e7eb'"
           ondrop="handleDrop(event)">
        <div id="dropzoneContent">
          <div style="font-size:2.5rem;margin-bottom:.5rem;">📷</div>
          <p style="font-weight:600;color:#374151;margin:0;">Haz clic o arrastra tu foto aquí</p>
          <p style="font-size:.85rem;color:#9ca3af;margin:.25rem 0 0;">JPG o PNG · Máximo 5MB</p>
        </div>
        <input type="file" id="selfieInput" name="selfie" accept="image/jpeg,image/png"
               style="display:none;" onchange="previewImage(this)">
      </div>

      <button type="submit" id="submitBtn"
              style="width:100%;margin-top:1.25rem;padding:1rem;background:linear-gradient(135deg,#8b5cf6,#ec4899);color:white;border:none;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.5rem;">
        🛡️ Enviar para verificación
      </button>
    </form>
  </div>
  @endif

  {{-- Tiempo estimado --}}
  <div style="text-align:center;color:#9ca3af;font-size:.85rem;margin-bottom:2rem;">
    ⏱️ Tiempo de revisión estimado: <strong style="color:#6b7280;">24 a 48 horas</strong>
  </div>

</div>

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
                    window.location.href = '{{ route(''verification.pending'') }}';
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
@endsection
