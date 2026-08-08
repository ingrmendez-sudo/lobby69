@extends('layouts.app')

@section('title', 'Dashboard')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush

@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@push('styles')

<style>
/* Fix alineamiento sidebar-content */
.l69-layout { align-items: start; }
.l69-layout__content { min-width: 0; align-self: start; }
.l69-sidebar { align-self: start; }
</style>
<style>
/* ══ TARJETA DEL FEED ══ */
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
.l69-feed-card__img-wrap {
    position: relative;
    aspect-ratio: 1 / 1;
    background: #0f0a1a;
    overflow: hidden;
}
.l69-feed-card__img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform .2s;
}
.l69-feed-card:hover .l69-feed-card__img { transform: scale(1.04); }

.l69-feed-card__owner-top {
    position: absolute; top: .5rem; left: .5rem;
    display: flex; align-items: center; gap: .4rem;
    background: rgba(0,0,0,.55); backdrop-filter: blur(4px);
    border-radius: 20px; padding: .25rem .55rem .25rem .25rem;
    z-index: 2; text-decoration: none;
    max-width: calc(100% - 1rem);
}
.l69-feed-card__owner-top img {
    width: 22px; height: 22px; border-radius: 50%;
    object-fit: cover; border: 1px solid rgba(255,255,255,.3); flex-shrink: 0;
}
.l69-feed-card__owner-top span {
    font-size: .72rem; font-weight: 600; color: #fff;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.l69-feed-card__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.65) 0%, transparent 55%);
    display: flex; align-items: flex-end; gap: .6rem;
    padding: .65rem .75rem; opacity: 0;
    transition: opacity .18s; z-index: 2;
}
.l69-feed-card:hover .l69-feed-card__overlay { opacity: 1; }

.l69-like-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(0,0,0,.5); border: 1px solid rgba(255,255,255,.15);
    border-radius: 20px; color: #fff; font-size: .8rem; font-weight: 600;
    padding: .3rem .65rem; cursor: pointer;
    transition: background .15s; white-space: nowrap;
}
.l69-like-btn:hover      { background: rgba(224,86,160,.75); }
.l69-like-btn.is-liked   { background: rgba(224,86,160,.7); color: #fff; }
.l69-like-btn.is-liked i { color: #fff; }

.l69-feed-card__comments {
    display: inline-flex; align-items: center; gap: .35rem;
    background: rgba(0,0,0,.5); border: 1px solid rgba(255,255,255,.15);
    border-radius: 20px; color: #fff; font-size: .8rem;
    font-weight: 600; padding: .3rem .65rem; white-space: nowrap;
}
.l69-feed-card__footer { display: none; }
.l69-feed-tabs a { color: var(--theme-text-2); border-color: rgba(180,60,120,.2); }
.l69-feed-tabs a:hover { color: var(--theme-text); }

/* ══ MODAL ══ */
#photoModal {
    display: none;
    position: fixed; inset: 0; z-index: 9999;
    align-items: center; justify-content: center;
    padding: 1rem; background: rgba(0,0,0,.88); overflow: hidden;
}
#photoModal.modal-open {
    display: flex !important;
}


.l69-modal-inner {
    position: relative; display: flex; flex-direction: row;
    width: 100%; max-width: 820px; max-height: 82vh;
    background: var(--bg-body, #1a1028);
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 24px 80px rgba(0,0,0,.7);
}
.l69-modal-photo-wrap {
    position: relative; flex: 0 0 52%; max-width: 52%;
    background: #0d0d0d; display: flex;
    align-items: center; justify-content: center;
    min-height: 300px; overflow: hidden;
}
#modalPhoto {
    display: block; width: 100%; height: 100%;
    max-height: 82vh; object-fit: contain;
    opacity: 0; transition: opacity 0.3s ease;
}
#photoSpinner {
    position: absolute; inset: 0; display: flex;
    align-items: center; justify-content: center;
    background: rgba(0,0,0,.35); z-index: 2;
}
#photoModalClose {
    position: absolute; top: 0.75rem; right: 0.75rem; z-index: 10;
    background: rgba(0,0,0,.55); border: none; color: #fff;
    width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; line-height: 1; transition: background .2s;
}
#photoModalClose:hover { background: rgba(0,0,0,.85); }

