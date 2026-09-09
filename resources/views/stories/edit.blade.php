@extends('layouts.app')

@section('title', 'Editar: ' . $story->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stories.css') }}">
<link rel="stylesheet" href="{{ asset('css/stories-editor.css') }}">
@endpush

@section('content')
<div class="l69-layout stories-layout">

    {{-- COLUMNA IZQUIERDA --}}
    <aside class="l69-sidebar--left stories-sidebar-left">
        <div class="stories-editor-tips">
            <h4 class="stories-card-title">💡 Consejos</h4>
            <ul class="stories-tips-list">
                <li>Revisa que el título siga siendo descriptivo.</li>
                <li>Actualiza el contenido sin perder el hilo.</li>
                <li>Puedes cambiar la categoría si es necesario.</li>
                <li>Guarda como borrador si no está listo aún.</li>
                <li>Una buena portada aumenta las lecturas.</li>
            </ul>
        </div>
        <div class="stories-categories-card">
            <h4 class="stories-card-title">📂 Categorías</h4>
            <ul class="stories-cat-list">
                @foreach($categories as $key => $label)
                <li><span class="stories-cat-link stories-cat--{{ $key }}">{{ $label }}</span></li>
                @endforeach
            </ul>
        </div>
    </aside>

    {{-- COLUMNA CENTRAL --}}
    <main class="l69-layout__content stories-main">
        <div class="stories-editor-wrapper">
            <div class="stories-editor-header">
                <h2>✏️ Editar historia</h2>
                <a href="{{ route('stories.show', $story->slug) }}" class="stories-btn-back">← Volver</a>
            </div>

            <form action="{{ route('stories.update', $story) }}" method="POST"
                  enctype="multipart/form-data" id="storyForm">
                @csrf
                @method('PUT')

                {{-- Título --}}
                <div class="stories-form-group">
                    <input type="text" name="title" id="storyTitle"
                           class="stories-title-input @error('title') is-invalid @enderror"
                           placeholder="Título de tu historia..."
                           value="{{ old('title', $story->title) }}" required>
                    @error('title')
                    <span class="stories-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Metadatos --}}
                <div class="stories-form-row">
                    <div class="stories-form-group">
                        <label class="stories-label">Categoría</label>
                        <select name="category" class="stories-select">
                            @foreach($categories as $key => $label)
                            <option value="{{ $key }}"
                                {{ old('category', $story->category) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stories-form-group">
                        <label class="stories-label">Imagen de portada</label>
                        <label class="stories-cover-upload" id="coverLabel">
                            <span id="coverText">
                                {{ $story->cover_image ? '✅ Portada actual' : '📷 Seleccionar imagen' }}
                            </span>
                            <input type="file" name="cover_image" id="coverInput"
                                   accept="image/*" style="display:none">
                        </label>
                        @if($story->cover_image)
                        <img id="coverPreview"
                             class="stories-cover-preview"
                             src="{{ Storage::url($story->cover_image) }}"
                             alt="Portada actual"
                             style="display:block">
                        @else
                        <img id="coverPreview" class="stories-cover-preview" src="" alt="" style="display:none">
                        @endif
                    </div>
                </div>

                {{-- Barra de herramientas --}}
                <div class="stories-toolbar" id="storyToolbar">
                    <div class="stories-toolbar__group">
                        <button type="button" data-cmd="bold"      title="Negrita"><b>B</b></button>
                        <button type="button" data-cmd="italic"    title="Cursiva"><i>I</i></button>
                        <button type="button" data-cmd="strike"    title="Tachado"><s>S</s></button>
                        <button type="button" data-cmd="underline" title="Subrayado"><u>U</u></button>
                    </div>
                    <div class="stories-toolbar__divider"></div>
                    <div class="stories-toolbar__group">
                        <button type="button" data-cmd="h1">H1</button>
                        <button type="button" data-cmd="h2">H2</button>
                        <button type="button" data-cmd="h3">H3</button>
                    </div>
                    <div class="stories-toolbar__divider"></div>
                    <div class="stories-toolbar__group">
                        <button type="button" data-cmd="bulletList">≡</button>
                        <button type="button" data-cmd="orderedList">1.</button>
                        <button type="button" data-cmd="blockquote">"</button>
                        <button type="button" data-cmd="horizontalRule">—</button>
                    </div>
                    <div class="stories-toolbar__divider"></div>
                    <div class="stories-toolbar__group">
                        <button type="button" data-cmd="undo">↩</button>
                        <button type="button" data-cmd="redo">↪</button>
                    </div>
                    <div class="stories-toolbar__divider"></div>
                    <div class="stories-toolbar__group">
                        <button type="button" id="emojiBtn">😊</button>
                    </div>
                </div>

                {{-- Emoji picker --}}
                <div id="emojiPicker" class="stories-emoji-picker" style="display:none">
                    @php
                    $emojis = ['😀','😂','😍','🥰','😎','🤔','😭','🤩','🥳','😏',
                               '❤️','💔','💕','✨','🔥','💫','⭐','🌙','🌸','🌹',
                               '👏','🙌','💪','🤝','✍️','📖','📝','💡','🎭','🎪',
                               '🌟','💎','🏆','🎯','🚀','💻','🎨','🎵','🌈','🦋'];
                    @endphp
                    @foreach($emojis as $emoji)
                    <button type="button" class="stories-emoji-btn" data-emoji="{{ $emoji }}">
                        {{ $emoji }}
                    </button>
                    @endforeach
                </div>

                {{-- Editor --}}
                <div id="storyEditor" class="stories-tiptap-editor"></div>
                <input type="hidden" name="content" id="storyContent">
                @error('content')
                <span class="stories-error">{{ $message }}</span>
                @enderror

                {{-- Contador --}}
                <div class="stories-word-count">
                    <span id="wordCount">0</span> palabras ·
                    <span id="readTime">1</span> min de lectura
                </div>

                {{-- Acciones --}}
                <div class="stories-form-actions">
                    <button type="submit" name="status" value="draft"
                            class="stories-btn-draft">
                        💾 Guardar borrador
                    </button>
                    <button type="submit" name="status" value="published"
                            class="stories-btn-publish">
                        🚀 Actualizar y publicar
                    </button>
                </div>
            </form>
        </div>
    </main>

    {{-- COLUMNA DERECHA --}}
    <aside class="l69-sidebar--right stories-sidebar-right">
        <div class="stories-widget">
            <h4 class="stories-widget__title">📊 Progreso</h4>
            <div class="stories-progress">
                <div class="stories-progress__item">
                    <span class="stories-progress__label">Palabras</span>
                    <span class="stories-progress__val" id="sidebarWords">0</span>
                </div>
                <div class="stories-progress__item">
                    <span class="stories-progress__label">Caracteres</span>
                    <span class="stories-progress__val" id="sidebarChars">0</span>
                </div>
                <div class="stories-progress__item">
                    <span class="stories-progress__label">Tiempo de lectura</span>
                    <span class="stories-progress__val" id="sidebarTime">~1 min</span>
                </div>
            </div>
            <div class="stories-progress-bar-wrap">
                <div class="stories-progress-bar" id="progressBar" style="width:0%"></div>
            </div>
            <span class="stories-progress-hint">Meta sugerida: 500 palabras</span>
        </div>

        <div class="stories-widget">
            <h4 class="stories-widget__title">📋 Estado actual</h4>
            <div class="stories-detail-item">
                <span>Estado</span>
                <span class="{{ $story->status === 'published' ? 'stories-cat--aventura' : 'stories-cat--general' }}">
                    {{ $story->status === 'published' ? '✅ Publicada' : '📝 Borrador' }}
                </span>
            </div>
            <div class="stories-detail-item">
                <span>👁 Lecturas</span>
                <span>{{ number_format($story->views) }}</span>
            </div>
            <div class="stories-detail-item">
                <span>📅 Creada</span>
                <span>{{ $story->created_at->format('d M Y') }}</span>
            </div>
        </div>

        <div class="stories-widget">
            <h4 class="stories-widget__title">⌨️ Atajos</h4>
            <div class="stories-shortcuts">
                <span><kbd>Ctrl</kbd>+<kbd>B</kbd></span><span>Negrita</span>
                <span><kbd>Ctrl</kbd>+<kbd>I</kbd></span><span>Cursiva</span>
                <span><kbd>Ctrl</kbd>+<kbd>Z</kbd></span><span>Deshacer</span>
                <span><kbd>Ctrl</kbd>+<kbd>Y</kbd></span><span>Rehacer</span>
            </div>
        </div>
    </aside>

</div>
@endsection

@push('scripts')
<script type="module">
import { Editor } from 'https://esm.sh/@tiptap/core@2.11.7'
import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2.11.7'
import Underline from 'https://esm.sh/@tiptap/extension-underline@2.11.7'

const editor = new Editor({
    element: document.querySelector('#storyEditor'),
    extensions: [StarterKit, Underline],
    content: `{!! json_encode(old('content', $story->content)) !!}`,
    editorProps: {
        attributes: { class: 'stories-tiptap-area' }
    },
    onUpdate({ editor }) { updateContent(editor); },
    onSelectionUpdate({ editor }) { updateToolbar(editor); },
});

function updateContent(ed) {
    const html  = ed.getHTML();
    document.getElementById('storyContent').value = html;
    const text  = ed.getText();
    const words = text.trim() ? text.trim().split(/\s+/).length : 0;
    const chars = text.length;
    const time  = Math.max(1, Math.ceil(words / 200));
    const pct   = Math.min(100, Math.round((words / 500) * 100));
    document.getElementById('wordCount').textContent    = words;
    document.getElementById('readTime').textContent     = time;
    document.getElementById('sidebarWords').textContent = words;
    document.getElementById('sidebarChars').textContent = chars;
    document.getElementById('sidebarTime').textContent  = `~${time} min`;
    document.getElementById('progressBar').style.width  = pct + '%';
}

const cmdMap = {
    bold:          () => editor.chain().focus().toggleBold().run(),
    italic:        () => editor.chain().focus().toggleItalic().run(),
    strike:        () => editor.chain().focus().toggleStrike().run(),
    underline:     () => editor.chain().focus().toggleUnderline().run(),
    h1:            () => editor.chain().focus().toggleHeading({ level: 1 }).run(),
    h2:            () => editor.chain().focus().toggleHeading({ level: 2 }).run(),
    h3:            () => editor.chain().focus().toggleHeading({ level: 3 }).run(),
    bulletList:    () => editor.chain().focus().toggleBulletList().run(),
    orderedList:   () => editor.chain().focus().toggleOrderedList().run(),
    blockquote:    () => editor.chain().focus().toggleBlockquote().run(),
    horizontalRule:() => editor.chain().focus().setHorizontalRule().run(),
    undo:          () => editor.chain().focus().undo().run(),
    redo:          () => editor.chain().focus().redo().run(),
};

document.querySelectorAll('[data-cmd]').forEach(btn => {
    btn.addEventListener('click', () => {
        if (cmdMap[btn.dataset.cmd]) cmdMap[btn.dataset.cmd]();
        updateToolbar(editor);
    });
});

function updateToolbar(ed) {
    const activeMap = {
        bold: ed.isActive('bold'), italic: ed.isActive('italic'),
        strike: ed.isActive('strike'), underline: ed.isActive('underline'),
        h1: ed.isActive('heading', { level: 1 }), h2: ed.isActive('heading', { level: 2 }),
        h3: ed.isActive('heading', { level: 3 }), bulletList: ed.isActive('bulletList'),
        orderedList: ed.isActive('orderedList'), blockquote: ed.isActive('blockquote'),
    };
    document.querySelectorAll('[data-cmd]').forEach(btn => {
        btn.classList.toggle('is-active', !!activeMap[btn.dataset.cmd]);
    });
}

const emojiBtn    = document.getElementById('emojiBtn');
const emojiPicker = document.getElementById('emojiPicker');
emojiBtn.addEventListener('click', e => {
    e.stopPropagation();
    emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'flex' : 'none';
});
document.querySelectorAll('.stories-emoji-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        editor.chain().focus().insertContent(btn.dataset.emoji).run();
        emojiPicker.style.display = 'none';
    });
});
document.addEventListener('click', () => { emojiPicker.style.display = 'none'; });

document.getElementById('coverInput').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('coverPreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('coverText').textContent = '✅ ' + file.name;
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('storyForm').addEventListener('submit', function(e) {
    document.getElementById('storyContent').value = editor.getHTML();
    if (editor.getText().trim().length < 50) {
        e.preventDefault();
        alert('La historia debe tener al menos 50 caracteres.');
    }
});

updateContent(editor);
</script>
@endpush
