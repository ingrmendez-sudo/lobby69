@extends('layouts.app')
@section('title', ($profile->nickname ?? 'Perfil') . ' — LOBBY69')

{{-- ════════════════════════════════════════════
     SIDEBAR IZQUIERDO
     ════════════════════════════════════════════ --}}
@push('sidebar-left')
<div class="l69-sidebar-card">
    <div style="text-align:center;padding:.5rem 0 1rem;">
        <img src="{{ $avatarUrl }}"
             style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid rgba(180,60,120,.4);margin-bottom:.5rem;"
             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        <div style="font-weight:700;font-size:.95rem;color:var(--theme-text);">
            {{ $profile->nickname }}
        </div>
        <div style="font-size:.76rem;color:var(--theme-muted);margin-top:.15rem;">
            {{ ucfirst($profile->profile_type ?? 'single') }}
            @if($verificationStatus === 'approved')
                · <span style="color:#3b82f6;">✓ Verificado</span>
            @endif
        </div>
    </div>

    <div class="l69-stat-grid">
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $followersCount }}</div>
            <div class="l69-stat__label">Seguidores</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $followingCount }}</div>
            <div class="l69-stat__label">Siguiendo</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $sbPhotosCount }}</div>
            <div class="l69-stat__label">Fotos</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $likesCount }}</div>
            <div class="l69-stat__label">Likes</div>
        </div>
    </div>

    @if($sbReviews->count() > 0)
    <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid rgba(180,60,120,.12);">
        <div style="font-size:.75rem;font-weight:700;color:var(--theme-muted);margin-bottom:.4rem;
                    text-transform:uppercase;letter-spacing:.04em;">
            Recomendaciones
        </div>
        <div style="display:flex;gap:.5rem;">
            <div style="flex:1;text-align:center;background:rgba(39,174,96,.1);
                        border-radius:8px;padding:.4rem;">
                <div style="font-size:1rem;font-weight:800;color:#27ae60;">{{ $sbPos }}</div>
                <div style="font-size:.7rem;color:var(--theme-muted);">👍 Positivas</div>
            </div>
            <div style="flex:1;text-align:center;background:rgba(231,76,60,.1);
                        border-radius:8px;padding:.4rem;">
                <div style="font-size:1rem;font-weight:800;color:#e74c3c;">{{ $sbNeg }}</div>
                <div style="font-size:.7rem;color:var(--theme-muted);">👎 Negativas</div>
            </div>
        </div>
    </div>
    @endif
</div>

@auth
    @if(!$isOwnProfile)
    <div class="l69-sidebar-card" style="margin-top:.6rem;">
        <div class="l69-sidebar-card__title">
            <i class="fas fa-bolt"></i> Acciones
        </div>
        <div style="display:flex;flex-direction:column;gap:.45rem;">
            @if($isFollowing)
                <form method="POST" action="{{ route('unfollow', $profile->user_id) }}" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="prf-sb-btn prf-sb-btn--outline" style="width:100%;">
                        ✓ Siguiendo
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('follow', $profile->user_id) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="prf-sb-btn prf-sb-btn--primary" style="width:100%;">
                        + Seguir
                    </button>
                </form>
            @endif
            <button class="prf-sb-btn prf-sb-btn--msg"
                    data-partner="{{ $profile->user_id }}"
                    data-name="{{ $profile->nickname }}"
                    id="btn-msg-profile"
                    style="width:100%;">
                <i class="fas fa-paper-plane"></i> Enviar mensaje
            </button>
        </div>
    </div>
    @else
    <div class="l69-sidebar-card" style="margin-top:.6rem;">
        <div class="l69-sidebar-card__title">
            <i class="fas fa-cog"></i> Mi Perfil
        </div>
        <a href="{{ route('profile.edit') }}"
           class="prf-sb-btn prf-sb-btn--outline"
           style="width:100%;text-align:center;display:block;">
            ✏️ Editar perfil
        </a>
    </div>
    @endif
@endauth
@endpush

{{-- ════════════════════════════════════════════
     SIDEBAR DERECHO
     ════════════════════════════════════════════ --}}
@push('sidebar-right')
@include('layouts.sidebar-right')

@auth
    @if(!$isOwnProfile && $commonFriends->count() > 0)
    <div class="l69-sidebar-card" style="margin-top:.6rem;">
        <div class="l69-sidebar-card__title">
            <i class="fas fa-users"></i> Amigos en común ({{ $commonFriends->count() }})
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.4rem;">
            @foreach($commonFriends as $cf)
            <a href="{{ $cf->nickname ? route('profile.show', $cf->nickname) : '#' }}"
               title="{{ $cf->nickname ?? $cf->display_name }}"
               style="display:block;">
                @if($cf->avatar_id)
                    <img src="{{ route('photos.serve', $cf->avatar_id) }}"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;
                                border:2px solid rgba(180,60,120,.3);"
                         onerror="this.style.display='none'">
                @else
                    <div style="width:36px;height:36px;border-radius:50%;
                                background:rgba(180,60,120,.3);display:flex;
                                align-items:center;justify-content:center;
                                font-size:.85rem;font-weight:700;color:#e056a0;">
                        {{ mb_substr($cf->display_name ?? '?', 0, 1) }}
                    </div>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif
@endauth
@endpush

{{-- ════════════════════════════════════════════
     ESTILOS
     ════════════════════════════════════════════ --}}
