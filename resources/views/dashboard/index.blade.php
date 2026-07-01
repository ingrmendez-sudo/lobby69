@extends('layouts.app')

@section('title', 'Dashboard')

{{-- ═══════════════════════════════════════
     SIDEBAR IZQUIERDO
════════════════════════════════════════ --}}
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush

{{-- ═══════════════════════════════════════
     SIDEBAR DERECHO
════════════════════════════════════════ --}}
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

{{-- ═══════════════════════════════════════
     CONTENIDO CENTRAL
════════════════════════════════════════ --}}
@section('content')

@push('styles')
<style>
/* ── Tarjeta del feed ── */
.l69-feed-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
}
.l69-feed-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
}

/* Imagen cuadrada */
.l69-feed-card__img-wrap {
    position: relative;
    aspect-ratio: 1 / 1;
    background: #0f0a1a;
    overflow: hidden;
}
.l69-feed-card__img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .2s;
}
.l69-feed-card:hover .l69-feed-card__img {
    transform: scale(1.04);
}

/* ── Info del dueño en esquina superior izquierda ── */
.l69-feed-card__owner-top {
    position: absolute;
    top: .5rem;
    left: .5rem;
    display: flex;
    align-items: center;
    gap: .4rem;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(4px);
    border-radius: 20px;
    padding: .25rem .55rem .25rem .25rem;
    z-index: 2;
    text-decoration: none;
    max-width: calc(100% - 1rem);
}
.l69-feed-card__owner-top img {
    width: 22px; height: 22px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(255,255,255,.3);
    flex-shrink: 0;
}
.l69-feed-card__owner-top span {
    font-size: .72rem;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Overlay con like y comentario — visible al hover ── */
.l69-feed-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.65) 0%, transparent 55%);
    display: flex;
    align-items: flex-end;
    gap: .6rem;
    padding: .65rem .75rem;
    opacity: 0;
    transition: opacity .18s;
    z-index: 2;
}
.l69-feed-card:hover .l69-feed-card__overlay {
    opacity: 1;
}

/* Botón like */
.l69-like-btn {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: rgba(0,0,0,.5);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 20px;
    color: #fff;
    font-size: .8rem;
    font-weight: 600;
    padding: .3rem .65rem;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
}
.l69-like-btn:hover { background: rgba(224,86,160,.75); }
.l69-like-btn.is-liked {
    background: rgba(224,86,160,.7);
    color: #fff;
}
.l69-like-btn i { font-size: .85rem; }

/* Contador comentarios */
.l69-feed-card__comments {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: rgba(0,0,0,.5);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 20px;
    color: #fff;
    font-size: .8rem;
    font-weight: 600;
    padding: .3rem .65rem;
    white-space: nowrap;
}

/* Footer — oculto, la info va en el overlay superior */
.l69-feed-card__footer {
    display: none;
}

/* ── Tabs del feed ── */
.l69-feed-tabs a {
    color: var(--theme-text-2);
    border-color: rgba(180,60,120,.2);
}
.l69-feed-tabs a:hover {
    color: var(--theme-text);
}
</style>
@endpush



{{-- ── Tabs de navegación del feed ── --}}
<div class="l69-feed-tabs" style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
    <a href="{{ route('dashboard', ['tab'=>'new']) }}"
       class="l69-quick-btn {{ $tab === 'new' ? 'l69-quick-btn--active' : '' }}"
       style="{{ $tab === 'new' ? 'background:rgba(180,60,120,.25);color:#e056a0;border-color:rgba(180,60,120,.5);' : '' }}">
        <i class="fas fa-clock"></i> Nuevas
    </a>
    <a href="{{ route('dashboard', ['tab'=>'popular']) }}"
       class="l69-quick-btn {{ $tab === 'popular' ? 'l69-quick-btn--active' : '' }}"
       style="{{ $tab === 'popular' ? 'background:rgba(180,60,120,.25);color:#e056a0;border-color:rgba(180,60,120,.5);' : '' }}">
        <i class="fas fa-fire"></i> Populares
    </a>
    <a href="{{ route('dashboard', ['tab'=>'following']) }}"
       class="l69-quick-btn {{ $tab === 'following' ? 'l69-quick-btn--active' : '' }}"
       style="{{ $tab === 'following' ? 'background:rgba(180,60,120,.25);color:#e056a0;border-color:rgba(180,60,120,.5);' : '' }}">
        <i class="fas fa-users"></i> Siguiendo
    </a>
