@extends('layouts.admin')
@php use Illuminate\Support\Facades\Storage; @endphp

@section('title', $article ? 'Editar Artículo' : 'Nuevo Artículo')
@section('page-title', $article ? 'Editar Artículo' : 'Nuevo Artículo')

@section('content')
<div style="max-width:900px;margin:0 auto;">

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.articles.index') }}"
           style="color:var(--theme-muted);font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
            <i class="fas fa-arrow-left"></i> Volver a artículos
        </a>
    </div>

    @if($errors->any())
    <div style="background:#ef444422;border:1px solid #ef4444;color:#ef4444;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.85rem;">
        <i class="fas fa-exclamation-triangle"></i>
        <ul style="margin:.4rem 0 0 1.2rem;padding:0;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $article ? route('admin.articles.update', $article->id) : route('admin.articles.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($article) @method('PUT') @endif

        <div style="display:grid;grid-template-columns:1fr 300px;gap:1rem;align-items:start;">

            {{-- Columna principal --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">

                {{-- Título --}}
                <div class="adm-card" style="padding:1.25rem;">
                    <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                        Título <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title', $article->title ?? '') }}"
                           required placeholder="Título del artículo"
                           style="width:100%;padding:.6rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:1rem;font-weight:600;">
                </div>

                {{-- Extracto --}}
                <div class="adm-card" style="padding:1.25rem;">
                    <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                        Extracto <span style="font-weight:400;">— resumen corto para listados (máx. 500 caracteres)</span>
                    </label>
                    <textarea name="excerpt" rows="2" maxlength="500"
                              placeholder="Breve descripción del artículo que aparece en listados y redes…"
                              style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;resize:vertical;">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>
                </div>

                {{-- Cuerpo --}}
                <div class="adm-card" style="padding:1.25rem;">
                    <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                        Contenido <span style="color:#ef4444;">*</span>
                    </label>
                    <textarea name="body" id="bodyEditor" rows="18" required
                              placeholder="Escribe el contenido completo del artículo…"
                              style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;resize:vertical;line-height:1.7;font-family:inherit;">{{ old('body', $article->body ?? '') }}</textarea>
                    <div style="font-size:.72rem;color:var(--theme-muted);margin-top:.3rem;">
                        <span id="charCount">0</span> caracteres
                    </div>
                </div>

            </div>

            {{-- Columna lateral --}}
            <div style="display:flex;flex-direction:column;gap:1rem;">

                {{-- Publicación --}}
                <div class="adm-card" style="padding:1.25rem;">
                    <h4 style="font-size:.82rem;font-weight:700;color:var(--theme-text);margin:0 0 .9rem;">
                        <i class="fas fa-cog" style="color:var(--theme-muted);"></i> Publicación
                    </h4>
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;color:var(--theme-text);margin-bottom:.75rem;">
                        <input type="checkbox" name="published" value="1"
                               {{ old('published', $article->published ?? false) ? 'checked' : '' }}
                               style="width:16px;height:16px;accent-color:#22c55e;">
                        <i class="fas fa-eye" style="color:#22c55e;"></i> Publicar artículo
                    </label>
                    @if(isset($article->published_at) && $article->published_at)
                    <div style="font-size:.72rem;color:var(--theme-muted);">
                        Publicado: {{ \Carbon\Carbon::parse($article->published_at)->format('d/m/Y H:i') }}
                    </div>
                    @endif
                    <div style="border-top:1px solid var(--theme-border);padding-top:.75rem;margin-top:.75rem;display:flex;flex-direction:column;gap:.5rem;">
                        <button type="submit"
                                style="width:100%;padding:.5rem;background:var(--theme-accent);color:#fff;border:none;border-radius:8px;font-size:.85rem;cursor:pointer;font-weight:600;">
                            <i class="fas fa-save"></i> {{ $article ? 'Guardar cambios' : 'Crear artículo' }}
                        </button>
                        <a href="{{ route('admin.articles.index') }}"
                           style="display:block;text-align:center;padding:.45rem;border:1px solid var(--theme-border);color:var(--theme-muted);border-radius:8px;font-size:.82rem;text-decoration:none;">
                            Cancelar
                        </a>
                    </div>
                </div>

                {{-- Categoría --}}
                <div class="adm-card" style="padding:1.25rem;">
                    <h4 style="font-size:.82rem;font-weight:700;color:var(--theme-text);margin:0 0 .75rem;">
                        <i class="fas fa-tag" style="color:var(--theme-muted);"></i> Categoría
                    </h4>
                    <select name="category"
                            style="width:100%;padding:.45rem .75rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.85rem;">
                        <option value="">Sin categoría</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $article->category ?? '') === $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Imagen de portada --}}
                <div class="adm-card" style="padding:1.25rem;">
                    <h4 style="font-size:.82rem;font-weight:700;color:var(--theme-text);margin:0 0 .75rem;">
                        <i class="fas fa-image" style="color:var(--theme-muted);"></i> Imagen de portada
                        <span style="font-weight:400;font-size:.72rem;"> — opcional</span>
                    </h4>

                    @if(isset($article->cover_path) && $article->cover_path)
                    <div style="margin-bottom:.75rem;">
                        <img src="{{ Storage::url($article->cover_path) }}"
                             style="width:100%;border-radius:6px;object-fit:cover;max-height:140px;">
                        <label style="display:flex;align-items:center;gap:.4rem;margin-top:.4rem;font-size:.75rem;color:#ef4444;cursor:pointer;">
                            <input type="checkbox" name="remove_cover" value="1" style="accent-color:#ef4444;">
                            Eliminar imagen actual
                        </label>
                    </div>
                    @endif

                    <div style="border:2px dashed var(--theme-border);border-radius:8px;padding:1rem;text-align:center;position:relative;cursor:pointer;"
                         id="coverDropZone">
                        <input type="file" name="cover" id="coverInput"
                               accept="image/jpg,image/jpeg,image/png,image/webp"
                               style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.4rem;color:var(--theme-muted);margin-bottom:.3rem;display:block;"></i>
                        <p style="font-size:.75rem;color:var(--theme-muted);margin:0;">
                            JPG, PNG, WEBP · máx. 4MB
                        </p>
                        <div id="coverFileName" style="margin-top:.3rem;font-size:.75rem;color:var(--theme-accent);display:none;"></div>
                    </div>
                    <div id="coverPreview" style="display:none;margin-top:.5rem;">
                        <img id="coverPreviewImg" style="width:100%;border-radius:6px;object-fit:cover;max-height:140px;">
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Contador de caracteres
const bodyEl = document.getElementById('bodyEditor');
const counter = document.getElementById('charCount');
function updateCount() { counter.textContent = bodyEl.value.length.toLocaleString(); }
bodyEl.addEventListener('input', updateCount);
updateCount();

// Preview portada
document.getElementById('coverInput').addEventListener('change', function() {
    const file    = this.files[0];
    const display = document.getElementById('coverFileName');
    const preview = document.getElementById('coverPreview');
    const img     = document.getElementById('coverPreviewImg');
    if (file) {
        display.textContent = '✓ ' + file.name;
        display.style.display = 'block';
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(file);
    }
});

// Highlight dropzone
const dz = document.getElementById('coverDropZone');
dz.addEventListener('dragover',  e => { e.preventDefault(); dz.style.borderColor = 'var(--theme-accent)'; });
dz.addEventListener('dragleave', ()  => { dz.style.borderColor = 'var(--theme-border)'; });
dz.addEventListener('drop',      ()  => { dz.style.borderColor = 'var(--theme-border)'; });
</script>
@endpush