.l69-modal-side {
    flex: 1; display: flex; flex-direction: column;
    overflow-y: auto; padding: 1rem; max-height: 82vh; gap: 0.6rem;
    position: relative;
}
.l69-modal-owner { display: flex; align-items: center; gap: 0.6rem; }
.l69-modal-owner img {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; border: 2px solid rgba(224,86,160,.4);
}
.l69-modal-owner span {
    font-weight: 700; font-size: 0.9rem;
    color: var(--theme-text, #f0e8ff);
}
.l69-modal-caption {
    font-size: 0.85rem; color: var(--theme-text-secondary, #c8b8e8);
    margin: 0; word-break: break-word;
}
.l69-modal-actions { display: flex; gap: 0.5rem; align-items: center; }

/* ══ SPINNERS ══ */
.l69-spinner {
    width: 32px; height: 32px;
    border: 3px solid rgba(255,255,255,.15);
    border-top-color: #e056a0; border-radius: 50%;
    animation: l69spin 0.7s linear infinite;
}
@keyframes l69spin { to { transform: rotate(360deg); } }

#commentSpinner {
    display: flex; align-items: center;
    justify-content: center; padding: 1.5rem 0;
}

/* ══ LISTA DE COMENTARIOS ══ */
#commentsList {
    flex: 1; overflow-y: auto; padding: 0.25rem 0;
    min-height: 60px; display: flex;
    flex-direction: column; gap: 0.25rem;
}
.l69-modal-comment-item {
    display: flex; gap: 0.5rem; padding: 0.45rem 0;
    border-bottom: 1px solid var(--theme-border, rgba(128,128,128,0.15));
    align-items: flex-start;
}
.l69-comment-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.l69-comment-content { min-width: 0; flex: 1; }
.l69-comment-author {
    display: block; font-weight: 700; font-size: 0.82rem;
    color: var(--theme-text, #1a1028);
}
.l69-comment-body {
    margin: 0.15rem 0 0; font-size: 0.82rem;
    word-break: break-word;
    color: var(--theme-text-secondary, #4a3a5a);
}
.l69-no-comments {
    font-size: 0.85rem; text-align: center;
    padding: 1.5rem 0; color: var(--theme-text-secondary, #666);
}
[data-theme="light"] .l69-comment-author    { color: #1a1028; }
[data-theme="light"] .l69-comment-body      { color: #4a3a5a; }
[data-theme="light"] .l69-no-comments       { color: #5a4a6a; }
[data-theme="light"] .l69-modal-owner span  { color: #1a1028; }
[data-theme="light"] .l69-modal-caption     { color: #4a3a5a; }
[data-theme="dark"]  .l69-comment-author    { color: #f0e8ff; }
[data-theme="dark"]  .l69-comment-body      { color: #c8b8e8; }
[data-theme="dark"]  .l69-no-comments       { color: #9070b0; }
[data-theme="dark"]  .l69-modal-owner span  { color: #f0e8ff; }
[data-theme="dark"]  .l69-modal-caption     { color: #c8b8e8; }

/* ══ FORMULARIO COMENTARIO ══ */
.l69-comment-form {
    display: flex; flex-direction: column; gap: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--theme-border, rgba(128,128,128,0.15));
    margin-top: auto;
}
.l69-comment-input {
    width: 100%; background: var(--bg-input, rgba(255,255,255,.06));
    border: 1px solid var(--theme-border, rgba(180,60,120,.2));
    border-radius: 8px; color: var(--theme-text, #f0e8ff);
    font-size: 0.85rem; padding: 0.5rem 0.75rem;
    resize: none; transition: border-color .2s; font-family: inherit;
}
.l69-comment-input:focus { outline: none; border-color: #e056a0; }
[data-theme="light"] .l69-comment-input { color: #1a1028; background: rgba(0,0,0,.04); }
.l69-comment-submit {
    align-self: flex-end; background: #e056a0; border: none;
    border-radius: 8px; color: #fff; font-size: 0.82rem;
    font-weight: 600; padding: 0.4rem 1rem;
    cursor: pointer; transition: background .2s;
}
.l69-comment-submit:hover    { background: #c43d8a; }
.l69-comment-submit:disabled { opacity: 0.5; cursor: not-allowed; }

/* ══ RESPONSIVE ══ */
@media (max-width: 640px) {
    .l69-modal-inner { flex-direction: column; max-height: 95vh; border-radius: 8px; }
    .l69-modal-photo-wrap { flex: 0 0 auto; max-width: 100%; height: 45vw; min-height: 200px; max-height: 50vh; }
    #modalPhoto { max-height: 50vh; }
    .l69-modal-side { max-height: 48vh; padding: 0.8rem; }
}

/* Fecha del comentario */
.l69-comment-date {
    font-size: 0.72rem;
    font-weight: 400;
    opacity: 0.55;
    margin-left: 0.35rem;
}

/* Toast de comentario publicado */
.l69-comment-toast {
    position: absolute;
    bottom: 4.5rem;
    left: 50%;
    transform: translateX(-50%);
    background: #1a8a5a;
    color: #fff;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    opacity: 1;
    transition: opacity 0.4s ease;
    pointer-events: none;
    white-space: nowrap;
    z-index: 10;
}

/* Error de comentario */
.l69-comment-error {
    font-size: .82rem;
    color: #ef4444;
    background: rgba(239, 68, 68, .08);
    border: 1px solid rgba(239, 68, 68, .25);
    border-radius: 6px;
    padding: .4rem .65rem;
    margin: 0;
    line-height: 1.4;
}
[data-theme="dark"] .l69-comment-error {
    background: rgba(239, 68, 68, .12);
    border-color: rgba(239, 68, 68, .3);
    color: #fca5a5;
}

</style>
@endpush

@section('content')
{{-- ── Wrapper para alinear con sidebars ── --}}
<div style="min-width:0;">
{{-- ── Disponibles ahora ── --}}
@include('availability._available_users', ['availableUsers' => \])

{{-- ── Tabs ── --}}
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
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));
    gap:1rem; margin-bottom:1.5rem;">
    @include('dashboard._feed_items', ['feed' => $feed, 'user' => $user])
</div>

@if($feed->hasMorePages())
<div id="loadMoreWrap" style="text-align:center;margin-bottom:2rem;">
    <button id="loadMoreBtn" class="l69-quick-btn"
            data-page="{{ $feed->currentPage() + 1 }}"
            data-tab="{{ $tab }}"
            style="display:inline-flex;width:auto;padding:.75rem 2rem;">
        <i class="fas fa-chevron-down"></i> Cargar más fotos
    </button>
</div>
@endif

@else
<div style="text-align:center;padding:4rem 2rem;background:var(--theme-surface-2);border-radius:16px;border:1px solid rgba(180,60,120,.15);">
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

{{-- ══ MODAL DE FOTO ══ --}}
<div id="photoModal" aria-hidden="true">

    {{-- fondo oscuro: click cierra --}}
    <div id="photoModalOverlay"
         style="position:absolute;inset:0;cursor:pointer;"></div>

    <div class="l69-modal-inner">

        {{-- botón cerrar --}}
        <button id="photoModalClose" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>

        {{-- SECCIÓN FOTO --}}
        <div class="l69-modal-photo-wrap">
            <div id="photoSpinner">
                <div class="l69-spinner"></div>
            </div>
            <img id="modalPhoto" src="" alt="Foto">
        </div>

        {{-- SECCIÓN INFO + COMENTARIOS --}}
        <div class="l69-modal-side">

            <div class="l69-modal-owner" id="modalOwnerWrap" style="cursor:pointer;">
                <img loading="lazy" id="modalOwnerAvatar" src="/img/default-avatar.svg" alt="">
                <span id="modalOwnerName"></span>
            </div>

            <p id="modalCaption" class="l69-modal-caption"></p>

            <div class="l69-modal-actions">
                <button id="modalLikeBtn" class="l69-like-btn"
                        data-photo-id="" data-liked="0">
                    <i class="far fa-heart"></i>
                    <span class="like-count">0</span>
                </button>

                {{-- Tooltip likers --}}
                <div id="dsb-likers-wrap" style="position:relative;display:inline-block;margin-left:.35rem;">
                    <span id="dsb-likers-count"
                          style="font-size:.72rem;color:#f472b6;cursor:pointer;user-select:none;font-weight:500;"
                          title="Ver quiénes dieron like"></span>
                    <div id="dsb-likers-tooltip"
                         style="display:none;position:fixed;z-index:99999;background:#1e1e2e;border:1px solid rgba(224,86,160,.35);border-radius:8px;padding:.5rem .65rem;min-width:160px;max-width:230px;box-shadow:0 4px 20px rgba(0,0,0,.7);pointer-events:none;">
                        <div style="font-size:.65rem;color:#f472b6;font-weight:600;
                                    margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.04em;">
                            Les gustó
                        </div>
                        <div id="dsb-likers-list"></div>
                    </div>
                </div>
            </div>

            <div id="commentSpinner">
                <div class="l69-spinner"></div>
            </div>

            <div id="commentsList"></div>

            <form id="commentForm" class="l69-comment-form">
                <input type="hidden" id="commentPhotoId" value="">
                <textarea id="commentBody" class="l69-comment-input"
                          placeholder="Escribe un comentario..." rows="2"></textarea>
                <button type="submit" class="l69-comment-submit">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </form>

        </div>{{-- /modal-side --}}
    </div>{{-- /modal-inner --}}
</div>{{-- /photoModal --}}

</div>{{-- /wrapper --}}
@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════
   DASHBOARD — script único, sin IIFE, scope global
═══════════════════════════════════════════════════════ */
var CSRF           = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
var currentPhotoId = null;

/* ── Helpers ── */
function escapeHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(String(str == null ? '' : str)));
    return d.innerHTML;
}

function formatDate(isoString) {
    if (!isoString) return '';
    var d = new Date(isoString);
    if (isNaN(d)) return '';
    var ahora  = new Date();
    var diff   = Math.floor((ahora - d) / 1000); // segundos

    if (diff < 60)                        return 'ahora';
    if (diff < 3600)                      return Math.floor(diff / 60) + ' min';
    if (diff < 86400)                     return Math.floor(diff / 3600) + ' h';
    if (diff < 604800)                    return Math.floor(diff / 86400) + ' d';

    // más de 7 días: mostrar fecha corta
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
}


/* ── Cerrar modal ── */
function dsbCloseModal() {

    var m = document.getElementById('photoModal');
    if (m) {
        m.classList.remove('modal-open');
        m.removeAttribute('style');          /* elimina cualquier style inline */
        if (document.activeElement) document.activeElement.blur();
        m.setAttribute('aria-hidden', 'true');
    }
    document.body.style.overflow = '';
    currentPhotoId = null;
}

/* ── Renderizar comentarios ── */
function renderComments(comments) {
    var list = document.getElementById('commentsList');
    if (!list) return;

    if (!comments || comments.length === 0) {
        list.innerHTML = '<p class="l69-no-comments">Sé el primero en comentar.</p>';
        return;
    }

    list.innerHTML = comments.map(function(c) {
        var nick   = escapeHtml(c.user_nick || (c.user && c.user.nickname) || 'Usuario');
        var body   = escapeHtml(c.comment || c.body || '');
        var avatar = c.avatar_photo_id ? '/fotos/' + c.avatar_photo_id + '/ver' : '/img/default-avatar.svg';
        var fecha = c.created_at ? formatDate(c.created_at) : '';
        return '<div class="l69-modal-comment-item">' +
                '<img src="' + avatar + '" class="l69-comment-avatar" ' +
                        'onerror="this.src=\'/img/default-avatar.svg\'">' +
                '<div class="l69-comment-content">' +
                    '<span class="l69-comment-author">' + nick +
                        (fecha ? ' <span class="l69-comment-date">' + fecha + '</span>' : '') +
                    '</span>' +
                    '<p class="l69-comment-body">' + body + '</p>' +
                '</div>' +
            '</div>';
    }).join('');

    list.scrollTop = list.scrollHeight;
}

/* ── Abrir modal ── */
function dsbOpenModal(photoId) {
    if (!photoId) return;

    var modal          = document.getElementById('photoModal');
    var modalPhoto     = document.getElementById('modalPhoto');
    var photoSpinner   = document.getElementById('photoSpinner');
    var commentsList   = document.getElementById('commentsList');
    var commentSpinner = document.getElementById('commentSpinner');

    if (!modal || !modalPhoto) return;

    /* mostrar modal */
    modal.removeAttribute('style');          /* elimina display:none inline si existe */
    modal.classList.add('modal-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';


    /* resetear estado visual — crítico para 2ª, 3ª foto */
    modalPhoto.style.opacity = '0';
    if (photoSpinner)   photoSpinner.style.display   = 'flex';
    if (commentsList)   commentsList.innerHTML        = '';
    if (commentSpinner) commentSpinner.style.display  = 'flex';

    /* vaciar src para forzar onload aunque sea la misma URL */
    modalPhoto.src = '';

    modalPhoto.onload = function() {
        if (photoSpinner) photoSpinner.style.display = 'none';
        modalPhoto.style.opacity = '1';
    };
    modalPhoto.onerror = function() {
        if (photoSpinner) photoSpinner.style.display = 'none';
        modalPhoto.style.opacity = '1';
    };

    /* fetch datos */
    fetch('/fotos/' + photoId + '/info', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(function(d) {
        /* foto */
        modalPhoto.src = '/fotos/' + photoId + '/ver';

        /* input oculto */
        var cpId = document.getElementById('commentPhotoId');
        if (cpId) cpId.value = photoId;

        /* like */
        var likeBtn = document.getElementById('modalLikeBtn');
        if (likeBtn) {
            likeBtn.dataset.photoId = photoId;
            var li = likeBtn.querySelector('i');
            var ls = likeBtn.querySelector('.like-count');
            if (d.photo.user_liked) {
                likeBtn.classList.add('is-liked');
                if (li) { li.className = 'fas fa-heart'; li.style.color = '#e056a0'; }
            } else {
                likeBtn.classList.remove('is-liked');
                if (li) { li.className = 'far fa-heart'; li.style.color = ''; }
            }
            if (ls) ls.textContent = d.photo.likes_count || 0;
        }

        /* owner */
        /* ── Owner info con link al perfil ── */
        /* ── Owner info ── */
        var ownerWrap = document.getElementById('modalOwnerWrap');
        var onEl = document.getElementById('modalOwnerName');
        var oaEl = document.getElementById('modalOwnerAvatar');

        var ownerName   = (d.owner && d.owner.name)     ? d.owner.name     : 'Usuario';
        var ownerNick   = (d.owner && d.owner.nickname) ? d.owner.nickname : null;
        var ownerAvatar = (d.owner && d.owner.avatar_url)
            ? d.owner.avatar_url
            : '/img/default-avatar.svg';
        var ownerUrl    = (d.owner && d.owner.url) ? d.owner.url : null;

        if (oaEl) oaEl.src = ownerAvatar;
        if (onEl) onEl.textContent = ownerNick || ownerName;

        if (ownerWrap) {
            ownerWrap.style.cursor = ownerUrl ? 'pointer' : 'default';
            ownerWrap.onclick = ownerUrl
                ? function(e) { e.stopPropagation(); window.location.href = ownerUrl; }
                : null;
        }




        /* ── Likers tooltip ── */
        /* Asegurar tooltip oculto al abrir modal */
        var dsbLikersTooltipReset = document.getElementById('dsb-likers-tooltip');
        if (dsbLikersTooltipReset) dsbLikersTooltipReset.style.display = 'none';

        var dsbLikersCount   = document.getElementById('dsb-likers-count');
        var dsbLikersTooltip = document.getElementById('dsb-likers-tooltip');
        var dsbLikersList    = document.getElementById('dsb-likers-list');
        var dsbLikersWrap    = document.getElementById('dsb-likers-wrap');

        if (dsbLikersCount && dsbLikersList) {
            var likers = d.photo.likers || [];
            if (likers.length > 0) {
                dsbLikersCount.textContent = likers.length === 1
                    ? '1 like'
                    : likers.length + ' likes';
                dsbLikersCount.style.display = 'inline';

                /* Construir lista */
                dsbLikersList.innerHTML = likers.map(function(lk) {
                    var avatarHtml = lk.avatar_id
                        ? '<img src="/fotos/' + lk.avatar_id + '/ver" '
                          + 'style="width:22px;height:22px;border-radius:50%;object-fit:cover;'
                          + 'flex-shrink:0;margin-right:.4rem;" '
                          + 'onerror="this.src=\'/img/default-avatar.svg\'">'
                        : '<div style="width:22px;height:22px;border-radius:50%;'
                          + 'background:rgba(224,86,160,.2);display:flex;align-items:center;'
                          + 'justify-content:center;flex-shrink:0;margin-right:.4rem;">'
                          + '<i class="fas fa-user" style="font-size:.55rem;color:#f472b6;"></i></div>';
                    return '<div style="display:flex;align-items:center;padding:.2rem 0;'
                        + 'font-size:.75rem;color:#e2e8f0;">'
                        + avatarHtml
                        + '<span style="color:#e2e8f0!important;font-size:.75rem;">' + escapeHtml(lk.nick || 'Usuario') + '</span></div>';
                }).join('');

                /* Hover para mostrar/ocultar tooltip */
                if (dsbLikersWrap) {
                    dsbLikersWrap.onmouseenter = function() {
                        if (dsbLikersTooltip) dsbLikersTooltip.style.display = 'block';
                    };
                    dsbLikersWrap.onmouseleave = function() {
                        if (dsbLikersTooltip) dsbLikersTooltip.style.display = 'none';
                    };
                }
            } else {
                dsbLikersCount.textContent = '';
                dsbLikersCount.style.display = 'none';
                if (dsbLikersTooltip) dsbLikersTooltip.style.display = 'none';
                if (dsbLikersWrap) {
                    dsbLikersWrap.onmouseenter = null;
                    dsbLikersWrap.onmouseleave = null;
                }
            }
        }

        /* caption */
        var cap = document.getElementById('modalCaption');
        if (cap) cap.textContent = d.photo.caption || '';

        /* comentarios */
        if (commentSpinner) commentSpinner.style.display = 'none';
        renderComments(d.photo.comments || []);
    })
    .catch(function(err) {
        console.error('openModal error:', err);
        if (photoSpinner)   photoSpinner.style.display   = 'none';
        if (commentSpinner) commentSpinner.style.display  = 'none';
    });
}

/* ── Toggle like (feed y modal) ── */
function toggleLike(photoId, btn) {
    if (!photoId || !btn || btn.dataset.loading === 'true') return;

    btn.dataset.loading = 'true';
    btn.style.opacity   = '0.6';

    var icon         = btn.querySelector('i');
    var counter      = btn.querySelector('.like-count') || btn.querySelector('span');
    var wasLiked     = btn.classList.contains('is-liked');
    var currentCount = parseInt((counter && counter.textContent) || '0', 10);

    /* optimistic UI */
    if (wasLiked) {
        btn.classList.remove('is-liked');
        if (icon)    { icon.className = 'far fa-heart'; icon.style.color = ''; }
        if (counter) counter.textContent = Math.max(0, currentCount - 1);
    } else {
        btn.classList.add('is-liked');
        if (icon)    { icon.className = 'fas fa-heart'; icon.style.color = '#e056a0'; }
        if (counter) counter.textContent = currentCount + 1;
    }

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
                var i  = b.querySelector('i');
                var sp = b.querySelector('.like-count') || b.querySelector('span');
                if (d.liked) {
                    b.classList.add('is-liked');
                    if (i) { i.className = 'fas fa-heart'; i.style.color = '#e056a0'; }
                } else {
                    b.classList.remove('is-liked');
                    if (i) { i.className = 'far fa-heart'; i.style.color = ''; }
                }
                if (sp) sp.textContent = d.likes_count;
            });
    })
    .catch(function() {
        /* revertir */
        if (wasLiked) {
            btn.classList.add('is-liked');
            if (icon)    { icon.className = 'fas fa-heart'; icon.style.color = '#e056a0'; }
            if (counter) counter.textContent = currentCount;
        } else {
            btn.classList.remove('is-liked');
            if (icon)    { icon.className = 'far fa-heart'; icon.style.color = ''; }
            if (counter) counter.textContent = currentCount;
        }
    })
    .finally(function() {
        btn.dataset.loading = 'false';
        btn.style.opacity   = '1';
    });
}

/* ── Enviar comentario ── */
function handleCommentSubmit(form) {
    var photoId = (document.getElementById('commentPhotoId') || {}).value;
    var bodyEl  = document.getElementById('commentBody');
    var body    = bodyEl ? bodyEl.value.trim() : '';
    var sendBtn = form ? form.querySelector('button[type="submit"]') : null;

    if (!body || !photoId) return;

    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    // Limpiar error previo
    showCommentError(null);

    fetch('/fotos/' + photoId + '/comentar', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ body: body })
    })
    .then(function(r) {
        return r.json().then(function(data) {
            return { ok: r.ok, status: r.status, data: data };
        });
    })
    .then(function(res) {
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
        }

        // ── Error del servidor (422, 500, etc.) ──
        if (!res.ok) {
            var msg = 'No se pudo publicar el comentario.';
            if (res.data && res.data.error) {
                msg = typeof res.data.error === 'string'
                    ? res.data.error
                    : Object.values(res.data.error).flat().join(' ');
            }
            showCommentError(msg);
            return;
        }

        // ── Éxito ──
        var d = res.data;
        if (bodyEl) bodyEl.value = '';

        var list = document.getElementById('commentsList');
        if (list) {
            var empty = list.querySelector('.l69-no-comments');
            if (empty) empty.remove();

            var av = d.comment.user_avatar
                ? (d.comment.avatar_photo_id ? '/fotos/' + d.comment.avatar_photo_id + '/ver' : '/img/default-avatar.svg') + ' '
                : '/img/default-avatar.svg';

            var ahora = formatDate(new Date().toISOString());
            list.insertAdjacentHTML('beforeend',
                '<div class="l69-modal-comment-item">' +
                    '<img src="' + av + '" class="l69-comment-avatar" ' +
                        'onerror="this.src=\'/img/default-avatar.svg\'">' +
                    '<div class="l69-comment-content">' +
                        '<span class="l69-comment-author">' +
                            escapeHtml(d.comment.user_nick || 'Usuario') +
                            ' <span class="l69-comment-date">' + ahora + '</span>' +
                        '</span>' +
                        '<p class="l69-comment-body">' +
                            escapeHtml(d.comment.comment || d.comment.body || '') +
                        '</p>' +
                    '</div>' +
                '</div>'
            );
            list.scrollTop = list.scrollHeight;

            // Toast de éxito
            var toast = document.createElement('div');
            toast.className   = 'l69-comment-toast';
            toast.textContent = '✓ Comentario publicado';
            list.parentElement.appendChild(toast);
            setTimeout(function() {
                toast.style.opacity = '0';
                setTimeout(function() { toast.remove(); }, 400);
            }, 2000);
        }
    })
    .catch(function(err) {
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
        }
        showCommentError('Error de conexión. Intenta de nuevo.');
    });
}

// ── Helper: mostrar / limpiar error bajo el textarea ──
function showCommentError(msg) {
    var form    = document.getElementById('commentForm');
    var existing = form ? form.querySelector('.l69-comment-error') : null;
    if (existing) existing.remove();

    if (!msg || !form) return;

    var el = document.createElement('p');
    el.className   = 'l69-comment-error';
    el.textContent = '⚠️ ' + msg;
    // Insertar antes del botón submit
    var btn = form.querySelector('button[type="submit"]');
    if (btn) {
        form.insertBefore(el, btn);
    } else {
        form.appendChild(el);
    }
}


/* ══ EVENT LISTENERS — uno solo de cada tipo ══ */

/* click en tarjeta → abrir modal */
document.addEventListener('click', function(e) {
    var card = e.target.closest('.l69-feed-card');
    if (!card) return;
    if (e.target.closest('.l69-like-btn')) return;
    if (e.target.closest('.l69-feed-card__owner-top')) return;
    dsbOpenModal(card.dataset.photoId);
});

/* like en feed */
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.l69-like-btn');
    if (!btn || !btn.dataset.photoId || btn.dataset.loading === 'true') return;
    e.preventDefault();
    e.stopPropagation();
    toggleLike(btn.dataset.photoId, btn);
});

/* cerrar con botón ✕ */
var elClose = document.getElementById('photoModalClose');
if (elClose) elClose.addEventListener('click', dsbCloseModal);

/* cerrar con click en overlay */
var elOverlay = document.getElementById('photoModalOverlay');
if (elOverlay) elOverlay.addEventListener('click', dsbCloseModal);

/* cerrar con Escape */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') dsbCloseModal();
});

/* formulario de comentario */
var elForm = document.getElementById('commentForm');
if (elForm) {
    elForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleCommentSubmit(this);
    });
}

