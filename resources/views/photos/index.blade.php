@extends('layouts.app')
@section('title', 'Mis Fotos — LOBBY69')
@section('content')
<div style="max-width:1000px;margin:2rem auto;padding:0 1rem;">

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
    <div>
      <h1 style="font-size:1.6rem;font-weight:800;color:var(--color-text);margin:0;">📸 Mis Fotos</h1>
      <p style="color:#64748b;margin:.25rem 0 0;">Gestiona tus álbumes. Las fotos son revisadas antes de publicarse.</p>
    </div>
    <a href="{{ route('dashboard') }}" style="padding:.6rem 1rem;border:1px solid #e5e7eb;border-radius:8px;font-size:.9rem;color:#6b7280;text-decoration:none;">← Dashboard</a>
  </div>

  {{-- Mensajes --}}
  @if(session('success'))
  <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">✅ {{ session('success') }}</div>
  @endif
  @if(session('error'))
  <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:1rem;border-radius:10px;margin-bottom:1.5rem;">⚠️ {{ session('error') }}</div>
  @endif

  {{-- Formulario de subida --}}
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:2rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">➕ Subir nuevas fotos</h2>

    <form method="POST" action="{{ route('photos.store') }}" enctype="multipart/form-data" id="uploadForm">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">

        {{-- Álbum --}}
        <div>
          <label style="display:block;font-weight:600;font-size:.85rem;color:#374151;margin-bottom:.4rem;">Álbum</label>
          <select name="album_type" style="width:100%;padding:.65rem .9rem;border:2px solid #e5e7eb;border-radius:8px;font-size:.9rem;">
            <option value="public">🌐 Público (todos los verificados)</option>
            <option value="private">🔒 Privado (Connectors+)</option>
            <option value="vip">👑 VIP (VIP Elite+)</option>
          </select>
        </div>

        {{-- Caption --}}
        <div style="grid-column:span 2;">
          <label style="display:block;font-weight:600;font-size:.85rem;color:#374151;margin-bottom:.4rem;">Descripción (opcional)</label>
          <input type="text" name="caption" maxlength="200" placeholder="Agrega una descripción..."
                 style="width:100%;padding:.65rem .9rem;border:2px solid #e5e7eb;border-radius:8px;font-size:.9rem;box-sizing:border-box;">
        </div>
      </div>

      {{-- Dropzone --}}
      <div id="dropzone"
           style="border:2px dashed #e5e7eb;border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all .2s;"
           onclick="document.getElementById('photoInput').click()"
           ondragover="event.preventDefault();this.style.borderColor='#8b5cf6';this.style.background='#faf5ff'"
           ondragleave="this.style.borderColor='#e5e7eb';this.style.background='white'"
           ondrop="handlePhotoDrop(event)">
        <div id="dropContent">
          <div style="font-size:2.5rem;margin-bottom:.5rem;">🖼️</div>
          <p style="font-weight:600;color:#374151;margin:0;">Haz clic o arrastra tus fotos aquí</p>
          <p style="font-size:.82rem;color:#9ca3af;margin:.3rem 0 0;">JPG, PNG o WEBP · Máx 10MB por foto · Múltiples fotos permitidas</p>
        </div>
        <input type="file" id="photoInput" name="photos[]" accept="image/jpeg,image/png,image/webp"
               multiple style="display:none;" onchange="previewPhotos(this)">
      </div>

      {{-- Preview grid --}}
      <div id="previewGrid" style="display:none;margin-top:1rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:.75rem;"></div>

      {{-- Barra de progreso --}}
      <div id="progressContainer" style="display:none;margin-top:1rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
          <span style="font-size:.85rem;color:#6b7280;font-weight:600;">Subiendo fotos...</span>
          <span id="progressPct" style="font-size:.85rem;color:#8b5cf6;font-weight:700;">0%</span>
        </div>
        <div style="background:#f1f5f9;border-radius:999px;height:8px;overflow:hidden;">
          <div id="progressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#8b5cf6,#ec4899);border-radius:999px;transition:width .3s;"></div>
        </div>
      </div>

      <button type="submit" id="uploadBtn"
              style="margin-top:1.25rem;padding:.85rem 2rem;background:linear-gradient(135deg,#8b5cf6,#ec4899);color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:.95rem;">
        📤 Subir fotos
      </button>
    </form>
  </div>

  {{-- Álbumes --}}
  @foreach(['public' => ['🌐 Álbum Público', '#8b5cf6'], 'private' => ['🔒 Álbum Privado', '#ec4899'], 'vip' => ['👑 Álbum VIP', '#f59e0b']] as $type => [$label, $color])
  <div style="background:white;border-radius:16px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:2rem;margin-bottom:1.5rem;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1.25rem;color:{{ $color }};">{{ $label }}
      <span style="font-size:.8rem;color:#9ca3af;font-weight:400;">({{ $grouped[$type]->count() }} fotos)</span>
    </h2>

    @if($grouped[$type]->isEmpty())
    <p style="color:#9ca3af;font-size:.9rem;text-align:center;padding:1rem 0;">No hay fotos en este álbum.</p>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.75rem;">
      @foreach($grouped[$type] as $photo)
      <div style="position:relative;border-radius:10px;overflow:hidden;aspect-ratio:1;background:#f8fafc;">

        {{-- Imagen --}}
        <img src="{{ route('photos.serve', $photo->id) }}"
             alt="Foto"
             style="width:100%;height:100%;object-fit:cover;"
             onerror="this.src='';this.parentElement.style.background='#f1f5f9'">

        {{-- Estado badge --}}
        <div style="position:absolute;top:.4rem;right:.4rem;
             background:{{ $photo->status==='approved'?'#10b981':($photo->status==='rejected'?'#ef4444':'#f59e0b') }};
             color:white;padding:.2rem .5rem;border-radius:20px;font-size:.7rem;font-weight:700;">
          {{ $photo->status==='approved'?'✅':($photo->status==='rejected'?'❌':'⏳') }}
          {{ ucfirst($photo->status) }}
        </div>

        {{-- Foto de perfil badge --}}
        @if($photo->is_profile_photo)
        <div style="position:absolute;top:.4rem;left:.4rem;background:#8b5cf6;color:white;padding:.2rem .5rem;border-radius:20px;font-size:.7rem;font-weight:700;">
          ⭐ Perfil
        </div>
        @endif

        {{-- Acciones --}}
        <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.7));padding:.75rem .5rem .5rem;display:flex;gap:.4rem;justify-content:center;">
          @if($photo->status === 'approved' && !$photo->is_profile_photo)
          <form method="POST" action="{{ route('photos.profile', $photo->id) }}" style="margin:0;">
            @csrf
            <button type="submit" title="Usar como foto de perfil"
                    style="background:#8b5cf6;color:white;border:none;border-radius:6px;padding:.3rem .5rem;cursor:pointer;font-size:.75rem;">
              ⭐
            </button>
          </form>
          @endif
          <form method="POST" action="{{ route('photos.destroy', $photo->id) }}" style="margin:0;"
                onsubmit="return confirm('¿Eliminar esta foto?')">
            @csrf @method('DELETE')
            <button type="submit" title="Eliminar"
                    style="background:#ef4444;color:white;border:none;border-radius:6px;padding:.3rem .5rem;cursor:pointer;font-size:.75rem;">
              🗑️
            </button>
          </form>
        </div>

        {{-- Nota de rechazo --}}
        @if($photo->status === 'rejected' && $photo->admin_note)
        <div style="position:absolute;inset:0;background:rgba(239,68,68,.9);display:flex;align-items:center;justify-content:center;padding:.5rem;opacity:0;transition:opacity .2s;"
             onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
          <p style="color:white;font-size:.75rem;text-align:center;margin:0;">{{ $photo->admin_note }}</p>
        </div>
        @endif
      </div>
      @endforeach
    </div>
    @endif
  </div>
  @endforeach