@push('styles')
<style>
.prf-wrap {
    --_bg:       var(--bg-card,        #ffffff);
    --_bg-input: var(--bg-input,       #f0eee8);
    --_text:     var(--text-primary,   #1a1523);
    --_text-sub: var(--text-secondary, #5a5470);
    --_muted:    var(--text-muted,     #9590a8);
    --_border:   var(--border-color,   rgba(26,21,35,.10));
    --_pink:     #e056a0;
    --_purple:   #8b5cf6;
    --_accent:   #c0392b;
    --_radius:   12px;
}

/* ── Header ── */
.prf-header {
    background: var(--_bg);
    border: 1px solid var(--_border);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.1rem;
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
}
.prf-avatar-wrap { position: relative; flex-shrink: 0; }
.prf-avatar {
    width: 100px; height: 100px;
    border-radius: 50%; object-fit: cover;
    border: 3px solid rgba(180,60,120,.4);
    display: block;
}
.prf-verified-badge {
    position: absolute; bottom: 3px; right: 3px;
    background: #3b82f6; color: #fff;
    border-radius: 50%; width: 22px; height: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; border: 2px solid var(--_bg);
}
.prf-info { flex: 1; min-width: 0; }
.prf-nick {
    font-size: 1.35rem; font-weight: 800;
    color: var(--_text); margin: 0 0 .3rem;
    display: flex; align-items: center; gap: .5rem; flex-wrap: wrap;
}
.prf-badge {
    font-size: .72rem; font-weight: 600;
    padding: .18rem .6rem; border-radius: 20px; white-space: nowrap;
}
.prf-badge--verified { background: rgba(59,130,246,.15); color: #60a5fa; }
.prf-badge--type     { background: rgba(180,60,120,.15); color: var(--_pink); }
.prf-badge--member   {
    background: rgba(120,60,180,.15); color: var(--_purple);
    display: inline-flex; align-items: center; gap: .25rem;
}
.prf-location { font-size: .85rem; color: var(--_muted); margin: 0 0 .5rem; }
.prf-bio      { font-size: .9rem;  color: var(--_text-sub); line-height: 1.65; margin: 0 0 .6rem; }

/* ── Follow row ── */
.prf-follow-row {
    display: flex; align-items: center; gap: .75rem;
    flex-wrap: wrap; margin-top: .5rem;
}
.prf-follow-stats {
    font-size: .85rem; color: var(--_muted);
    display: flex; gap: .5rem; align-items: center;
}
.prf-follow-stats strong { color: var(--_text); font-weight: 700; }
.prf-follow-sep { color: var(--_muted); }

/* ── Cuerpo ── */
.prf-body-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.1rem;
    margin-bottom: 1.1rem;
}

/* ── Card genérica ── */
.prf-card {
    background: var(--_bg);
    border: 1px solid var(--_border);
    border-radius: var(--_radius);
    padding: 1.1rem 1.2rem;
    margin-bottom: 1.1rem;
}
.prf-card__title {
    font-size: .88rem; font-weight: 700;
    color: var(--_text); margin: 0 0 .85rem;
    padding-bottom: .55rem;
    border-bottom: 1px solid var(--_border);
    display: flex; align-items: center; gap: .4rem;
}

/* ── Tabla de datos ── */
.prf-table { width: 100%; font-size: .82rem; border-collapse: collapse; }
.prf-table td {
    padding: .28rem 0;
    border-bottom: 1px solid var(--_border);
    vertical-align: top;
}
.prf-table td:first-child {
    color: var(--_muted); width: 45%; padding-right: .5rem;
}
.prf-table td:last-child { color: var(--_text); font-weight: 500; }
.prf-data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.prf-data-col-title { font-size: .82rem; font-weight: 700; margin: 0 0 .5rem; }
.prf-data-col-title--main    { color: var(--_purple); }
.prf-data-col-title--partner { color: var(--_pink); }

/* ── Tags ── */
.prf-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
.prf-tag  {
    font-size: .76rem; font-weight: 600;
    padding: .22rem .55rem; border-radius: 20px; white-space: nowrap;
}
.prf-tag--active {
    background: rgba(180,60,120,.15); color: var(--_pink);
    border: 1px solid rgba(180,60,120,.25);
}
.prf-tag--inactive {
    background: rgba(128,128,128,.07); color: var(--_muted);
    border: 1px solid rgba(128,128,128,.1);
    text-decoration: line-through; opacity: .5;
}

/* ── Carrusel ── */
.prf-carousel-wrap  { position: relative; }
.prf-carousel-track {
    display: flex; gap: .6rem;
    overflow-x: auto; scroll-snap-type: x mandatory;
    scrollbar-width: none; -ms-overflow-style: none;
    padding-bottom: .25rem;
}
.prf-carousel-track::-webkit-scrollbar { display: none; }
.prf-carousel-item {
    flex-shrink: 0; scroll-snap-align: start;
    width: 200px; height: 200px;
    border-radius: 10px; overflow: hidden;
    cursor: pointer; position: relative;
    background: var(--_bg-input);
    border: 1px solid var(--_border);
    transition: transform .15s;
}
.prf-carousel-item:hover { transform: scale(1.02); }
.prf-carousel-item img   { width: 100%; height: 100%; object-fit: cover; display: block; }
.prf-carousel-item-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.6) 0%, transparent 55%);
    opacity: 0; transition: opacity .2s;
    display: flex; align-items: flex-end; padding: .5rem;
}
.prf-carousel-item:hover .prf-carousel-item-overlay { opacity: 1; }
.prf-carousel-item-meta { display: flex; gap: .6rem; align-items: center; }
.prf-carousel-item-meta span {
    font-size: .75rem; color: #fff;
    display: flex; align-items: center; gap: .2rem;
}
.prf-carousel-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    z-index: 10; background: rgba(0,0,0,.55); color: #fff;
    border: none; border-radius: 50%; width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .9rem; transition: background .15s;
}
.prf-carousel-btn:hover   { background: rgba(0,0,0,.8); }
.prf-carousel-btn--prev   { left: .5rem; }
.prf-carousel-btn--next   { right: .5rem; }

/* ── Modal foto ── */
.prf-photo-modal {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.82);
    z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}