</div>

{{-- ── Grid de fotos ── --}}
@if($feed->count() > 0)
<div class="l69-feed-grid" id="feedGrid" style="
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
">
    @include('dashboard._feed_items', ['feed' => $feed, 'user' => $user])
</div>

{{-- ── Botón cargar más ── --}}
@if($feed->hasMorePages())
<div id="loadMoreWrap" style="text-align:center;margin-bottom:2rem;">
    <button id="loadMoreBtn"
            class="l69-quick-btn"
            data-page="{{ $feed->currentPage() + 1 }}"
            data-tab="{{ $tab }}"
            style="display:inline-flex;width:auto;padding:.75rem 2rem;">
        <i class="fas fa-chevron-down"></i> Cargar más fotos
    </button>
</div>
@endif

@else
{{-- ── Estado vacío ── --}}
<div style="
    text-align:center;
    padding:4rem 2rem;
    background:var(--theme-surface-2);
    border-radius:16px;
    border:1px solid rgba(180,60,120,.15);
">
    <i class="fas fa-images" style="font-size:3rem;color:rgba(180,60,120,.4);margin-bottom:1rem;display:block;"></i>
    <p style="color:rgba(226,217,243,.6);margin:0 0 1rem;">
        @if($tab === 'following')
            Aún no sigues a nadie o las personas que sigues no tienen fotos públicas.
        @else
            Todavía no hay fotos aprobadas en el feed.
        @endif
    </p>
    <a href="{{ route('photos.index') }}" class="l69-quick-btn" style="display:inline-flex;width:auto;">
        <i class="fas fa-camera"></i> Subir mis fotos
    </a>
</div>
@endif

{{-- ══════════════════════════════════════════
     MODAL DE FOTO
═══════════════════════════════════════════ --}}
<div id="photoModal" style="
    display:none;
    position:fixed;top:0;left:0;right:0;bottom:0;
    background:rgba(0,0,0,.85);
    z-index:99999;
    align-items:center;
    justify-content:center;
    padding:1rem;
