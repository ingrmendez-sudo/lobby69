@extends('layouts.app')
@section('title', 'Mis Fotos — LOBBY69')

@push('styles')
<style>
/* ── Página Mis Fotos ── */
.mf-wrap {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

/* Cabecera */
.mf-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.mf-header h1 {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--theme-text);
    margin: 0;
}
.mf-header p {
    color: var(--theme-muted, #9ca3af);
    margin: .25rem 0 0;
    font-size: .9rem;
}
.mf-back-btn {
    padding: .6rem 1rem;
    border: 1px solid var(--theme-border);
    border-radius: 8px;
    font-size: .9rem;
    color: var(--theme-muted, #6b7280);
    text-decoration: none;
    background: transparent;
    transition: background .2s;
}
.mf-back-btn:hover {
    background: var(--theme-hover, rgba(139,92,246,.08));
    color: var(--theme-text);
}

/* Alertas */
.mf-alert-ok {
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    color: #065f46;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    font-size: .9rem;
}
.mf-alert-err {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    font-size: .9rem;
}
[data-theme="dark"] .mf-alert-ok {
    background: #064e3b;
    border-color: #059669;
    color: #a7f3d0;
}
[data-theme="dark"] .mf-alert-err {
    background: #450a0a;
    border-color: #dc2626;
    color: #fca5a5;
}

/* Tarjeta genérica */
.mf-card {
    background: var(--theme-card);
    border: 1px solid var(--theme-border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
}
.mf-card h2 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 1.25rem;
    color: var(--theme-text);
}

/* Formulario de subida */
.mf-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}
.mf-form-grid label {
    display: block;
    font-weight: 600;
    font-size: .85rem;
    color: var(--theme-text);
    margin-bottom: .4rem;
}
.mf-form-grid select,
.mf-form-grid input[type="text"] {
    width: 100%;
    padding: .65rem .9rem;
    border: 2px solid var(--theme-border);
    border-radius: 8px;
    font-size: .9rem;
    background: var(--theme-input, var(--theme-card));
    color: var(--theme-text);
    box-sizing: border-box;
    transition: border-color .2s;
}
.mf-form-grid select:focus,
.mf-form-grid input[type="text"]:focus {
    outline: none;
    border-color: #8b5cf6;
}
.mf-span2 { grid-column: span 2; }

/* Dropzone */
.mf-dropzone {
    border: 2px dashed var(--theme-border);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
}
.mf-dropzone:hover {
    border-color: #8b5cf6;
    background: rgba(139,92,246,.04);
}
.mf-dropzone .dz-icon { font-size: 2.5rem; margin-bottom: .5rem; }
.mf-dropzone .dz-title {
    font-weight: 600;
    color: var(--theme-text);
    margin: 0;
}
.mf-dropzone .dz-sub {
    font-size: .82rem;
    color: var(--theme-muted, #9ca3af);
    margin: .3rem 0 0;
}

/* Preview grid */
#previewGrid {
    margin-top: 1rem;
    display: none;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: .75rem;
}
.mf-preview-item {
    border-radius: 8px;
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--theme-input, #f8fafc);
}

/* Progreso */
.mf-progress-wrap {
    display: none;
    margin-top: 1rem;
}
.mf-progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: .3rem;
}
.mf-progress-header span:first-child {
    font-size: .85rem;
    color: var(--theme-muted, #6b7280);
    font-weight: 600;
}
.mf-progress-header span:last-child {
    font-size: .85rem;
    color: #8b5cf6;
    font-weight: 700;
}
.mf-progress-track {
    background: var(--theme-input, #f1f5f9);
    border-radius: 999px;
    height: 8px;
    overflow: hidden;
}
.mf-progress-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #8b5cf6, #ec4899);
    border-radius: 999px;
    transition: width .3s;
}

/* Botón subir */
.mf-upload-btn {
    margin-top: 1.25rem;
    padding: .85rem 2rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    font-size: .95rem;
    transition: opacity .2s;
}
.mf-upload-btn:disabled { opacity: .6; cursor: not-allowed; }

/* ── Sección álbum ── */
.mf-album {
    background: var(--theme-card);
    border: 1px solid var(--theme-border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
}
.mf-album-header {
    display: flex;
    align-items: baseline;
    gap: .6rem;
    margin-bottom: 1.25rem;
}
.mf-album-header h2 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
}
.mf-album-count {
    font-size: .8rem;
    color: var(--theme-muted, #9ca3af);
    font-weight: 400;
}
.mf-album-empty {
    color: var(--theme-muted, #9ca3af);
    font-size: .9rem;
    text-align: center;
    padding: 1rem 0;
}

/* Grid de fotos */
.mf-photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: .75rem;
}
.mf-photo-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--theme-input, #f8fafc);
}
.mf-photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Badges */
.mf-badge {
    position: absolute;
    top: .4rem;
    right: .4rem;
    padding: .2rem .5rem;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 700;
    color: white;
    pointer-events: none;
}
.mf-badge-approved  { background: #10b981; }
.mf-badge-rejected  { background: #ef4444; }
.mf-badge-pending   { background: #f59e0b; }
.mf-badge-profile {
    position: absolute;
    top: .4rem;
    left: .4rem;
    padding: .2rem .5rem;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 700;
    color: white;
    background: #8b5cf6;
    pointer-events: none;
}

/* Overlay de acciones */
.mf-actions {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,.72));
    padding: .75rem .5rem .5rem;
    display: flex;
    gap: .4rem;
    justify-content: center;
}
.mf-btn-action {
    border: none;
    border-radius: 6px;
    padding: .3rem .55rem;
    cursor: pointer;
    font-size: .75rem;
    font-weight: 700;
    color: white;
    transition: opacity .2s;
}
.mf-btn-action:hover { opacity: .85; }
.mf-btn-star   { background: #8b5cf6; }
.mf-btn-delete { background: #ef4444; }

/* Overlay nota de rechazo */
.mf-reject-overlay {
    position: absolute;
    inset: 0;
    background: rgba(239,68,68,.9);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: .5rem;
    opacity: 0;
    transition: opacity .2s;
}
.mf-reject-overlay:hover { opacity: 1; }
.mf-reject-overlay p {
    color: white;
    font-size: .75rem;
    text-align: center;
    margin: 0;
}
</style>
@endpush

@section('content')
<div class="mf-wrap">

  {{-- Cabecera --}}
  <div class="mf-header">
    <div>
      <h1>📸 Mis Fotos</h1>
      <p>Gestiona tus álbumes. Las fotos son revisadas antes de publicarse.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="mf-back-btn">← Dashboard</a>
  </div>

  {{-- Alertas --}}
  @if(session('success'))
  <div class="mf-alert-ok">✅ {{ session('success') }}</div>
  @endif
  @if(session('error'))
  <div class="mf-alert-err">⚠️ {{ session('error') }}</div>
  @endif

  {{-- Formulario de subida --}}
  <div class="mf-card">
    <h2>➕ Subir nuevas fotos</h2>

    <form method="POST" action="{{ route('photos.store') }}" enctype="multipart/form-data" id="uploadForm">
      @csrf
      <div class="mf-form-grid">

        <div>
          <label>Álbum</label>
          <select name="album_type">
            <option value="public">🌐 Público</option>
            <option value="private">🔒 Privado</option>
            <option value="vip">👑 VIP</option>
          </select>
        </div>

        <div class="mf-span2">
          <label>Descripción (opcional)</label>
          <input type="text" name="caption" maxlength="200" placeholder="Agrega una descripción...">
        </div>
      </div>

      {{-- Dropzone --}}
      <div class="mf-dropzone" id="dropzone"
           onclick="document.getElementById('photoInput').click()"
           ondragover="event.preventDefault();this.style.borderColor='#8b5cf6'"
           ondragleave="this.style.borderColor=''"
           ondrop="handlePhotoDrop(event)">
        <div id="dropContent">
          <div class="dz-icon">🖼️</div>
          <p class="dz-title">Haz clic o arrastra tus fotos aquí</p>
          <p class="dz-sub">JPG, PNG o WEBP · Máx 10MB por foto · Múltiples fotos permitidas</p>
        </div>
        <input type="file" id="photoInput" name="photos[]" accept="image/jpeg,image/png,image/webp"
               multiple style="display:none;" onchange="previewPhotos(this)">
      </div>

      {{-- Preview --}}
      <div id="previewGrid"></div>

      {{-- Progreso --}}
      <div class="mf-progress-wrap" id="progressContainer">
        <div class="mf-progress-header">
          <span>Subiendo fotos...</span>
          <span id="progressPct">0%</span>
        </div>
        <div class="mf-progress-track">
          <div class="mf-progress-bar" id="progressBar"></div>
        </div>
      </div>

      <button type="submit" class="mf-upload-btn" id="uploadBtn">
        📤 Subir fotos
      </button>
    </form>
  </div>

  {{-- Álbumes --}}
  @foreach([
    'public'  => ['🌐 Álbum Público',  '#8b5cf6'],
    'private' => ['🔒 Álbum Privado',  '#ec4899'],
    'vip'     => ['👑 Álbum VIP',       '#f59e0b'],
  ] as $type => [$label, $color])
  <div class="mf-album">
    <div class="mf-album-header">
      <h2 style="color:{{ $color }};">{{ $label }}</h2>
      <span class="mf-album-count">({{ $grouped[$type]->count() }} fotos)</span>
    </div>

    @if($grouped[$type]->isEmpty())
      <p class="mf-album-empty">No hay fotos en este álbum.</p>
    @else
    <div class="mf-photo-grid">
      @foreach($grouped[$type] as $photo)
      <div class="mf-photo-item">

        <img src="{{ route('photos.serve', $photo->id) }}"
             alt="Foto"
             onerror="this.parentElement.style.background='var(--theme-input,#f1f5f9)';this.remove()">

        {{-- Badge estado --}}
        <div class="mf-badge
          {{ $photo->status === 'approved' ? 'mf-badge-approved' : ($photo->status === 'rejected' ? 'mf-badge-rejected' : 'mf-badge-pending') }}">
          {{ $photo->status === 'approved' ? '✅' : ($photo->status === 'rejected' ? '❌' : '⏳') }}
          {{ ucfirst($photo->status) }}
        </div>

        {{-- Badge perfil --}}
        @if($photo->is_profile_photo)
        <div class="mf-badge-profile">⭐ Perfil</div>
        @endif

        {{-- Acciones --}}
        <div class="mf-actions">
          @if($photo->status === 'approved' && !$photo->is_profile_photo)
          <form method="POST" action="{{ route('photos.profile', $photo->id) }}" style="margin:0;">
            @csrf
            <button type="submit" class="mf-btn-action mf-btn-star" title="Usar como foto de perfil">
              ⭐ Perfil
            </button>
          </form>
          @endif

          <form method="POST" action="{{ route('photos.destroy', $photo->id) }}" style="margin:0;"
                onsubmit="return confirm('¿Eliminar esta foto?')">
            @csrf @method('DELETE')
            <button type="submit" class="mf-btn-action mf-btn-delete" title="Eliminar">
              🗑️
            </button>
          </form>
        </div>

        {{-- Nota rechazo --}}
        @if($photo->status === 'rejected' && $photo->admin_note)
        <div class="mf-reject-overlay">
          <p>{{ $photo->admin_note }}</p>
        </div>
        @endif

      </div>
      @endforeach
    </div>
    @endif
  </div>
  @endforeach

</div>

@push('scripts')
<script>
function previewPhotos(input) {
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';
    grid.style.display = 'grid';

    Array.from(input.files).forEach(function(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'mf-preview-item';
            div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('dropContent').innerHTML =
        '<p class="dz-title" style="color:#10b981;">✅ ' + input.files.length + ' foto(s) seleccionada(s)</p>' +
        '<p class="dz-sub">Haz clic para cambiar</p>';
    document.getElementById('dropzone').style.borderColor = '#10b981';
}

function handlePhotoDrop(e) {
    e.preventDefault();
    const input = document.getElementById('photoInput');
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => {
        if (f.type.startsWith('image/')) dt.items.add(f);
    });
    input.files = dt.files;
    previewPhotos(input);
}

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
        setTimeout(() => {
            window.location.href = '{{ route("photos.index") }}';
        }, 400);
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
@endpush

@endsection