.prf-photo-modal.hidden { display: none; }
.prf-photo-modal__box {
    background: var(--_bg);
    border-radius: 16px;
    width: min(860px, 96vw);
    max-height: 90vh;
    overflow-y: auto;
    display: flex; flex-direction: column;
    box-shadow: 0 24px 80px rgba(0,0,0,.4);
}
.prf-photo-modal__img-wrap {
    position: relative; background: #000;
    display: flex; align-items: center; justify-content: center;
    max-height: 55vh; overflow: hidden;
}
.prf-photo-modal__img { max-width: 100%; max-height: 55vh; object-fit: contain; display: block; }
.prf-photo-modal__nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(0,0,0,.55); color: #fff; border: none;
    border-radius: 50%; width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; transition: background .15s;
}
.prf-photo-modal__nav:hover  { background: rgba(0,0,0,.85); }
.prf-photo-modal__nav--prev  { left: .6rem; }
.prf-photo-modal__nav--next  { right: .6rem; }
.prf-photo-modal__body       { padding: 1rem 1.25rem; }
.prf-photo-modal__meta {
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: .5rem; margin-bottom: .75rem;
}
.prf-photo-modal__caption  { font-size: .9rem; font-weight: 600; color: var(--_text); }
.prf-photo-modal__actions  { display: flex; align-items: center; gap: .6rem; }
.prf-photo-modal__close {
    background: transparent; border: none; cursor: pointer;
    color: var(--_muted); font-size: 1.1rem; padding: .25rem .5rem;
    border-radius: 6px; transition: background .15s, color .15s;
}
.prf-photo-modal__close:hover { background: rgba(0,0,0,.08); color: var(--_text); }

/* ── Like button ── */
.prf-like-btn {
    display: flex; align-items: center; gap: .35rem;
    background: transparent; border: 1px solid var(--_border);
    color: var(--_text-sub); border-radius: 8px;
    padding: .3rem .7rem; font-size: .83rem; font-weight: 600;
    cursor: pointer; transition: all .15s;
}
.prf-like-btn:hover,
.prf-like-btn.liked {
    background: rgba(231,76,60,.12); color: #e74c3c;
    border-color: rgba(231,76,60,.3);
}
.prf-like-btn.liked .prf-like-icon::before { content: '❤️'; }
.prf-like-icon::before { content: '🤍'; }

/* ── Comentarios en modal ── */
.prf-modal-comments {
    max-height: 220px; overflow-y: auto; margin-bottom: .6rem;
}
.prf-modal-comment {
    display: flex; gap: .55rem; padding: .4rem 0;
    border-bottom: 1px solid var(--_border); font-size: .82rem;
}
.prf-modal-comment:last-child { border-bottom: none; }
.prf-modal-comment__avatar {
    width: 28px; height: 28px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
    border: 1px solid var(--_border);
}
.prf-modal-comment__ph {
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(180,60,120,.2); color: var(--_pink);
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; flex-shrink: 0;
}
.prf-modal-comment__body   { flex: 1; min-width: 0; }
.prf-modal-comment__author { font-weight: 600; color: var(--_text); }
.prf-modal-comment__text   { color: var(--_text-sub); line-height: 1.45; }
.prf-modal-comment__time   { font-size: .7rem; color: var(--_muted); white-space: nowrap; }
.prf-comment-empty         { font-size: .8rem; color: var(--_muted); padding: .4rem 0; margin: 0; }

.prf-comment-form { display: flex; gap: .5rem; margin-top: .5rem; }
.prf-comment-form textarea {
    flex: 1; resize: none;
    background: var(--_bg-input);
    border: 1px solid var(--_border);
    color: var(--_text);
    border-radius: 8px; padding: .4rem .65rem;
    font-size: .83rem; font-family: inherit;
}
.prf-comment-form textarea:focus {
    outline: none; border-color: rgba(192,57,43,.4);
}
.prf-comment-form button {
    background: var(--_accent); color: #fff;
    border: none; border-radius: 8px;
    padding: .4rem .85rem; font-size: .82rem; font-weight: 700;
    cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.prf-comment-form button:hover { opacity: .85; }

/* ── Modal conversación ── */
.l69-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.55);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999;
}
.l69-modal-overlay.hidden { display: none; }
.l69-modal-box {
    background: var(--_bg); border: 1px solid var(--_border);
    border-radius: 14px; width: min(520px, 96vw);
    display: flex; flex-direction: column;
    max-height: 85vh; overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
.l69-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem 1rem; border-bottom: 1px solid var(--_border);
    font-weight: 700; font-size: .92rem; color: var(--_text);
}
.l69-modal-close {
    background: transparent; border: none; cursor: pointer;
    color: var(--_muted); font-size: 1rem; padding: .15rem .4rem;
    border-radius: 5px; transition: background .15s, color .15s;
}
.l69-modal-close:hover { background: rgba(0,0,0,.07); color: var(--_text); }
.l69-modal-messages {
    flex: 1; overflow-y: auto; padding: 1rem;
    display: flex; flex-direction: column; gap: .45rem;
}
.l69-msg-bubble {
    max-width: 72%; padding: .45rem .8rem;
    border-radius: 10px; font-size: .85rem;
    line-height: 1.45; word-break: break-word;
}
.l69-msg-bubble.mine {
    background: var(--_accent); color: #fff;
    align-self: flex-end; border-bottom-right-radius: 2px;
}
.l69-msg-bubble.theirs {
    background: var(--_bg-input); color: var(--_text);
    align-self: flex-start; border-bottom-left-radius: 2px;
}
.l69-msg-time { font-size: .69rem; opacity: .65; display: block; margin-top: .12rem; }
.l69-modal-send {
    display: flex; gap: .5rem; padding: .75rem 1rem;
    border-top: 1px solid var(--_border);
}
.l69-modal-send textarea {
    flex: 1; resize: none; background: var(--_bg-input);
    border: 1px solid var(--_border); color: var(--_text);
    border-radius: 8px; padding: .45rem .65rem;
    font-size: .85rem; font-family: inherit;
}
.l69-modal-send textarea:focus { outline: none; border-color: rgba(192,57,43,.4); }
.l69-modal-send button {
    background: var(--_accent); color: #fff; border: none;
    border-radius: 8px; padding: .4rem .85rem;
    font-size: .85rem; font-weight: 700;
    cursor: pointer; transition: opacity .15s;
}
.l69-modal-send button:hover { opacity: .85; }