">
    {{-- Overlay para cerrar al click fuera --}}
    <div id="photoModalOverlay" style="position:absolute;inset:0;"></div>

    {{-- Contenedor del modal --}}
    <div style="
        position:relative;
        background:var(--theme-surface-2, #1a1028);
        border-radius:16px;
        max-width:960px;
        width:100%;
        max-height:90vh;
        overflow:hidden;
        display:flex;
        flex-direction:row;
        z-index:1;
    ">
        {{-- ── Imagen ── --}}
        <div style="flex:1;min-width:0;background:#000;display:flex;align-items:center;justify-content:center;">
            <img id="modalPhoto" src="" alt="Foto"
                 style="max-width:100%;max-height:90vh;object-fit:contain;display:block;">
        </div>

        {{-- ── Panel derecho ── --}}
        <div style="width:320px;flex-shrink:0;display:flex;flex-direction:column;overflow:hidden;">

            {{-- Header del modal --}}
            <div style="
                display:flex;align-items:center;
                justify-content:space-between;
                padding:.9rem 1rem;
                border-bottom:1px solid rgba(180,60,120,.2);
                flex-shrink:0;
            ">
                {{-- Owner --}}
                <a id="modalOwnerLink" href="#" style="display:flex;align-items:center;gap:.6rem;text-decoration:none;">
                    <img id="modalOwnerAvatar" src="" alt=""
                         style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid rgba(180,60,120,.4);"
                         onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                    <div>
                        <div id="modalOwnerNick" style="font-weight:700;font-size:.9rem;color:var(--theme-text);"></div>
                        <div id="modalOwnerMeta" style="font-size:.72rem;color:rgba(226,217,243,.5);"></div>
                    </div>
                </a>
                {{-- Cerrar --}}
                <button id="photoModalClose" style="
                    background:none;border:none;cursor:pointer;
                    color:rgba(226,217,243,.6);font-size:1.2rem;
                    padding:.3rem;line-height:1;
                " title="Cerrar">&times;</button>
            </div>

            {{-- Caption --}}
            <p id="modalCaption" style="
                display:none;
                margin:.75rem 1rem 0;
                font-size:.85rem;
                color:rgba(226,217,243,.75);
                flex-shrink:0;
            "></p>

            {{-- Acciones: like + comentarios --}}
            <div style="
                display:flex;align-items:center;gap:1rem;
                padding:.75rem 1rem;
                border-bottom:1px solid rgba(180,60,120,.15);
                flex-shrink:0;
            ">
                <button id="modalLikeBtn"
                        class="l69-like-btn"
                        data-photo-id=""
                        style="
                            background:none;border:none;cursor:pointer;
                            display:flex;align-items:center;gap:.4rem;
                            color:rgba(226,217,243,.7);font-size:.9rem;padding:0;
                        ">
                    <i class="far fa-heart" style="font-size:1.1rem;"></i>
                    <span id="modalLikeCount">0</span>
                </button>
                <span style="display:flex;align-items:center;gap:.4rem;color:rgba(226,217,243,.5);font-size:.9rem;">
                    <i class="far fa-comment"></i>
                    <span id="modalCommentCount">0</span>
                </span>
                <a id="modalProfileLink" href="#"
                   style="margin-left:auto;font-size:.78rem;color:rgba(180,60,120,.8);text-decoration:none;">
                    Ver perfil →
                </a>
            </div>

            {{-- Lista de comentarios --}}
            <div id="commentsList" style="
                flex:1;overflow-y:auto;
                padding:.75rem 1rem;
                display:flex;flex-direction:column;gap:.75rem;
                scrollbar-width:thin;
                scrollbar-color:rgba(180,60,120,.3) transparent;
            ">
                <div style="text-align:center;color:rgba(226,217,243,.4);font-size:.85rem;padding:2rem 0;">
                    <i class="far fa-comment"></i><br>Sin comentarios aún
                </div>
            </div>

            {{-- Formulario de comentario --}}
            <form id="commentForm" style="
                border-top:1px solid rgba(180,60,120,.15);
                padding:.75rem 1rem;
                flex-shrink:0;
            ">
                <input type="hidden" id="commentPhotoId" value="">
                <div style="display:flex;gap:.5rem;align-items:flex-end;">
                    <textarea id="commentBody"
                              placeholder="Escribe un comentario..."
                              maxlength="500"
                              rows="2"
                              style="
                                  flex:1;resize:none;
                                  background:rgba(255,255,255,.06);
                                  border:1px solid rgba(180,60,120,.25);
                                  border-radius:9px;
                                  padding:.55rem .75rem;
                                  color:var(--theme-text);
                                  font-size:.85rem;
                                  outline:none;
                              "></textarea>
                    <button type="submit"
                            class="dsb-modal__comment-send"
                            style="
                                background:rgba(180,60,120,.7);
                                border:none;border-radius:9px;
                                color:#fff;cursor:pointer;
                                padding:.55rem .75rem;
                                font-size:.9rem;
                                flex-shrink:0;
                            ">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <p id="commentNote" style="font-size:.72rem;color:rgba(226,217,243,.45);margin:.4rem 0 0;">
                    <i class="fas fa-info-circle"></i>
                    Los comentarios se publican tras revisión del admin
                </p>
            </form>

        </div>{{-- fin panel derecho --}}
    </div>{{-- fin contenedor modal --}}
</div>{{-- fin photoModal --}}

@endsection

{{-- ═══════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════ --}}
@push('scripts')
<script>
(function(){
var CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Abrir modal al click en tarjeta ──
document.addEventListener('click', function(e) {
    var card = e.target.closest('.l69-feed-card');
    if (!card) return;
    if (e.target.closest('.l69-like-btn') ||
        e.target.closest('.l69-card__owner')) return;
    openModal(card.dataset.photoId);
});

// ── Like en feed ──
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.l69-like-btn');
    if (!btn || !btn.dataset.photoId) return;
    e.preventDefault(); e.stopPropagation();
    toggleLike(btn.dataset.photoId, btn);
});

// ── Toggle Like ──
function toggleLike(photoId, btn) {
    btn.disabled = true;
    fetch('/fotos/' + photoId + '/like', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        document.querySelectorAll('.l69-like-btn[data-photo-id="' + photoId + '"]')
            .forEach(function(b) {
                b.querySelector('i').className = d.liked ? 'fas fa-heart' : 'far fa-heart';
                d.liked ? b.classList.add('is-liked') : b.classList.remove('is-liked');
                var sp = b.querySelector('span');
                if (sp) sp.textContent = d.likes_count;
            });
        var mc = document.getElementById('modalLikeCount');
        if (mc && document.getElementById('modalLikeBtn')?.dataset.photoId === photoId) {
            mc.textContent = d.likes_count;
        }
    })
    .catch(function(err) { console.warn('toggleLike error:', err); })
    .finally(function() { btn.disabled = false; });
}