</div>

<script>
function previewPhotos(input) {
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';
    grid.style.display = 'grid';

    Array.from(input.files).forEach(function(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.cssText = 'border-radius:8px;overflow:hidden;aspect-ratio:1;background:#f8fafc;';
            div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('dropContent').innerHTML =
        '<p style="color:#10b981;font-weight:600;margin:0;">✅ ' + input.files.length + ' foto(s) seleccionada(s)</p>' +
        '<p style="font-size:.8rem;color:#9ca3af;margin:.25rem 0 0;">Haz clic para cambiar</p>';
    document.getElementById('dropzone').style.borderColor = '#10b981';
}

function handlePhotoDrop(e) {
    e.preventDefault();
    const input = document.getElementById('photoInput');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => { if(f.type.startsWith('image/')) dt.items.add(f); });
    input.files = dt.files;
    previewPhotos(input);
}

// Upload con progreso
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('photoInput');
    if (!input.files || input.files.length === 0) {
        alert('Selecciona al menos una foto.');
        return;
    }

    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Subiendo...';
    document.getElementById('progressContainer').style.display = 'block';

    const formData = new FormData(this);
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const pct = Math.round((e.loaded / e.total) * 95);
            document.getElementById('progressBar').style.width = pct + '%';
            document.getElementById('progressPct').textContent = pct + '%';
        }
    });

    xhr.addEventListener('load', function() {
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressPct').textContent = '100%';
        setTimeout(() => { window.location.href = xhr.responseURL || '{{ route("photos.index") }}'; }, 400);
    });

    xhr.addEventListener('error', function() {
        btn.disabled = false;
        btn.innerHTML = '📤 Subir fotos';
        alert('Error de conexión. Intenta de nuevo.');
    });

    xhr.open('POST', this.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});
</script>
@endsection