/* cargar más fotos */
var elLoadMore = document.getElementById('loadMoreBtn');
if (elLoadMore) {
    elLoadMore.addEventListener('click', function() {
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
        var grid = document.getElementById('feedGrid');
        if (grid && d.html) {
            grid.insertAdjacentHTML('beforeend', d.html);
        }
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
        console.error('Feed error:', err);
        self.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error al cargar';
        self.disabled  = false;
    });


    });
}

    // ── Auto-abrir modal si se llegó desde /notificaciones con ?photo=UUID ──
    (function() {
        var params  = new URLSearchParams(window.location.search);
        var photoId = params.get('photo');
        if (!photoId) return;

        // Limpiar query param inmediatamente para no mostrar ?photo= en la URL
        var cleanUrl = new URL(window.location.href);
        cleanUrl.searchParams.delete('photo');
        window.history.replaceState({}, '', cleanUrl.toString());

        // dsbOpenModal necesita que el DOM esté listo y el modal exista
        function tryOpen(attempts) {
            var modal = document.getElementById('photoModal');
            if (modal && typeof dsbOpenModal === 'function') {
                dsbOpenModal(photoId);
            } else if (attempts < 30) {
                setTimeout(function() { tryOpen(attempts + 1); }, 100);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() { tryOpen(0); });
        } else {
            tryOpen(0);
        }
    })();
</script>
@endpush

