/* ── Botones sidebar ── */
.prf-sb-btn {
    display: inline-flex; align-items: center; justify-content: center;
    gap: .4rem; border-radius: 8px; padding: .45rem 1rem;
    font-size: .83rem; font-weight: 700;
    cursor: pointer; transition: all .15s;
    border: none; text-decoration: none;
}
.prf-sb-btn--primary {
    background: linear-gradient(135deg, var(--_purple), var(--_pink));
    color: #fff;
}
.prf-sb-btn--primary:hover { opacity: .85; }
.prf-sb-btn--outline {
    background: transparent;
    border: 1.5px solid var(--_border);
    color: var(--_text-sub);
}
.prf-sb-btn--outline:hover { border-color: var(--_accent); color: var(--_accent); }
.prf-sb-btn--msg { background: var(--_accent); color: #fff; }
.prf-sb-btn--msg:hover { opacity: .85; }

/* ── Responsive ── */
@media (max-width: 700px) {
    .prf-body-grid      { grid-template-columns: 1fr; }
    .prf-header         { flex-direction: column; align-items: center; text-align: center; }
    .prf-data-grid      { grid-template-columns: 1fr; }
    .prf-carousel-item  { width: 150px; height: 150px; }
}

/* ════════════════════════════════
   SECCIÓN DE DATOS — rediseño
   ════════════════════════════════ */

/* Card de datos con separador interno */
.prf-data-section {
    display: flex;
    flex-direction: column;
    gap: .15rem;
}
.prf-data-row {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    padding: .38rem .1rem;
    border-bottom: 1px solid var(--_border);
    font-size: .83rem;
}
.prf-data-row:last-child { border-bottom: none; }
.prf-data-label {
    flex-shrink: 0;
    width: 42%;
    color: var(--_muted);
    font-size: .78rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: .35rem;
}
.prf-data-label i {
    width: 14px;
    text-align: center;
    color: var(--_pink);
    font-size: .72rem;
    opacity: .8;
}
.prf-data-value {
    flex: 1;
    color: var(--_text);
    font-weight: 600;
    font-size: .83rem;
    word-break: break-word;
}

/* Tarjeta de pareja con columnas */
.prf-couple-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.prf-couple-col-title {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin: 0 0 .6rem;
    padding-bottom: .35rem;
    border-bottom: 2px solid var(--_border);
    display: flex;
    align-items: center;
    gap: .35rem;
}
.prf-couple-col-title--main    { color: var(--_purple); border-color: var(--_purple); }
.prf-couple-col-title--partner { color: var(--_pink);   border-color: var(--_pink); }

/* Nuevo comentario — animación de entrada */
.prf-modal-comment--new {
    animation: fadeSlideIn .3s ease;
}
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Contador de comentarios en modal */
.prf-comments-header {
    font-size: .78rem;
    font-weight: 700;
    color: var(--_muted);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: .5rem;
    display: flex;
    align-items: center;
    gap: .4rem;
}

</style>
@endpush

{{-- ════════════════════════════════════════════
     CONTENIDO PRINCIPAL
     ════════════════════════════════════════════ --}}
@section('content')
@php
    $showName  = $profile->show_name ?? true;
    $showPName = $profile->show_partner_name ?? true;
    $mainName  = $showName  ? ($profile->display_name ?? '') : 'Nombre oculto';
    $partName  = $showPName ? ($profile->partner_name  ?? '') : 'Nombre oculto';
    $location  = implode(', ', array_filter([
        $profile->city  ?? null,
        $profile->state ?? null,
    ]));
@endphp

<div class="prf-wrap">

    {{-- ── Header ── --}}
    <div class="prf-header">
        <div class="prf-avatar-wrap">
            <img class="prf-avatar"
                 src="{{ $avatarUrl }}"
                 alt="{{ $profile->nickname }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            @if($verificationStatus === 'approved')
                <div class="prf-verified-badge" title="Identidad verificada">✓</div>
            @endif
        </div>

        <div class="prf-info">
            <h1 class="prf-nick">
                {{ $profile->nickname }}
                @if($verificationStatus === 'approved')
                    <span class="prf-badge prf-badge--verified">✓ Verificado</span>
                @endif
                <span class="prf-badge prf-badge--type">{{ $typeLabel }}</span>
                <span class="prf-badge prf-badge--member">
                    <img src="{{ $memberIcon }}"
                         alt="{{ $memberLabel }}"
                         style="width:16px;height:16px;object-fit:contain;">
                    {{ $memberLabel }}
                </span>
            </h1>

            @if($location)
                <p class="prf-location">📍 {{ $location }}</p>
            @endif

            @if($profile->bio)
                <p class="prf-bio">{{ $profile->bio }}</p>
            @endif

            <div class="prf-follow-row">
                <div class="prf-follow-stats">
                    <span><strong>{{ $followersCount }}</strong> seguidores</span>
                    <span class="prf-follow-sep">·</span>
                    <span><strong>{{ $followingCount }}</strong> siguiendo</span>
                    <span class="prf-follow-sep">·</span>
                    <span><strong>{{ $photosCount }}</strong> fotos</span>
                </div>

                @auth
                    @if(!$isOwnProfile)
                        @if($isFollowing)
                            <form method="POST"
                                  action="{{ route('unfollow', $profile->user_id) }}"
                                  style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="prf-sb-btn prf-sb-btn--outline">
                                    ✓ Siguiendo
                                </button>
                            </form>
                        @else
                            <form method="POST"
                                  action="{{ route('follow', $profile->user_id) }}"
                                  style="margin:0;">
                                @csrf
                                <button type="submit" class="prf-sb-btn prf-sb-btn--primary">
                                    + Seguir
                                </button>
                            </form>
                        @endif
                        <button class="prf-sb-btn prf-sb-btn--msg"
                                data-partner="{{ $profile->user_id }}"
                                data-name="{{ $profile->nickname }}"
                                id="btn-msg-profile-header">
                            <i class="fas fa-paper-plane"></i> Mensaje
                        </button>
                    @else
                        <a href="{{ route('profile.edit') }}" class="prf-sb-btn prf-sb-btn--outline">
                            ✏️ Editar perfil
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    {{-- ── Cuerpo: dos columnas ── --}}
    <div class="prf-body-grid">

        {{-- Columna izquierda: datos personales --}}
        <div>
            <div class="prf-card">
                <h2 class="prf-card__title">
                    👤 Sobre {{ $isPairing ? 'ellos' : ($isUnicorn ? 'ella/él' : 'mí') }}
                </h2>

                @if($isPairing)
                    <div class="prf-couple-grid">
                        {{-- Persona principal --}}
                        <div>
                            <p class="prf-couple-col-title prf-couple-col-title--main">
                                <i class="fas fa-{{ $profile->gender === 'masculino' ? 'mars' : 'venus' }}"></i>
                                {{ $mainName ?: ($profile->gender === 'masculino' ? 'Él' : 'Ella') }}
                            </p>
                            <div class="prf-data-section">
                                @if($profile->age)
                                <div class="prf-data-row">
                                    <span class="prf-data-label"><i class="fas fa-birthday-cake"></i> Edad</span>
                                    <span class="prf-data-value">{{ $profile->age }} años</span>
                                </div>
                                @endif
                                @if($profile->orientation)
                                <div class="prf-data-row">
                                    <span class="prf-data-label"><i class="fas fa-heart"></i> Orientación</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->orientation) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Pareja --}}
                        @if($profile->partner_age || $profile->partner_name)
                        <div>
                            <p class="prf-couple-col-title prf-couple-col-title--partner">
                                <i class="fas fa-{{ $profile->partner_gender === 'masculino' ? 'mars' : 'venus' }}"></i>
                                {{ $partName ?: ($profile->partner_gender === 'masculino' ? 'Él' : 'Ella') }}
                            </p>
                            <div class="prf-data-section">
                                @if($profile->partner_age)
                                <div class="prf-data-row">
                                    <span class="prf-data-label"><i class="fas fa-birthday-cake"></i> Edad</span>
                                    <span class="prf-data-value">{{ $profile->partner_age }} años</span>
                                </div>
                                @endif
                                @if($profile->partner_gender)
                                <div class="prf-data-row">
                                    <span class="prf-data-label"><i class="fas fa-venus-mars"></i> Género</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->partner_gender) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    {{-- Single / Unicornio --}}
                    <div class="prf-data-section">
                        @if($profile->age)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-birthday-cake"></i> Edad</span>
                            <span class="prf-data-value">{{ $profile->age }} años</span>
                        </div>
                        @endif
                        @if($profile->gender)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-venus-mars"></i> Género</span>
                            <span class="prf-data-value">{{ ucfirst($profile->gender) }}</span>
                        </div>
                        @endif
                        @if($profile->orientation)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-heart"></i> Orientación</span>
                            <span class="prf-data-value">{{ ucfirst($profile->orientation) }}</span>
                        </div>
                        @endif
                        @if($profile->nationality ?? null)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-flag"></i> Nacionalidad</span>
                            <span class="prf-data-value">{{ $profile->nationality }}</span>
                        </div>
                        @endif
                        @if($profile->tattoos ?? null)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-pen-nib"></i> Tatuajes</span>
                            <span class="prf-data-value">{{ $profile->tattoos }}</span>
                        </div>
                        @endif
                        @if($profile->piercings ?? null)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-circle"></i> Piercings</span>
                            <span class="prf-data-value">{{ $profile->piercings }}</span>
                        </div>
                        @endif
                        @if($profile->smokes ?? null)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-smoking"></i> Fuma</span>
                            <span class="prf-data-value">{{ ucfirst($profile->smokes) }}</span>
                        </div>
                        @endif
                        @if($profile->drinks ?? null)
                        <div class="prf-data-row">
                            <span class="prf-data-label"><i class="fas fa-wine-glass-alt"></i> Bebe</span>
                            <span class="prf-data-value">{{ ucfirst($profile->drinks) }}</span>
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Columna derecha: buscan + intereses --}}
        <div>
            <div class="prf-card">
                <h2 class="prf-card__title">🔍 Buscan</h2>
                <div class="prf-tags">
                    @foreach($allLookingFor as $opt)
                        <span class="prf-tag {{ in_array($opt, $lookingFor) ? 'prf-tag--active' : 'prf-tag--inactive' }}">
                            {{ $opt }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="prf-card">
                <h2 class="prf-card__title">💫 Para</h2>
                <div class="prf-tags">
                    @foreach($allInterests as $opt)
                        <span class="prf-tag {{ in_array($opt, $interests) ? 'prf-tag--active' : 'prf-tag--inactive' }}">
                            {{ $opt }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    {{-- ── Carrusel de fotos ── --}}
    @if($photos->isNotEmpty())
    <div class="prf-card">
        <h2 class="prf-card__title">
            📸 Fotos públicas
            <span style="font-weight:400;color:var(--_muted);font-size:.8rem;margin-left:.25rem;">
                ({{ $photosCount }})
            </span>
        </h2>
        <div class="prf-carousel-wrap">
            <button class="prf-carousel-btn prf-carousel-btn--prev"
                    id="carousel-prev" aria-label="Anterior">‹</button>
            <div class="prf-carousel-track" id="carousel-track">
                @foreach($photos as $i => $photo)
                @php
                    $puuid     = (string)($photo->photo_uuid ?? '');
                    $likeCount = (int)($likeCounts[$puuid] ?? 0);
                    $iLiked    = isset($myLikes[$puuid]);
                @endphp
                <div class="prf-carousel-item"
                     data-index="{{ $i }}"
                     data-photo-id="{{ $photo->id }}"
                     data-photo-uuid="{{ $puuid }}"
                     data-caption="{{ $photo->caption ?? '' }}"
                     data-likes="{{ $likeCount }}"
                     data-iliked="{{ $iLiked ? '1' : '0' }}">
                    <img src="{{ route('photos.serve', $photo->id) }}"
                         alt="{{ $photo->caption ?? '' }}"
                         loading="lazy"
                         onerror="this.parentElement.style.display='none'">
                    <div class="prf-carousel-item-overlay">
                        <div class="prf-carousel-item-meta">
                            <span>{{ $iLiked ? '❤️' : '🤍' }} {{ $likeCount }}</span>
                            <span>
                                <i class="fas fa-comment" style="font-size:.65rem;"></i> Ver
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <button class="prf-carousel-btn prf-carousel-btn--next"
                    id="carousel-next" aria-label="Siguiente">›</button>
        </div>
    </div>
    @endif

</div>{{-- /.prf-wrap --}}

{{-- ── Modal foto ── --}}
<div id="photo-modal" class="prf-photo-modal hidden">
    <div class="prf-photo-modal__box">
        <div class="prf-photo-modal__img-wrap">
            <button class="prf-photo-modal__nav prf-photo-modal__nav--prev"
                    id="pm-prev" aria-label="Anterior">‹</button>
            <img id="pm-img" class="prf-photo-modal__img" src="" alt="">
            <button class="prf-photo-modal__nav prf-photo-modal__nav--next"
                    id="pm-next" aria-label="Siguiente">›</button>
        </div>
        <div class="prf-photo-modal__body">
            <div class="prf-photo-modal__meta">
                <span class="prf-photo-modal__caption" id="pm-caption"></span>
                <div class="prf-photo-modal__actions">
                    @auth
                    <button class="prf-like-btn" id="pm-like-btn" data-photo-uuid="">
                        <span class="prf-like-icon"></span>
                        <span id="pm-like-count">0</span>
                    </button>
                    @endauth
                    <button class="prf-photo-modal__close" id="pm-close" aria-label="Cerrar">✕</button>
                </div>
            </div>
            <div class="prf-modal-comments" id="pm-comments">
                <p class="prf-comment-empty">Cargando comentarios…</p>
            </div>
            {{-- Comentarios --}}
            <div class="prf-comments-header">
                <i class="fas fa-comments" style="color:var(--_pink);"></i>
                Comentarios
            </div>
            <div class="prf-modal-comments" id="pm-comments">
                <p class="prf-comment-empty">Cargando comentarios…</p>
            </div>

            @auth
            <form class="prf-comment-form" id="pm-comment-form">
                <textarea id="pm-comment-body"
                          placeholder="Escribe un comentario…"
                          rows="2"
                          maxlength="400"></textarea>
                <button type="submit">Comentar</button>
            </form>
            @endauth
        </div>
    </div>
</div>

{{-- ── Modal conversación ── --}}
<div id="conv-modal" class="l69-modal-overlay hidden">
    <div class="l69-modal-box">
        <div class="l69-modal-header">
            <span id="conv-modal-name"></span>
            <button id="conv-modal-close" class="l69-modal-close" aria-label="Cerrar">✕</button>
        </div>
        <div id="conv-modal-messages" class="l69-modal-messages"></div>
        <form id="conv-send-form" class="l69-modal-send">
            <input type="hidden" id="conv-receiver-id">
            <textarea id="conv-body"
                      placeholder="Escribe un mensaje…"
                      rows="2"
                      maxlength="1000"></textarea>
            <button type="submit">Enviar</button>
        </form>
    </div>
</div>

@endsection

{{-- ════════════════════════════════════════════
     SCRIPTS
     ════════════════════════════════════════════ --}}
@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const ME   = '{{ Auth::id() }}';

/* ════════════════════════════════
   HELPERS
   ════════════════════════════════ */
async function postJson(url, data) {
    const r = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify(data),
    });
    return r.json();
}