// ── Abrir Modal ──
function openModal(photoId) {
    if (!photoId) return;
    document.getElementById('photoModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';

    document.getElementById('modalPhoto').src          = '';
    document.getElementById('modalOwnerAvatar').src    = '';
    document.getElementById('modalOwnerNick').textContent  = '';
    document.getElementById('modalOwnerMeta').textContent  = '';
    document.getElementById('modalLikeCount').textContent  = '0';
    document.getElementById('modalCommentCount').textContent = '0';
    document.getElementById('modalCaption').style.display   = 'none';
    document.getElementById('commentsList').innerHTML =
        '<div style="text-align:center;padding:2rem;color:rgba(226,217,243,.4);">' +
        '<i class="fas fa-spinner fa-spin"></i></div>';

    fetch('/fotos/' + photoId + '/info', {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        var p = d.photo;
        document.getElementById('modalPhoto').src = '/foto/' + p.file_path;
        document.getElementById('modalLikeCount').textContent  = p.likes_count;
        document.getElementById('modalCommentCount').textContent = d.photo.comments_count;

        var lb = document.getElementById('modalLikeBtn');
        lb.dataset.photoId = p.id;
        lb.querySelector('i').className = d.photo.user_liked ? 'fas fa-heart' : 'far fa-heart';
        d.photo.user_liked ? lb.classList.add('is-liked') : lb.classList.remove('is-liked');

       if (p.description && p.description.trim() !== '') {
            var cap = document.getElementById('modalCaption');
            cap.textContent   = p.description;
            cap.style.display = 'block';
        }

        document.getElementById('modalOwnerAvatar').src       = p.owner.avatar_url ?? '{{ asset('img/default-avatar.svg') }}';
        document.getElementById('modalOwnerNick').textContent = p.owner.nick;
        document.getElementById('modalOwnerLink').href        = '/perfil/' + p.owner.nick;
        document.getElementById('modalProfileLink').href      = '/perfil/' + p.owner.nick;
        document.getElementById('commentPhotoId').value       = p.id;

        var typeLabel = p.owner.profile_type === 'pareja'    ? '👫 Pareja'    :
                        p.owner.profile_type === 'unicornio' ? '⭐ Unicornio' : '👤 Single';
        document.getElementById('modalOwnerMeta').textContent =
            typeLabel + (p.owner.city ? ' · ' + p.owner.city : '');

        var list = document.getElementById('commentsList');
        if (!d.photo.comments || d.photo.comments.length === 0) {
            list.innerHTML =
                '<div style="text-align:center;padding:2rem;color:rgba(226,217,243,.4);font-size:.85rem;">' +
                '<i class="far fa-comment"></i><br>Sé el primero en comentar</div>';
        } else {
            list.innerHTML = d.photo.comments.map(function(c) {
                var avatar = c.user_avatar || '{{ asset('img/default-avatar.svg') }}';
                return '<div style="display:flex;gap:.6rem;align-items:flex-start;">' +
                    '<img src="' + avatar + '" ' +
                         'onerror="this.src=\'{{ asset('img/default-avatar.svg') }}\'" ' +
                         'style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0;">' +
                    '<div style="min-width:0;">' +
                    '<span style="font-weight:700;font-size:.82rem;color:var(--theme-text);">' + (c.user_nick || 'Usuario') + '</span>' +
                    '<span style="font-size:.72rem;color:rgba(226,217,243,.4);margin-left:.4rem;">' + (c.created_at || '') + '</span>' +
                    '<p style="margin:.2rem 0 0;font-size:.83rem;color:rgba(226,217,243,.75);word-break:break-word;">' + c.comment + '</p>' +
                    '</div></div>';
            }).join('');
        }
    })
    .catch(function(err) {
        console.error('openModal error:', err);
        document.getElementById('commentsList').innerHTML =
            '<div style="text-align:center;padding:2rem;color:#ef4444;font-size:.85rem;">' +
            '<i class="fas fa-exclamation-circle"></i><br>Error al cargar</div>';
    });
}

// ── Cerrar modal ──
document.getElementById('photoModalClose').addEventListener('click', closeModal);
document.getElementById('photoModalOverlay').addEventListener('click', closeModal);
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeModal(); });
function closeModal() {
    document.getElementById('photoModal').style.display = 'none';
    document.body.style.overflow = '';
}

