@extends('layouts.admin')

@section('title', $event ? 'Editar Evento' : 'Nuevo Evento')
@section('page-title', $event ? 'Editar Evento' : 'Nuevo Evento')

@section('content')
<div style="max-width:760px;margin:0 auto;">

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('admin.events.index') }}"
           style="color:var(--theme-muted);font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
            <i class="fas fa-arrow-left"></i> Volver a eventos
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

    <div class="adm-card" style="padding:1.75rem;">
        <form method="POST"
              action="{{ $event ? route('admin.events.update', $event->id) : route('admin.events.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if($event) @method('PUT') @endif

            {{-- Título --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                    Título <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" name="title"
                       value="{{ old('title', $event->title ?? '') }}"
                       required placeholder="Nombre del evento"
                       style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.9rem;">
            </div>

            {{-- Descripción --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                    Descripción
                </label>
                <p style="font-size:.72rem;color:var(--theme-muted);margin-bottom:.4rem;">
                    En este espacio se declara la información completa del evento: horarios, requisitos, dress code, etc.
                </p>
                <textarea name="description" rows="5"
                          placeholder="Describe el evento con todos los detalles relevantes para los asistentes…"
                          style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;resize:vertical;line-height:1.6;">{{ old('description', $event->description ?? '') }}</textarea>
            </div>

            {{-- Fecha inicio y fin --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                        Fecha y hora de inicio <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="datetime-local" name="starts_at"
                           value="{{ old('starts_at', isset($event->starts_at) ? \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d\TH:i') : '') }}"
                           required
                           style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;">
                </div>
                <div>
                    <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                        Fecha y hora de fin
                    </label>
                    <input type="datetime-local" name="ends_at"
                           value="{{ old('ends_at', isset($event->ends_at) ? \Carbon\Carbon::parse($event->ends_at)->format('Y-m-d\TH:i') : '') }}"
                           style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;">
                </div>
            </div>

            {{-- Dirección --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                    Dirección
                </label>
                <input type="text" name="address"
                       value="{{ old('address', $event->address ?? '') }}"
                       placeholder="Calle, número, colonia, ciudad, estado…"
                       style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;">
            </div>

            {{-- Organizado por --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                    Organizado por
                </label>
                <input type="text" name="organized_by"
                       value="{{ old('organized_by', $event->organized_by ?? '') }}"
                       placeholder="Nombre del organizador o empresa"
                       style="width:100%;padding:.55rem .85rem;border-radius:8px;border:1px solid var(--theme-border);background:var(--theme-bg);color:var(--theme-text);font-size:.88rem;">
            </div>

            {{-- Imagen --}}
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.8rem;font-weight:600;color:var(--theme-muted);margin-bottom:.4rem;">
                    Imagen del evento
                    <span style="font-weight:400;"> — opcional, máx. 4MB (jpg, png, webp)</span>
                </label>

                {{-- Preview imagen existente --}}
                @if(isset($event->image_path) && $event->image_path)
                <div id="currentImage" style="margin-bottom:.75rem;position:relative;display:inline-block;">
                    <img src="{{ Storage::url($event->image_path) }}"
                         style="max-height:180px;border-radius:8px;object-fit:cover;max-width:100%;">
                    <label style="display:flex;align-items:center;gap:.4rem;margin-top:.4rem;font-size:.78rem;color:#ef4444;cursor:pointer;">
                        <input type="checkbox" name="remove_image" value="1"
                               style="accent-color:#ef4444;">
                        Eliminar imagen actual
                    </label>
                </div>
                @endif

                <div style="border:2px dashed var(--theme-border);border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;position:relative;"
                     id="dropZone">
                    <input type="file" name="image" id="imageInput"
                           accept="image/jpg,image/jpeg,image/png,image/webp"
                           style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                    <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem;color:var(--theme-muted);margin-bottom:.5rem;display:block;"></i>
                    <p style="font-size:.82rem;color:var(--theme-muted);margin:0;">
                        Arrastra una imagen aquí o <span style="color:var(--theme-accent);">haz clic para seleccionar</span>
                    </p>
                    <div id="fileNameDisplay" style="margin-top:.5rem;font-size:.78rem;color:var(--theme-accent);display:none;"></div>
                </div>

                {{-- Preview nueva imagen --}}
                <div id="newImagePreview" style="display:none;margin-top:.75rem;">
                    <img id="newPreviewImg" style="max-height:180px;border-radius:8px;object-fit:cover;max-width:100%;">
                </div>
            </div>

            {{-- Checkboxes --}}
            <div style="display:flex;gap:2rem;margin-bottom:1.75rem;padding:.9rem;background:var(--theme-bg);border-radius:8px;border:1px solid var(--theme-border);">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;color:var(--theme-text);">
                    <input type="checkbox" name="is_online" value="1"
                           {{ old('is_online', $event->is_online ?? false) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--theme-accent);">
                    <i class="fas fa-wifi" style="color:#06b6d4;"></i> Evento online
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;color:var(--theme-text);">
                    <input type="checkbox" name="is_published" value="1"
                           {{ old('is_published', $event->is_published ?? false) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--theme-accent);">
                    <i class="fas fa-eye" style="color:#22c55e;"></i> Publicar inmediatamente
                </label>
            </div>

            {{-- Botones --}}
            <div style="display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid var(--theme-border);padding-top:1.25rem;">
                <a href="{{ route('admin.events.index') }}"
                   style="padding:.5rem 1.2rem;border:1px solid var(--theme-border);color:var(--theme-muted);border-radius:8px;text-decoration:none;font-size:.85rem;">
                    Cancelar
                </a>
                <button type="submit"
                        style="padding:.5rem 1.5rem;background:var(--theme-accent);color:#fff;border:none;border-radius:8px;font-size:.85rem;cursor:pointer;font-weight:600;">
                    <i class="fas fa-save"></i> {{ $event ? 'Guardar cambios' : 'Crear evento' }}
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('imageInput').addEventListener('change', function() {
    const file    = this.files[0];
    const display = document.getElementById('fileNameDisplay');
    const preview = document.getElementById('newImagePreview');
    const img     = document.getElementById('newPreviewImg');

    if (file) {
        display.textContent = '✓ ' + file.name;
        display.style.display = 'block';
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        display.style.display  = 'none';
        preview.style.display  = 'none';
    }
});

// Highlight dropzone al arrastrar
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover',  e => { e.preventDefault(); dz.style.borderColor = 'var(--theme-accent)'; });
dz.addEventListener('dragleave', ()  => { dz.style.borderColor = 'var(--theme-border)'; });
dz.addEventListener('drop',      e => { dz.style.borderColor = 'var(--theme-border)'; });
</script>
@endpush