function escHtml(s) {
    return String(s ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function fmtTime(iso) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleTimeString('es-MX', {
            hour: '2-digit', minute: '2-digit',
        });
    } catch { return ''; }
}

/* ════════════════════════════════
   CARRUSEL
   ════════════════════════════════ */
const track   = document.getElementById('carousel-track');
const btnPrev = document.getElementById('carousel-prev');
const btnNext = document.getElementById('carousel-next');
const STEP    = 216;

btnPrev?.addEventListener('click', () =>
    track?.scrollBy({ left: -STEP * 2, behavior: 'smooth' })
);
btnNext?.addEventListener('click', () =>
    track?.scrollBy({ left: STEP * 2, behavior: 'smooth' })
);

/* ════════════════════════════════
   MODAL FOTO
   ════════════════════════════════ */
const photoModal  = document.getElementById('photo-modal');
const pmImg       = document.getElementById('pm-img');
const pmCaption   = document.getElementById('pm-caption');
const pmLikeBtn   = document.getElementById('pm-like-btn');
const pmLikeCount = document.getElementById('pm-like-count');
const pmComments  = document.getElementById('pm-comments');
const pmForm      = document.getElementById('pm-comment-form');
const pmBody      = document.getElementById('pm-comment-body');

let photoItems = Array.from(document.querySelectorAll('.prf-carousel-item'));
let currentIdx = 0;

