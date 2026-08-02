@extends('layouts.app')
@section('title', 'Mis Videos — LOBBY69')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@push('styles')

<style>
.mv-wrap {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}
.mv-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.mv-header h1 { font-size: 1.6rem; font-weight: 800; color: var(--theme-text); margin: 0; }
.mv-header p  { color: var(--theme-muted, #9ca3af); margin: .25rem 0 0; font-size: .9rem; }
.mv-back-btn {
    padding: .6rem 1rem;
    border: 1px solid var(--theme-border);
    border-radius: 8px;
    font-size: .9rem;
    color: var(--theme-muted, #6b7280);
    text-decoration: none;
    background: transparent;
    transition: all .2s;
}
.mv-back-btn:hover { color: var(--theme-text); border-color: var(--theme-text); }

.mv-alert-ok {
    background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46;
    padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: .9rem;
}
.mv-alert-err {
    background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;
    padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: .9rem;
}
[data-theme="dark"] .mv-alert-ok  { background: #064e3b; border-color: #059669; color: #a7f3d0; }
[data-theme="dark"] .mv-alert-err { background: #450a0a; border-color: #dc2626; color: #fca5a5; }

.mv-card {
    background: var(--theme-card);
    border: 1px solid var(--theme-border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
}
.mv-card h2 { font-size: 1.1rem; font-weight: 700; margin: 0 0 1.25rem; color: var(--theme-text); }

.mv-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}
.mv-form-grid label {
    display: block; font-weight: 600;
    font-size: .85rem; color: var(--theme-text); margin-bottom: .4rem;
}
.mv-form-grid select,
.mv-form-grid input[type="text"] {
    width: 100%; padding: .65rem .9rem;
    border: 2px solid var(--theme-border); border-radius: 8px;
    font-size: .9rem; background: var(--theme-input, var(--theme-card));
    color: var(--theme-text); box-sizing: border-box; transition: border-color .2s;
}
.mv-form-grid select:focus,
.mv-form-grid input[type="text"]:focus { outline: none; border-color: #8b5cf6; }
.mv-span2 { grid-column: span 2; }

.mv-dropzone {
    border: 2px dashed var(--theme-border);
    border-radius: 12px; padding: 2.5rem 2rem;
    text-align: center; cursor: pointer; transition: all .2s;
}
.mv-dropzone:hover { border-color: #8b5cf6; background: rgba(139,92,246,.04); }
.mv-dropzone .dz-icon  { font-size: 3rem; margin-bottom: .5rem; }
.mv-dropzone .dz-title { font-weight: 600; color: var(--theme-text); margin: 0; }
.mv-dropzone .dz-sub   { font-size: .82rem; color: var(--theme-muted, #9ca3af); margin: .3rem 0 0; }
.mv-dz-limits {
    display: flex; justify-content: center;
    gap: 1.5rem; margin-top: .75rem; flex-wrap: wrap;
}
.mv-dz-limit {
    font-size: .78rem; color: var(--theme-muted, #9ca3af);
    background: var(--theme-input, rgba(128,128,128,.08));
    border: 1px solid var(--theme-border);
    border-radius: 20px; padding: .2rem .75rem;
}

/* Progreso */
.mv-progress-wrap  { display: none; margin-top: 1rem; }
.mv-progress-head  { display: flex; justify-content: space-between; margin-bottom: .3rem; }
.mv-progress-head span:first-child { font-size: .85rem; color: var(--theme-muted); font-weight: 600; }
.mv-progress-head span:last-child  { font-size: .85rem; color: #8b5cf6; font-weight: 700; }
.mv-progress-track { background: var(--theme-input, #f1f5f9); border-radius: 999px; height: 8px; overflow: hidden; }
.mv-progress-bar   { height: 100%; width: 0%; background: linear-gradient(90deg,#8b5cf6,#ec4899); border-radius: 999px; transition: width .3s; }

.mv-upload-btn {
    margin-top: 1.25rem; padding: .85rem 2rem;
    background: linear-gradient(135deg,#8b5cf6,#ec4899);
    color: white; border: none; border-radius: 10px;
    font-weight: 700; cursor: pointer; font-size: .95rem; transition: opacity .2s;
}
.mv-upload-btn:disabled { opacity: .6; cursor: not-allowed; }

/* Álbumes */
.mv-album {
    background: var(--theme-card);
    border: 1px solid var(--theme-border);
    border-radius: 16px; padding: 2rem; margin-bottom: 1.5rem;
}
.mv-album-header { display: flex; align-items: baseline; gap: .6rem; margin-bottom: 1.25rem; }
.mv-album-header h2 { font-size: 1rem; font-weight: 700; margin: 0; }
.mv-album-count { font-size: .8rem; color: var(--theme-muted, #9ca3af); }
.mv-album-empty { color: var(--theme-muted, #9ca3af); font-size: .9rem; text-align: center; padding: 1rem 0; }

/* Grid de videos */
.mv-video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.mv-video-item {
    background: var(--theme-input, #0f0a1a);
    border: 1px solid var(--theme-border);
    border-radius: 12px; overflow: hidden;
    position: relative;
}
.mv-video-player {
    width: 100%; aspect-ratio: 16/9;
    background: #000; display: block;
    object-fit: cover;
}
.mv-video-info {
    padding: .65rem .75rem;
}
.mv-video-caption {
    font-size: .83rem; color: var(--theme-text);
    margin: 0 0 .35rem; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.mv-video-meta {
    display: flex; align-items: center;
    justify-content: space-between; gap: .5rem;
}
.mv-badge-status {
    font-size: .7rem; font-weight: 700;
    padding: .2rem .55rem; border-radius: 20px; color: white;
    white-space: nowrap;
}
.mv-badge-approved { background: #10b981; }
.mv-badge-rejected { background: #ef4444; }
.mv-badge-pending  { background: #f59e0b; }
.mv-video-views    { font-size: .75rem; color: var(--theme-muted, #9ca3af); }

.mv-video-actions {
    display: flex; gap: .4rem;
    padding: 0 .75rem .65rem;
}
.mv-btn-del {
    flex: 1; padding: .35rem; background: #ef4444;
    color: white; border: none; border-radius: 6px;
    font-size: .78rem; font-weight: 700;
    cursor: pointer; transition: opacity .2s;
}
.mv-btn-del:hover { opacity: .85; }

.mv-admin-note {
    font-size: .75rem; color: #ef4444;
    padding: 0 .75rem .5rem;
    line-height: 1.4;
}
</style>
@endpush

@section('content')
<div class="mv-wrap">

    {{-- Cabecera --}}
    <div class="mv-header">
        <div>
            <h1>🎬 Mis Videos</h1>
            <p>Sube y gestiona tus videos. Son revisados antes de publicarse.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="mv-back-btn">← Dashboard</a>
    </div>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="mv-alert-ok">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mv-alert-err">⚠️ {{ session('error') }}</div>
    @endif

    {{-- Formulario de subida --}}
    <div class="mv-card">
        <h2>➕ Subir nuevo video</h2>

        <form method="POST" action="{{ route('videos.store') }}"
              enctype="multipart/form-data" id="uploadVideoForm">
            @csrf
            <div class="mv-form-grid">
                <div>
                    <label>Álbum</label>
                    <select name="album_type">
                        <option value="public">🌐 Público</option>
                        <option value="private">🔒 Privado</option>
                        <option value="vip">👑 VIP</option>
                    </select>
                </div>
                <div class="mv-span2">
                    <label>Descripción (opcional)</label>
                    <input type="text" name="caption" maxlength="200"
                           placeholder="Agrega una descripción...">
                </div>
            </div>

            {{-- Dropzone --}}
            <div class="mv-dropzone" id="videoDropzone"
                 onclick="document.getElementById('videoInput').click()"
                 ondragover="event.preventDefault();this.style.borderColor='#8b5cf6'"
                 ondragleave="this.style.borderColor=''">
                <div id="videoDropContent">
                    <div class="dz-icon">🎬</div>
                    <p class="dz-title">Haz clic o arrastra tu video aquí</p>
                    <p class="dz-sub">MP4, MOV, AVI o WEBM</p>
                    <div class="mv-dz-limits">
                        <span class="mv-dz-limit">⏱ Mín. 30 seg</span>
                        <span class="mv-dz-limit">⏱ Máx. 5 min</span>
                        <span class="mv-dz-limit">💾 Máx. 100 MB</span>
                    </div>
                </div>
                <input type="file" id="videoInput" name="video"
                       accept="video/mp4,video/quicktime,video/avi,video/webm"
                       style="display:none;" onchange="previewVideo(this)">
            </div>

            {{-- Progreso --}}
            <div class="mv-progress-wrap" id="videoProgressWrap">
                <div class="mv-progress-head">
                    <span>Subiendo video...</span>
                    <span id="videoPct">0%</span>
                </div>
                <div class="mv-progress-track">
                    <div class="mv-progress-bar" id="videoProgressBar"></div>
                </div>
            </div>

            <button type="submit" class="mv-upload-btn" id="videoUploadBtn">
                📤 Subir video
            </button>
        </form>
    </div>

    {{-- Álbumes --}}
    @foreach([
        'public'  => ['🌐 Álbum Público',  '#8b5cf6'],
        'private' => ['🔒 Álbum Privado',  '#ec4899'],
        'vip'     => ['👑 Álbum VIP',       '#f59e0b'],
    ] as $type => [$label, $color])
    <div class="mv-album">
        <div class="mv-album-header">
            <h2 style="color:{{ $color }};">{{ $label }}</h2>
            <span class="mv-album-count">({{ $grouped[$type]->count() }} videos)</span>
        </div>

        @if($grouped[$type]->isEmpty())
            <p class="mv-album-empty">No hay videos en este álbum.</p>
        @else
        <div class="mv-video-grid">
            @foreach($grouped[$type] as $video)
            <div class="mv-video-item">

                {{-- Reproductor --}}
                @if($video->status === 'approved')
                <video class="mv-video-player" controls preload="metadata"
                       route('videos.serve.public', $video->id)
                    Tu navegador no soporta video HTML5.
                </video>
                @else
                <div style="aspect-ratio:16/9;background:#0f0a1a;display:flex;
                            align-items:center;justify-content:center;">
                    <span style="font-size:2rem;">
                        {{ $video->status === 'pending' ? '⏳' : '❌' }}
                    </span>
                </div>
                @endif

                <div class="mv-video-info">
                    @if($video->caption)
                    <p class="mv-video-caption">{{ $video->caption }}</p>
                    @endif
                    <div class="mv-video-meta">
                        <span class="mv-badge-status
                            {{ $video->status === 'approved' ? 'mv-badge-approved'
                             : ($video->status === 'rejected' ? 'mv-badge-rejected'
                             : 'mv-badge-pending') }}">
                            {{ $video->status === 'approved' ? '✅ Aprobado'
                             : ($video->status === 'rejected' ? '❌ Rechazado'
                             : '⏳ En revisión') }}
                        </span>
                        @if($video->status === 'approved')
                        <span class="mv-video-views">
                            👁 {{ $video->views_count }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Nota de rechazo --}}
                @if($video->status === 'rejected' && $video->admin_note)
                <p class="mv-admin-note">📝 {{ $video->admin_note }}</p>
                @endif

                {{-- Acciones --}}
                <div class="mv-video-actions">
                    <form method="POST"
                          action="{{ route('videos.destroy', $video->id) }}"
                          onsubmit="return confirm('¿Eliminar este video?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="mv-btn-del">🗑️ Eliminar</button>
                    </form>
                </div>

            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach

</div>
@endsection

@push('scripts')
<script>
function previewVideo(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];

    // Validar tamaño
    if (file.size > 104857600) {
        alert('El video supera el límite de 100MB.');
        input.value = '';
        return;
    }

    // Validar duración
    var url = URL.createObjectURL(file);
    var tmp = document.createElement('video');
    tmp.preload = 'metadata';
    tmp.src = url;
    tmp.onloadedmetadata = function() {
        URL.revokeObjectURL(url);
        var dur = Math.round(tmp.duration);
        if (dur < 30) {
            alert('El video debe durar al menos 30 segundos. Este dura ' + dur + ' seg.');
            input.value = '';
            document.getElementById('videoDropContent').innerHTML =
                '<div class="dz-icon">🎬</div>' +
                '<p class="dz-title">Haz clic o arrastra tu video aquí</p>' +
                '<p class="dz-sub">MP4, MOV, AVI o WEBM</p>';
            return;
        }
        if (dur > 300) {
            alert('El video no puede superar 5 minutos. Este dura ' + Math.round(dur/60) + ' min.');
            input.value = '';
            return;
        }

        var mins = Math.floor(dur / 60);
        var secs = dur % 60;
        var mb   = (file.size / 1048576).toFixed(1);

        document.getElementById('videoDropContent').innerHTML =
            '<p class="dz-title" style="color:#10b981;">✅ ' + file.name + '</p>' +
            '<p class="dz-sub">⏱ ' + mins + ':' + String(secs).padStart(2,'0') +
            ' min &nbsp;·&nbsp; 💾 ' + mb + ' MB</p>';
        document.getElementById('videoDropzone').style.borderColor = '#10b981';
    };
}

document.getElementById('uploadVideoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var input = document.getElementById('videoInput');
    if (!input.files || input.files.length === 0) {
        alert('Selecciona un video.');
        return;
    }

    var btn = document.getElementById('videoUploadBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Subiendo...';
    document.getElementById('videoProgressWrap').style.display = 'block';

    var formData = new FormData(this);
    var xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            var pct = Math.round((e.loaded / e.total) * 95);
            document.getElementById('videoProgressBar').style.width = pct + '%';
            document.getElementById('videoPct').textContent = pct + '%';
        }
    });

    xhr.addEventListener('load', function() {
        document.getElementById('videoProgressBar').style.width = '100%';
        document.getElementById('videoPct').textContent = '100%';
        setTimeout(function() {
            window.location.href = '{{ route("videos.index") }}';
        }, 400);
    });

    xhr.addEventListener('error', function() {
        btn.disabled = false;
        btn.innerHTML = '📤 Subir video';
        alert('Error de conexión. Intenta de nuevo.');
    });

    xhr.open('POST', this.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});
</script>
@endpush