// ── Like en modal ──
document.getElementById('modalLikeBtn').addEventListener('click', function() {
    toggleLike(this.dataset.photoId, this);
});

// ── Enviar comentario ──
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var photoId = document.getElementById('commentPhotoId').value;
    var bodyEl  = document.getElementById('commentBody');
    var body    = bodyEl.value.trim();
    var note    = document.getElementById('commentNote');
    var sendBtn = this.querySelector('.dsb-modal__comment-send');
    if (!body) return;

    sendBtn.disabled  = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/fotos/' + photoId + '/comentario', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept':       'application/json'
        },
        body: JSON.stringify({ body: body })
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        bodyEl.value     = '';
        note.style.color = '#34d399';
        note.innerHTML   = '<i class="fas fa-check-circle"></i> Comentario enviado';
        var mc = document.getElementById('modalCommentCount');
        if (mc) mc.textContent = parseInt(mc.textContent || '0') + 1;
        setTimeout(function() {
            note.style.color = '';
            note.innerHTML   = '<i class="fas fa-info-circle"></i> Los comentarios se publican tras revisión del admin';
        }, 3500);
    })
    .catch(function(err) {
        note.style.color = '#ef4444';
        note.innerHTML   = '<i class="fas fa-times-circle"></i> Error al enviar';
        console.error('comentario error:', err);
    })
    .finally(function() {
        sendBtn.disabled  = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
});

// ── Cargar más fotos ──
var loadBtn = document.getElementById('loadMoreBtn');
if (loadBtn) {
    loadBtn.addEventListener('click', function() {
        var page = this.dataset.page;
        var tab  = this.dataset.tab;
        var self = this;
        self.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
        self.disabled  = true;

        fetch('/dashboard/feed?tab=' + tab + '&page=' + page, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(d) {
            document.getElementById('feedGrid').insertAdjacentHTML('beforeend', d.html);
            if (d.hasMore) {
                self.dataset.page = d.currentPage + 1;
                self.innerHTML    = '<i class="fas fa-chevron-down"></i> Cargar más fotos';
                self.disabled     = false;
            } else {
                var wrap = document.getElementById('loadMoreWrap');
                if (wrap) wrap.style.display = 'none';
            }
        })
        .catch(function(err) {
            self.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error al cargar';
            self.disabled  = false;
            console.error('loadMore error:', err);
        });
    });
}

})();
</script>
@endpush