function openPhoto(idx) {
    if (!photoItems.length) return;
    currentIdx = ((idx % photoItems.length) + photoItems.length) % photoItems.length;
    const el        = photoItems[currentIdx];
    const photoId   = el.dataset.photoId;
    const photoUuid = el.dataset.photoUuid;
    const caption   = el.dataset.caption;

    pmImg.src = `/fotos/${photoId}/ver`;
    pmImg.alt = caption || '';
    if (pmCaption) pmCaption.textContent = caption || `Foto ${currentIdx + 1} de ${photoItems.length}`;

    // Resetear like — se actualiza al cargar los datos del servidor
    if (pmLikeBtn) {
        pmLikeBtn.dataset.photoId   = photoId;
        pmLikeBtn.dataset.photoUuid = photoUuid;
        pmLikeBtn.classList.remove('liked');
        pmLikeBtn.disabled = false;
    }
    if (pmLikeCount) pmLikeCount.textContent = el.dataset.likes ?? '0';
    if (el.dataset.iliked === '1') pmLikeBtn?.classList.add('liked');

    // Cargar comentarios desde el servidor (fuente de verdad)
    loadPhotoData(photoId, photoUuid);
    photoModal?.classList.remove('hidden');
}

// Cargar datos completos de la foto: comentarios aprobados + like count real
async function loadPhotoData(photoId, photoUuid) {
    if (!pmComments) return;
    pmComments.innerHTML = '<p class="prf-comment-empty">Cargando comentarios…</p>';
    try {
        const r    = await fetch(`/fotos/${photoId}/info`, {
            headers: { Accept: 'application/json' },
        });
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        const data = await r.json();

        // Actualizar like count y estado con el valor real del servidor
        if (data.photo && pmLikeCount) {
            pmLikeCount.textContent = data.photo.likes_count ?? 0;
            // Actualizar dataset del carousel item
            const item = photoItems.find(el => el.dataset.photoId === photoId);
            if (item) item.dataset.likes = data.photo.likes_count ?? 0;
        }
        if (data.photo?.user_liked !== undefined && pmLikeBtn) {
            pmLikeBtn.classList.toggle('liked', !!data.photo.user_liked);
            const item = photoItems.find(el => el.dataset.photoId === photoId);
            if (item) item.dataset.iliked = data.photo.user_liked ? '1' : '0';
        }

        // Renderizar comentarios
        // El servidor retorna: user_nick, display_name, avatar_photo_id, body, created_at
        renderComments(data.comments ?? []);
    } catch(e) {
        pmComments.innerHTML = '<p class="prf-comment-empty">Error al cargar comentarios.</p>';
    }
}

/* Construye la URL del avatar desde avatar_photo_id */
function avatarUrl(avatarPhotoId) {
    if (!avatarPhotoId) return null;
    return `/fotos/${avatarPhotoId}/ver`;
}

function renderComments(comments) {
    if (!pmComments) return;
    if (!comments.length) {
        pmComments.innerHTML = '<p class="prf-comment-empty">Sin comentarios aprobados aún. ¡Sé el primero!</p>';
        return;
    }
    // Campos reales del servidor: user_nick, display_name, avatar_photo_id, body, created_at
    pmComments.innerHTML = comments.map(c => {
        const name   = c.user_nick ?? c.display_name ?? 'Usuario';
        const avUrl  = avatarUrl(c.avatar_photo_id);
        const avHtml = avUrl
            ? `<img class="prf-modal-comment__avatar"
                    src="${escHtml(avUrl)}"
                    alt="${escHtml(name)}"
                    onerror="this.style.display='none'">`
            : `<div class="prf-modal-comment__ph">${escHtml(name[0]?.toUpperCase() ?? '?')}</div>`;
        return `
            <div class="prf-modal-comment">
                ${avHtml}
                <div class="prf-modal-comment__body">
                    <div class="prf-modal-comment__author">${escHtml(name)}</div>
                    <div class="prf-modal-comment__text">${escHtml(c.body)}</div>
                </div>
                <span class="prf-modal-comment__time">${fmtTime(c.created_at)}</span>
            </div>`;
    }).join('');
    pmComments.scrollTop = pmComments.scrollHeight;
}

/* Agregar un comentario recién enviado al DOM sin recargar */
function appendComment(c) {
    if (!pmComments) return;
    // Quitar el mensaje de "sin comentarios" si existe
    const empty = pmComments.querySelector('.prf-comment-empty');
    if (empty) empty.remove();

    const name   = c.user_nick ?? c.display_name ?? 'Usuario';
    const avUrl  = avatarUrl(c.avatar_photo_id);
    const avHtml = avUrl
        ? `<img class="prf-modal-comment__avatar"
                src="${escHtml(avUrl)}"
                alt="${escHtml(name)}"
                onerror="this.style.display='none'">`
        : `<div class="prf-modal-comment__ph">${escHtml(name[0]?.toUpperCase() ?? '?')}</div>`;

    const div = document.createElement('div');
    div.className = 'prf-modal-comment prf-modal-comment--new';
    div.innerHTML = `
        ${avHtml}
        <div class="prf-modal-comment__body">
            <div class="prf-modal-comment__author">${escHtml(name)}</div>
            <div class="prf-modal-comment__text">${escHtml(c.body)}</div>
        </div>
        <span class="prf-modal-comment__time">${fmtTime(c.created_at)}</span>`;
    pmComments.appendChild(div);
    pmComments.scrollTop = pmComments.scrollHeight;
}

/* Navegar entre fotos */
photoItems.forEach((el, i) =>
    el.addEventListener('click', () => openPhoto(i))
);

document.getElementById('pm-close')
    ?.addEventListener('click', () => photoModal?.classList.add('hidden'));
document.getElementById('pm-prev')
    ?.addEventListener('click', () => openPhoto(currentIdx - 1));
document.getElementById('pm-next')
    ?.addEventListener('click', () => openPhoto(currentIdx + 1));

photoModal?.addEventListener('click', e => {
    if (e.target === photoModal) photoModal.classList.add('hidden');
});

document.addEventListener('keydown', e => {
    if (photoModal?.classList.contains('hidden')) return;
    if (e.key === 'ArrowLeft')  openPhoto(currentIdx - 1);
    if (e.key === 'ArrowRight') openPhoto(currentIdx + 1);
    if (e.key === 'Escape')     photoModal.classList.add('hidden');
});

/* ── Like ── */
pmLikeBtn?.addEventListener('click', async () => {
    if (pmLikeBtn.disabled) return;
    const photoId = pmLikeBtn.dataset.photoId;
    if (!photoId) return;

    pmLikeBtn.disabled = true;
    try {
        // El servidor retorna: { liked: bool, likes_count: int }
        const data = await postJson(`/fotos/${photoId}/like`, {});
        if (typeof data.liked !== 'undefined') {
            pmLikeBtn.classList.toggle('liked', data.liked);
            if (pmLikeCount) pmLikeCount.textContent = data.likes_count ?? 0;

            // Sincronizar el carrusel
            const item = photoItems.find(el => el.dataset.photoId === photoId);
            if (item) {
                item.dataset.iliked = data.liked ? '1' : '0';
                item.dataset.likes  = data.likes_count ?? 0;
                const overlay = item.querySelector('.prf-carousel-item-meta span:first-child');
                if (overlay) overlay.textContent = (data.liked ? '❤️' : '🤍') + ' ' + (data.likes_count ?? 0);
            }
        }
    } catch(e) {
        console.error('Error al dar like:', e);
    } finally {
        pmLikeBtn.disabled = false;
    }
});

/* ── Enviar comentario ── */
pmForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const body = pmBody?.value.trim();
    if (!body) return;

    const item = photoItems[currentIdx];
    if (!item) return;
    const photoId = item.dataset.photoId;

    const btn = pmForm.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.disabled    = true;
    btn.textContent = 'Enviando…';

    try {
        // El servidor retorna: { success: true, comment: { id, body, user_nick, avatar_photo_id, created_at } }
        const data = await postJson(`/fotos/${photoId}/comentar`, { body });

        if (data.success && data.comment) {
            pmBody.value = '';
            appendComment(data.comment);
            // Feedback visual breve
            btn.textContent = '✓ Enviado';
            setTimeout(() => { btn.textContent = originalText; }, 1500);
        } else if (data.error) {
            alert(data.error);
            btn.textContent = originalText;
        } else {
            // Si el servidor no retorna el comentario, recargamos desde servidor
            pmBody.value = '';
            btn.textContent = '✓ Enviado';
            setTimeout(() => { btn.textContent = originalText; }, 1500);
            loadPhotoData(photoId, item.dataset.photoUuid);
        }
    } catch(err) {
        console.error('Error al comentar:', err);
        alert('Error de conexión. Intenta de nuevo.');
        btn.textContent = originalText;
    } finally {
        btn.disabled = false;
    }
});

/* ════════════════════════════════
   MODAL CONVERSACIÓN
   ════════════════════════════════ */
const convModal  = document.getElementById('conv-modal');
const convName   = document.getElementById('conv-modal-name');
const convMsgs   = document.getElementById('conv-modal-messages');
const convForm   = document.getElementById('conv-send-form');
const convBody   = document.getElementById('conv-body');
const convRecvId = document.getElementById('conv-receiver-id');

function renderMessages(msgs) {
    if (!convMsgs) return;
    convMsgs.innerHTML = '';
    if (!msgs.length) {
        convMsgs.innerHTML = `<p style="text-align:center;font-size:.8rem;
            color:var(--text-muted,#999);padding:1rem 0;">
            Sin mensajes aún. ¡Escribe el primero!</p>`;
        return;
    }
    msgs.forEach(m => {
        const mine = String(m.sender_id) === ME;
        const d    = document.createElement('div');
        d.className = `l69-msg-bubble ${mine ? 'mine' : 'theirs'}`;
        d.innerHTML = `${escHtml(m.body)}
            <span class="l69-msg-time">${fmtTime(m.created_at)}</span>`;
        convMsgs.appendChild(d);
    });
    convMsgs.scrollTop = convMsgs.scrollHeight;
}

async function openConversation(partnerId, name) {
    if (!partnerId || !convModal) return;
    if (convName)   convName.textContent = name ?? '';
    if (convRecvId) convRecvId.value     = partnerId;
    convModal.classList.remove('hidden');
    if (convMsgs) convMsgs.innerHTML = `<p style="text-align:center;font-size:.8rem;
        color:var(--text-muted,#999);padding:1rem 0;">Cargando…</p>`;
    try {
        const data = await fetch(`/mensajes/conversacion/${partnerId}`, {
            headers: { Accept: 'application/json' },
        }).then(r => r.json());
        renderMessages(data.messages ?? []);
    } catch {
        if (convMsgs) convMsgs.innerHTML = `<p style="text-align:center;
            color:#e74c3c;font-size:.8rem">Error al cargar mensajes.</p>`;
    }
}

document.getElementById('btn-msg-profile')
    ?.addEventListener('click', function () {
        openConversation(this.dataset.partner, this.dataset.name);
    });
document.getElementById('btn-msg-profile-header')
    ?.addEventListener('click', function () {
        openConversation(this.dataset.partner, this.dataset.name);
    });

document.getElementById('conv-modal-close')
    ?.addEventListener('click', () => convModal?.classList.add('hidden'));
convModal?.addEventListener('click', e => {
    if (e.target === convModal) convModal.classList.add('hidden');
});

convForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const body = convBody?.value.trim();
    if (!body || !convRecvId?.value) return;
    const btn = convForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
        const data = await postJson('/mensajes/enviar', {
            receiver_id: convRecvId.value,
            body,
        });
        if (data.ok) {
            convBody.value = '';
            await openConversation(convRecvId.value, convName?.textContent ?? '');
        }
    } finally {
        btn.disabled = false;
    }
});
</script>
@endpush

