@extends('layouts.app')
@section('title', ($profile->nickname ?? 'Perfil') . ' — LOBBY69')

{{-- ════════════════════════════════════════════
     SIDEBAR IZQUIERDO — stats del perfil visitado
     ════════════════════════════════════════════ --}}
@push('sidebar-left')

{{-- Tarjeta: avatar + stats --}}
<div class="l69-sidebar-card">
    <div style="text-align:center;padding:.5rem 0 1rem;">
        <img src="{{ $avatarUrl }}"
             style="width:72px;height:72px;border-radius:50%;object-fit:cover;
                    border:2px solid rgba(180,60,120,.4);margin-bottom:.5rem;"
             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        <div style="font-weight:700;font-size:.95rem;color:var(--theme-text);">
            {{ $profile->nickname }}
        </div>
        <div style="font-size:.76rem;color:var(--theme-muted);margin-top:.15rem;">
            {{ ucfirst($profile->profile_type ?? 'single') }}
            @if($verificationStatus === 'approved')
                &middot; <span style="color:#3b82f6;">&#10003; Verificado</span>
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
                    text-transform:uppercase;letter-spacing:.04em;">Recomendaciones</div>
        <div style="display:flex;gap:.5rem;">
            <div style="flex:1;text-align:center;background:rgba(39,174,96,.1);
                        border-radius:8px;padding:.4rem;">
                <div style="font-size:1rem;font-weight:800;color:#27ae60;">{{ $sbPos }}</div>
                <div style="font-size:.7rem;color:var(--theme-muted);">&#128077; Positivas</div>
            </div>
            <div style="flex:1;text-align:center;background:rgba(231,76,60,.1);
                        border-radius:8px;padding:.4rem;">
                <div style="font-size:1rem;font-weight:800;color:#e74c3c;">{{ $sbNeg }}</div>
                <div style="font-size:.7rem;color:var(--theme-muted);">&#128078; Negativas</div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Ultimos perfiles visitados --}}
@auth
@if($recentlyVisited->count() > 0)
<div class="l69-sidebar-card" style="margin-top:.6rem;">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-history"></i> Visitados recientemente
    </div>
    @foreach($recentlyVisited as $rv)
    <a href="{{ $rv->nickname ? route('profile.show', $rv->nickname) : '#' }}"
       style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;
              text-decoration:none;border-bottom:1px solid rgba(255,255,255,.05);">
        @if($rv->avatar_id)
            <img src="{{ route('photos.serve', $rv->avatar_id) }}"
                 style="width:32px;height:32px;border-radius:50%;object-fit:cover;
                        flex-shrink:0;border:1px solid rgba(180,60,120,.3);"
                 onerror="this.style.display='none'">
        @else
            <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;
                        background:rgba(108,63,197,.3);display:flex;
                        align-items:center;justify-content:center;
                        font-size:.75rem;font-weight:700;color:#a78bfa;">
                {{ mb_substr($rv->display_name ?? '?', 0, 1) }}
            </div>
        @endif
        <div style="min-width:0;">
            <div style="font-size:.78rem;font-weight:600;color:var(--theme-text);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $rv->display_name }}
                @if($rv->verified_profile)
                    <i class="fas fa-check-circle" style="color:#22c55e;font-size:.6rem;"></i>
                @endif
            </div>
            <div style="font-size:.68rem;color:var(--theme-muted);">
                {{ ucfirst($rv->profile_type ?? '') }}
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif

@endauth

{{-- Tarjeta: acciones --}}
@auth
    @if(!$isOwnProfile)
    <div class="l69-sidebar-card" style="margin-top:.6rem;">
        <div class="l69-sidebar-card__title">
            <i class="fas fa-bolt"></i> Acciones
        </div>
        <div style="display:flex;flex-direction:column;gap:.45rem;">
            @if($isFollowing)
                <form method="POST" action="{{ route('unfollow', $profile->nickname) }}" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" class="prf-sb-btn prf-sb-btn--outline" style="width:100%;">
                        &#10003; Siguiendo
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('follow', $profile->nickname) }}" style="margin:0;">
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

            {{-- Botón amistad --}}
            @php $fStatus = $friendshipStatus ?? null; @endphp
            @if($fStatus === 'accepted')
                <button class="prf-sb-btn prf-sb-btn--outline" disabled style="width:100%;opacity:.7;cursor:default;">
                    <i class="fas fa-user-check"></i> Amigos
                </button>
            @elseif($fStatus === 'pending')
                <button class="prf-sb-btn prf-sb-btn--outline" disabled style="width:100%;opacity:.7;cursor:default;">
                    <i class="fas fa-clock"></i> Solicitud enviada
                </button>
            @else
                <button class="prf-sb-btn prf-sb-btn--primary"
                        id="btn-add-friend"
                        data-target="{{ $profile->user_id }}"
                        style="width:100%;">
                    <i class="fas fa-user-plus"></i> Agregar amigo
                </button>
            @endif
        </div>
    </div>
    @else
    <div class="l69-sidebar-card" style="margin-top:.6rem;">
        <div class="l69-sidebar-card__title"><i class="fas fa-cog"></i> Mi Perfil</div>
        <a href="{{ route('profile.edit') }}"
           class="prf-sb-btn prf-sb-btn--outline"
           style="width:100%;text-align:center;display:block;">
            &#9999;&#65039; Editar perfil
        </a>
    </div>
    @endif
@endauth

{{-- Tarjeta: amigos en comun --}}
@auth
    @if(!$isOwnProfile && $commonFriends->count() > 0)
    <div class="l69-sidebar-card" style="margin-top:.6rem;">
        <div class="l69-sidebar-card__title">
            <i class="fas fa-users"></i> Amigos en comun ({{ $commonFriends->count() }})
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

{{-- Tarjeta: eventos y noticias --}}
<div class="l69-sidebar-card" style="margin-top:.6rem;">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-compass"></i> Descubre
    </div>
    <div style="display:flex;flex-direction:column;gap:.4rem;">
        <a href="{{ route('events.public.index') }}" class="l69-quick-btn">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a>
        <a href="{{ route('articles.public.index') }}" class="l69-quick-btn">
            <i class="fas fa-newspaper"></i> Noticias
        </a>
        <a href="{{ route('explore') }}" class="l69-quick-btn">
            <i class="fas fa-compass"></i> Explorar perfiles
        </a>
    </div>
</div>

@endpush

{{-- ════════════════════════════════════════════
     SIDEBAR DERECHO — actividad propia + visitados + recomendados
     ════════════════════════════════════════════ --}}
@push('sidebar-right')

{{-- Stats propios del usuario logueado (layout base) --}}
{{-- Ocultar Mi Actividad y Accesos Rapidos en perfil ajeno --}}
<style>
/* Ocultar por titulo exacto */

        /* ── Replies de comentarios ── */
        .prf-comment-reply {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            margin: .3rem 0 .3rem 2.2rem;
            padding: .4rem .6rem;
            background: rgba(255,255,255,.04);
            border-left: 2px solid var(--_pink, #c0392b);
            border-radius: 0 6px 6px 0;
        }
        .prf-comment-reply__ph {
            width: 22px !important;
            height: 22px !important;
            font-size: .65rem !important;
            flex-shrink: 0;
        }
        .prf-comment-reply__body { flex: 1; min-width: 0; }
        .prf-btn-reply {
            background: none;
            border: none;
            color: var(--_pink, #c0392b);
            font-size: .72rem;
            cursor: pointer;
            padding: .15rem .3rem;
            margin-top: .25rem;
            opacity: .8;
            transition: opacity .2s;
        }
        .prf-btn-reply:hover { opacity: 1; }
        .prf-btn-reply:disabled { opacity: .4; cursor: default; }
    </style>
@include('layouts.sidebar-right')


{{-- Perfiles recomendados --}}
@if($recommendedProfiles->count() > 0)
<div class="l69-sidebar-card" style="margin-top:.6rem;">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-thumbs-up"></i> Recomendados para ti
    </div>
    @foreach($recommendedProfiles as $rp)
    <a href="{{ $rp->nickname ? route('profile.show', $rp->nickname) : '#' }}"
       style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;
              text-decoration:none;border-bottom:1px solid rgba(255,255,255,.05);">
        @if($rp->avatar_id)
            <img src="{{ route('photos.serve', $rp->avatar_id) }}"
                 style="width:32px;height:32px;border-radius:50%;object-fit:cover;
                        flex-shrink:0;border:1px solid rgba(224,86,160,.3);"
                 onerror="this.style.display='none'">
        @else
            <div style="width:32px;height:32px;border-radius:50%;flex-shrink:0;
                        background:rgba(224,86,160,.2);display:flex;
                        align-items:center;justify-content:center;
                        font-size:.75rem;font-weight:700;color:#f472b6;">
                {{ mb_substr($rp->display_name ?? '?', 0, 1) }}
            </div>
        @endif
        <div style="min-width:0;">
            <div style="font-size:.78rem;font-weight:600;color:var(--theme-text);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $rp->display_name }}
                @if($rp->verified_profile)
                    <i class="fas fa-check-circle" style="color:#22c55e;font-size:.6rem;"></i>
                @endif
            </div>
            <div style="font-size:.68rem;color:var(--theme-muted);">
                {{ $rp->city ?? ucfirst($rp->profile_type ?? '') }}
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif
@endpush

{{-- ════════════════════════════════════════════
     ESTILOS
     ════════════════════════════════════════════ --}}
@push('styles')
<style>
.prf-wrap {
    --_bg:       var(--bg-card,        #ffffff);
    --_bg-input: var(--bg-input,       #f0eee8);
    --_text:     var(--text-primary,   #c4b6df);
    --_text-sub: var(--text-secondary, #d3cbf1);
    --_muted:    var(--text-muted,     #9590a8);
    --_border:   var(--border-color,   rgba(231, 221, 250, 0.28));
    --_pink:     #e056a0;
    --_purple:   #8b5cf6;
    --_accent:   #c0392b;
    --_radius:   12px;
}

/* Header */
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
    padding: .2rem .55rem; border-radius: 20px;
    white-space: nowrap;
}
.prf-badge--verified { background: rgba(59,130,246,.15); color: #3b82f6; }
.prf-badge--type     { background: rgba(139,92,246,.15); color: #8b5cf6; }
.prf-badge--member   {
    background: rgba(180,60,120,.12); color: var(--_pink);
    display: inline-flex; align-items: center; gap: .3rem;
}
.prf-location { font-size: .83rem; color: var(--_muted); margin: 0 0 .35rem; }
.prf-bio      { font-size: .85rem; color: var(--_text-sub); margin: 0 0 .6rem; line-height: 1.5; }
.prf-follow-row {
    display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
    margin-top: .4rem;
}
.prf-follow-stats { display: flex; align-items: center; gap: .4rem; font-size: .83rem; color: var(--_text-sub); }
.prf-follow-sep   { color: var(--_muted); }

/* Botones sidebar */
.prf-sb-btn {
    display: inline-flex; align-items: center; justify-content: center;
    gap: .4rem; padding: .45rem .9rem;
    border-radius: 9px; font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .15s; text-decoration: none;
    border: 1px solid transparent;
}
.prf-sb-btn--primary {
    background: var(--_blue); color: #5c92e2; border-color: var(--_blue);
}
.prf-sb-btn--primary:hover { opacity: .87; }
.prf-sb-btn--outline {
    background: transparent; color: var(--_text);
    border-color: var(--_border);
}
.prf-sb-btn--outline:hover { background: rgba(0,0,0,.05); }
.prf-sb-btn--msg {
    background: rgba(139,92,246,.12); color: var(--_purple);
    border-color: rgba(139,92,246,.25);
}
.prf-sb-btn--msg:hover { background: rgba(139,92,246,.22); }

/* Zona central */
.prf-central { display: flex; flex-direction: column; gap: 1rem; }

/* Tarjeta generica */
.prf-card {
    background: var(--_bg);
    border: 1px solid var(--_border);
    border-radius: var(--_radius);
    padding: 1.1rem 1.25rem;
}
.prf-card__title {
    font-size: .82rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--_muted); margin: 0 0 .85rem;
    display: flex; align-items: center; gap: .4rem;
}

/* Seccion colapsable "Sobre ellos" */
.prf-sobre-wrap {}
.prf-sobre-inner {
    max-height: 99999px; /* sin límite inicial */
    overflow: hidden;
    transition: max-height .35s ease;
    line-height: 1.8em;
}
.prf-sobre-inner.expanded { max-height: 99999px; }
.prf-sobre-toggle {
    margin-top: .5rem;
    background: none; border: none;
    color: var(--_pink); font-size: .8rem; font-weight: 600;
    cursor: pointer; padding: 0; display: flex; align-items: center; gap: .25rem;
}
.prf-sobre-toggle:hover { opacity: .8; }

/* Grid de datos */
.prf-data-section { display: flex; flex-direction: column; gap: .3rem; }
.prf-data-row {
    display: flex; align-items: baseline;
    gap: .5rem; font-size: .84rem;
    padding: .25rem 0;
    border-bottom: 1px solid var(--_border);
}
.prf-data-row:last-child { border-bottom: none; }
.prf-data-label {
    min-width: 110px; color: var(--_muted);
    font-size: .78rem; display: flex;
    align-items: center; gap: .35rem;
}
.prf-data-label i { width: 14px; text-align: center; color: var(--_pink); font-size: .72rem; opacity: .8; }
.prf-data-value { flex: 1; color: var(--_text); font-weight: 600; font-size: .83rem; word-break: break-word; }

/* Pareja grid */
.prf-couple-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.prf-couple-col-title {
    font-size: .78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .04em; margin: 0 0 .6rem;
    padding-bottom: .35rem; border-bottom: 2px solid var(--_border);
    display: flex; align-items: center; gap: .35rem;
}
.prf-couple-col-title--main    { color: var(--_purple); border-color: var(--_purple); }
.prf-couple-col-title--partner { color: var(--_pink);   border-color: var(--_pink); }

/* Globos Buscan / Para */
.prf-bubbles-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.prf-bubble {
    background: var(--_bg);
    border: 1px solid var(--_border);
    border-radius: var(--_radius);
    padding: 1rem 1.1rem;
}
.prf-bubble__title {
    font-size: .8rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--_muted); margin: 0 0 .65rem;
}
.prf-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
.prf-tag {
    font-size: .75rem; font-weight: 500;
    padding: .2rem .55rem; border-radius: 20px;
    border: 1px solid transparent;
}
.prf-tag--active {
    background: rgba(180,60,120,.15);
    color: var(--_pink);
    border-color: rgba(180,60,120,.3);
}
.prf-tag--inactive {
    background: rgba(0,0,0,.04);
    color: var(--_muted);
    border-color: var(--_border);
    opacity: .55;
}

/* Carrusel */
.prf-carousel-wrap  { position: relative; }
.prf-carousel-track {
    display: flex; gap: .6rem;
    overflow-x: auto; scroll-snap-type: x mandatory;
    padding-bottom: .4rem;
    scrollbar-width: none;
}
.prf-carousel-track::-webkit-scrollbar { display: none; }
.prf-carousel-item {
    width: 200px; height: 200px; flex-shrink: 0;
    border-radius: 10px; overflow: hidden;
    position: relative; cursor: pointer;
    scroll-snap-align: start;
    transition: transform .2s;
    border: 1px solid var(--_border);
}
.prf-carousel-item:hover { transform: scale(1.02); }
.prf-carousel-item img   { width: 100%; height: 100%; object-fit: cover; display: block; }
.prf-carousel-item-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.65) 0%, transparent 55%);
    opacity: 0; transition: opacity .2s;
    display: flex; align-items: flex-end; padding: .5rem;
}
.prf-carousel-item:hover .prf-carousel-item-overlay { opacity: 1; }
.prf-carousel-item-meta { display: flex; gap: .6rem; align-items: center; }
.prf-carousel-item-meta span {
    color: #fff; font-size: .75rem; font-weight: 600;
    display: flex; align-items: center; gap: .2rem;
}
.prf-carousel-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(0,0,0,.55); color: #fff; border: none;
    border-radius: 50%; width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .9rem; z-index: 2;
    transition: background .15s;
}
.prf-carousel-btn:hover   { background: rgba(0,0,0,.8); }
.prf-carousel-btn--prev   { left: .5rem; }
.prf-carousel-btn--next   { right: .5rem; }

/* Modal foto */
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
    border-radius: 20px;
    width: min(960px, 96vw);
    max-height: 90vh;
    overflow: hidden;
    display: grid;
    grid-template-columns: 60% 40%;
    box-shadow: 0 0 0 2px rgba(160,160,160,.30),
                0 8px 24px rgba(0,0,0,.40),
                0 32px 80px rgba(0,0,0,.60);
}
.prf-photo-modal__img-wrap {
    position: relative;
    background: #111;
    min-height: 400px;
    border-radius: 20px 0 0 20px;
    display: flex; align-items: center; justify-content: center;
    max-height: 90vh;
    overflow: hidden;
}
.prf-photo-modal__img { max-width: 100%; max-height: 90vh; object-fit: contain; display: block; }
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

/* Comentarios en modal */
.prf-comments-section { margin-bottom: .6rem; }
.prf-comments-header {
    font-size: .78rem; font-weight: 700;
    color: var(--_muted); text-transform: uppercase;
    letter-spacing: .04em; margin-bottom: .5rem;
    display: flex; align-items: center; gap: .4rem;
}
.prf-modal-comments { max-height: 220px; overflow-y: auto; margin-bottom: .6rem; }
.prf-modal-comment {
    display: flex; gap: .55rem; padding: .4rem 0;
    border-bottom: 1px solid var(--_border); font-size: .82rem;
}
.prf-modal-comment:last-child { border-bottom: none; }
.prf-modal-comment__avatar {
    width: 28px; height: 28px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0; border: 1px solid var(--_border);
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
.prf-comment-form textarea:focus { outline: none; border-color: rgba(192,57,43,.4); }
.prf-comment-form button {
    background: #c0392b; color: #fff;
    border: none; border-radius: 8px;
    padding: .4rem .85rem; font-size: .82rem; font-weight: 700;
    cursor: pointer; transition: opacity .15s; white-space: nowrap;
    flex-shrink: 0;
}
.prf-comment-form button:hover { opacity: .85; }
.prf-modal-comment--new { animation: fadeSlideIn .3s ease; }
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Modal conversacion */
.l69-modal-overlay {
    position: fixed; inset: 0; z-index: 9998;
    background: rgba(0,0,0,.75);
    display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.l69-modal-overlay.hidden { display: none; }
.l69-modal-box {
    background: var(--bg-card, var(--_bg, #ffffff));
    border-radius: 16px;
    width: min(480px, 96vw); max-height: 85vh;
    display: flex; flex-direction: column;
    box-shadow: 0 0 0 2px rgba(160,160,160,.28),
                0 8px 24px rgba(0,0,0,.35),
                0 24px 64px rgba(0,0,0,.50);
}
/* Texto del modal conversación legible en ambos modos */
.l69-modal-header,
.l69-modal-header span,
#conv-modal-name {
    color: var(--text-primary, var(--_text, #1a1523)) !important;
}
.l69-msg-bubble.mine   { background: rgba(180,60,120,.22); color: var(--text-primary, var(--_text, #1a1523)); }
.l69-msg-bubble.theirs { background: rgba(128,128,128,.14); color: var(--text-primary, var(--_text, #1a1523)); }
.l69-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .85rem 1.1rem;
    border-bottom: 1px solid var(--_border);
    font-weight: 700; font-size: .95rem; color: var(--_text);
}
.l69-modal-close {
    background: none; border: none; cursor: pointer;
    color: var(--_muted); font-size: 1rem; padding: .25rem .4rem;
    border-radius: 6px; transition: background .15s;
}
.l69-modal-close:hover { background: rgba(0,0,0,.07); }
.l69-modal-messages {
    flex: 1; overflow-y: auto; padding: .75rem 1rem; display: flex;
    flex-direction: column; gap: .5rem; min-height: 120px;
}
.l69-msg-bubble {
    max-width: 75%; padding: .45rem .75rem;
    border-radius: 12px; font-size: .83rem; line-height: 1.45; word-break: break-word;
}
.l69-msg-bubble.mine   { background: rgba(180,60,120,.18); color: var(--_text); align-self: flex-end; border-bottom-right-radius: 3px; }
.l69-msg-bubble.theirs { background: rgba(0,0,0,.06);      color: var(--_text); align-self: flex-start; border-bottom-left-radius: 3px; }
.l69-msg-time { display: block; font-size: .65rem; color: var(--_muted); margin-top: .2rem; text-align: right; }
.l69-modal-send {
    display: flex; gap: .5rem; padding: .75rem 1rem;
    border-top: 1px solid var(--_border);
}
.l69-modal-send textarea {
    flex: 1; resize: none; background: var(--_bg-input);
    border: 1px solid var(--_border); color: var(--_text);
    border-radius: 8px; padding: .4rem .65rem;
    font-size: .83rem; font-family: inherit;
}
.l69-modal-send textarea:focus { outline: none; border-color: rgba(180,60,120,.4); }
.l69-modal-send button {
    background: #e056a0; color: #fff; border: none;
    border-radius: 8px; padding: .5rem 1.1rem;
    font-size: .82rem; font-weight: 700; cursor: pointer; transition: opacity .15s;
    flex-shrink: 0; align-self: flex-end;
}
.l69-modal-send button:hover { opacity: .85; }

/* Responsive */
@media (max-width: 640px) {
    .prf-bubbles-row { grid-template-columns: 1fr; }
    .prf-carousel-item { width: 150px; height: 150px; }
    .prf-couple-grid { grid-template-columns: 1fr; }
}
/* Panel derecho modal */
.prf-photo-modal__body {
    display: flex;
    flex-direction: column;
    height: 100%;
    max-height: 90vh;
    overflow: hidden;
    background: var(--bg-card, var(--_bg, #ffffff));
    border-radius: 0 20px 20px 0;
    border-left: 1px solid rgba(128,128,128,.20);
    padding: 1.1rem 1.25rem;
}
/* Texto legible en modo noche */
.prf-photo-modal__body .prf-photo-modal__caption,
.prf-photo-modal__body .prf-comments-header,
.prf-photo-modal__body .prf-modal-comment__author,
.prf-photo-modal__body .prf-modal-comment__text,
.prf-photo-modal__body .prf-comment-empty,
.prf-photo-modal__body .prf-photo-modal__meta {
    color: var(--text-primary, var(--_text, #1a1523));
}
.prf-photo-modal__body .prf-modal-comment__time {
    color: var(--text-muted, var(--_muted, #9590a8));
}
.prf-comments-section  { flex:1; overflow:hidden; display:flex; flex-direction:column; }
.prf-modal-comments    { flex:1; overflow-y:auto; max-height:none; margin-bottom:.6rem; }
@media (max-width:640px) {
    .prf-photo-modal__box { grid-template-columns:1fr; max-height:92vh; overflow-y:auto; }
    .prf-photo-modal__img-wrap { min-height:220px; max-height:45vw; border-radius:16px 16px 0 0; }
    .prf-photo-modal__body { max-height:50vh; overflow-y:auto;     background: var(--_bg);
    border-radius: 0 16px 16px 0;
    padding: 1.1rem 1.25rem;
}
}
/* Filas extra colapsables en "Sobre ellos" pareja */
.prf-row-extra { display: none; }
.prf-sobre-expanded .prf-row-extra { display: flex; }

        /* ── Replies de comentarios ── */
        .prf-comment-reply {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            margin: .3rem 0 .3rem 2.2rem;
            padding: .4rem .6rem;
            background: rgba(255,255,255,.04);
            border-left: 2px solid var(--_pink, #c0392b);
            border-radius: 0 6px 6px 0;
        }
        .prf-comment-reply__ph {
            width: 22px !important;
            height: 22px !important;
            font-size: .65rem !important;
            flex-shrink: 0;
        }
        .prf-comment-reply__body { flex: 1; min-width: 0; }
        .prf-btn-reply {
            background: none;
            border: none;
            color: var(--_pink, #c0392b);
            font-size: .72rem;
            cursor: pointer;
            padding: .15rem .3rem;
            margin-top: .25rem;
            opacity: .8;
            transition: opacity .2s;
        }
        .prf-btn-reply:hover { opacity: 1; }
        .prf-btn-reply:disabled { opacity: .4; cursor: default; }
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

    {{-- Header --}}
    <div class="prf-header">
        <div class="prf-avatar-wrap">
            <img class="prf-avatar"
                 src="{{ $avatarUrl }}"
                 alt="{{ $profile->nickname }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            @if($verificationStatus === 'approved')
                <div class="prf-verified-badge" title="Identidad verificada">&#10003;</div>
            @endif
        </div>
        <div class="prf-info">
            <h1 class="prf-nick">
                {{ $profile->nickname }}
                @if($verificationStatus === 'approved')
                    <span class="prf-badge prf-badge--verified">&#10003; Verificado</span>
                @endif
                <span class="prf-badge prf-badge--type">{{ $typeLabel }}</span>
                <span class="prf-badge prf-badge--member">
                    <img src="{{ $memberIcon }}" alt="{{ $memberLabel }}"
                         style="width:16px;height:16px;object-fit:contain;">
                    {{ $memberLabel }}
                </span>
            </h1>
            @if($location)
                <p class="prf-location">&#128205; {{ $location }}</p>
            @endif
            @if($profile->bio)
                <p class="prf-bio">{{ $profile->bio }}</p>
            @endif
            <div class="prf-follow-row">
                <div class="prf-follow-stats">
                    <span><strong>{{ $followersCount }}</strong> seguidores</span>
                    <span class="prf-follow-sep">&middot;</span>
                    <span><strong>{{ $followingCount }}</strong> siguiendo</span>
                    <span class="prf-follow-sep">&middot;</span>
                    <span><strong>{{ $photosCount }}</strong> fotos</span>
                </div>
                @auth
                    @if(!$isOwnProfile)
                        @if($isFollowing)
                            <form method="POST" action="{{ route('unfollow', $profile->nickname) }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="prf-sb-btn prf-sb-btn--outline">&#10003; Siguiendo</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('follow', $profile->nickname) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="prf-sb-btn prf-sb-btn--primary">+ Seguir</button>
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
                            &#9999;&#65039; Editar perfil
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    {{-- Zona central --}}
    <div class="prf-central">

        {{-- Tarjeta "Sobre ellos" colapsable --}}
        <div class="prf-card prf-sobre-wrap">
            <h2 class="prf-card__title">
                &#128100; Sobre {{ $isPairing ? 'ellos' : ($isUnicorn ? 'ella/el' : 'mi') }}
            </h2>
            <div class="prf-sobre-inner" id="sobre-inner">
                @if($isPairing)
                    <div class="prf-couple-grid">
                        {{-- Miembro principal --}}
                        <div>
                            <p class="prf-couple-col-title prf-couple-col-title--main">
                                <i class="fas fa-{{ $profile->gender === 'masculino' ? 'mars' : 'venus' }}"></i>
                                {{ $mainName ?: ($profile->gender === 'masculino' ? 'El' : 'Ella') }}
                            </p>
                            <div class="prf-data-section">
                                @if($profile->age)
                                <div class="prf-data-row prf-row-visible">
                                    <span class="prf-data-label"><i class="fas fa-birthday-cake"></i> Edad</span>
                                    <span class="prf-data-value">{{ $profile->age }} a&ntilde;os</span>
                                </div>
                                @endif
                                @if($profile->orientation)
                                <div class="prf-data-row prf-row-visible">
                                    <span class="prf-data-label"><i class="fas fa-heart"></i> Orientaci&oacute;n</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->orientation) }}</span>
                                </div>
                                @endif
                                @if($profile->height ?? null)
                                <div class="prf-data-row prf-row-visible">
                                    <span class="prf-data-label"><i class="fas fa-ruler-vertical"></i> Altura</span>
                                    <span class="prf-data-value">{{ $profile->height }} cm</span>
                                </div>
                                @endif
                                @if($profile->weight ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-weight"></i> Peso</span>
                                    <span class="prf-data-value">{{ $profile->weight }} kg</span>
                                </div>
                                @endif
                                @if($profile->nationality ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-flag"></i> Nacionalidad</span>
                                    <span class="prf-data-value">{{ $profile->nationality }}</span>
                                </div>
                                @endif
                                @if($profile->gender === 'femenino' && ($profile->breast_size ?? null))
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-female"></i> Busto</span>
                                    <span class="prf-data-value">{{ $profile->breast_size }}</span>
                                </div>
                                @endif
                                @if($profile->gender === 'masculino' && ($profile->penis_size ?? null))
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-male"></i> Talla</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->penis_size) }}</span>
                                </div>
                                @endif
                                @if($profile->tattoos ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-pen-nib"></i> Tatuajes</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->tattoos) }}</span>
                                </div>
                                @endif
                                @if($profile->piercings ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-circle"></i> Piercings</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->piercings) }}</span>
                                </div>
                                @endif
                                @if($profile->smokes ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-smoking"></i> Fuma</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->smokes) }}</span>
                                </div>
                                @endif
                                @if($profile->drinks ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-wine-glass-alt"></i> Bebe</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->drinks) }}</span>
                                </div>
                                @endif
                                @if($profile->languages ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-language"></i> Idiomas</span>
                                    <span class="prf-data-value">{{ $profile->languages }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Pareja --}}
                        <div>
                            <p class="prf-couple-col-title prf-couple-col-title--partner">
                                <i class="fas fa-{{ $profile->partner_gender === 'masculino' ? 'mars' : 'venus' }}"></i>
                                {{ $partName ?: ($profile->partner_gender === 'masculino' ? 'El' : 'Ella') }}
                            </p>
                            <div class="prf-data-section">
                                @if($profile->partner_age)
                                <div class="prf-data-row prf-row-visible">
                                    <span class="prf-data-label"><i class="fas fa-birthday-cake"></i> Edad</span>
                                    <span class="prf-data-value">{{ $profile->partner_age }} a&ntilde;os</span>
                                </div>
                                @endif
                                @if($profile->partner_orientation ?? null)
                                <div class="prf-data-row prf-row-visible">
                                    <span class="prf-data-label"><i class="fas fa-heart"></i> Orientaci&oacute;n</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->partner_orientation) }}</span>
                                </div>
                                @endif
                                @if($profile->partner_height ?? null)
                                <div class="prf-data-row prf-row-visible">
                                    <span class="prf-data-label"><i class="fas fa-ruler-vertical"></i> Altura</span>
                                    <span class="prf-data-value">{{ $profile->partner_height }} cm</span>
                                </div>
                                @endif
                                @if($profile->partner_weight ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-weight"></i> Peso</span>
                                    <span class="prf-data-value">{{ $profile->partner_weight }} kg</span>
                                </div>
                                @endif
                                @if($profile->partner_nationality ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-flag"></i> Nacionalidad</span>
                                    <span class="prf-data-value">{{ $profile->partner_nationality }}</span>
                                </div>
                                @endif
                                @if($profile->partner_gender === 'femenino' && ($profile->partner_breast_size ?? null))
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-female"></i> Busto</span>
                                    <span class="prf-data-value">{{ $profile->partner_breast_size }}</span>
                                </div>
                                @endif
                                @if($profile->partner_gender === 'masculino' && ($profile->partner_penis_size ?? null))
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-male"></i> Talla</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->partner_penis_size) }}</span>
                                </div>
                                @endif
                                @if($profile->partner_tattoos ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-pen-nib"></i> Tatuajes</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->partner_tattoos) }}</span>
                                </div>
                                @endif
                                @if($profile->partner_piercings ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-circle"></i> Piercings</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->partner_piercings) }}</span>
                                </div>
                                @endif
                                @if($profile->partner_smokes ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-smoking"></i> Fuma</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->partner_smokes) }}</span>
                                </div>
                                @endif
                                @if($profile->partner_drinks ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-wine-glass-alt"></i> Bebe</span>
                                    <span class="prf-data-value">{{ ucfirst($profile->partner_drinks) }}</span>
                                </div>
                                @endif
                                @if($profile->partner_languages ?? null)
                                <div class="prf-data-row prf-row-extra">
                                    <span class="prf-data-label"><i class="fas fa-language"></i> Idiomas</span>
                                    <span class="prf-data-value">{{ $profile->partner_languages }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- Toggle ver mas pareja --}}
                    <button class="prf-sobre-toggle" id="sobre-toggle-pareja" type="button" style="margin-top:.6rem;">
                        <i class="fas fa-chevron-down" id="sobre-icon-pareja"></i>
                        <span id="sobre-label-pareja">Ver m&aacute;s</span>
                    </button>
                @else
                    <div class="prf-data-section" id="sobre-single-wrap">
                        @if($profile->age)
                        <div class="prf-data-row prf-row-visible">
                            <span class="prf-data-label"><i class="fas fa-birthday-cake"></i> Edad</span>
                            <span class="prf-data-value">{{ $profile->age }} a&ntilde;os</span>
                        </div>
                        @endif
                        @if($profile->gender)
                        <div class="prf-data-row prf-row-visible">
                            <span class="prf-data-label"><i class="fas fa-venus-mars"></i> G&eacute;nero</span>
                            <span class="prf-data-value">{{ ucfirst($profile->gender) }}</span>
                        </div>
                        @endif
                        @if($profile->orientation)
                        <div class="prf-data-row prf-row-visible">
                            <span class="prf-data-label"><i class="fas fa-heart"></i> Orientaci&oacute;n</span>
                            <span class="prf-data-value">{{ ucfirst($profile->orientation) }}</span>
                        </div>
                        @endif
                        @if($profile->height ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-ruler-vertical"></i> Altura</span>
                            <span class="prf-data-value">{{ $profile->height }} cm</span>
                        </div>
                        @endif
                        @if($profile->weight ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-weight"></i> Peso</span>
                            <span class="prf-data-value">{{ $profile->weight }} kg</span>
                        </div>
                        @endif
                        @if($profile->nationality ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-flag"></i> Nacionalidad</span>
                            <span class="prf-data-value">{{ $profile->nationality }}</span>
                        </div>
                        @endif
                        @if($profile->gender === 'femenino' && ($profile->breast_size ?? null))
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-female"></i> Busto</span>
                            <span class="prf-data-value">{{ $profile->breast_size }}</span>
                        </div>
                        @endif
                        @if($profile->gender === 'masculino' && ($profile->penis_size ?? null))
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-male"></i> Talla</span>
                            <span class="prf-data-value">{{ ucfirst($profile->penis_size) }}</span>
                        </div>
                        @endif
                        @if($profile->tattoos ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-pen-nib"></i> Tatuajes</span>
                            <span class="prf-data-value">{{ ucfirst($profile->tattoos) }}</span>
                        </div>
                        @endif
                        @if($profile->piercings ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-circle"></i> Piercings</span>
                            <span class="prf-data-value">{{ ucfirst($profile->piercings) }}</span>
                        </div>
                        @endif
                        @if($profile->smokes ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-smoking"></i> Fuma</span>
                            <span class="prf-data-value">{{ ucfirst($profile->smokes) }}</span>
                        </div>
                        @endif
                        @if($profile->drinks ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-wine-glass-alt"></i> Bebe</span>
                            <span class="prf-data-value">{{ ucfirst($profile->drinks) }}</span>
                        </div>
                        @endif
                        @if($profile->languages ?? null)
                        <div class="prf-data-row prf-row-extra">
                            <span class="prf-data-label"><i class="fas fa-language"></i> Idiomas</span>
                            <span class="prf-data-value">{{ $profile->languages }}</span>
                        </div>
                        @endif
                    </div>
                    <button class="prf-sobre-toggle" id="sobre-toggle-single" type="button" style="margin-top:.6rem;">
                        <i class="fas fa-chevron-down" id="sobre-icon-single"></i>
                        <span id="sobre-label-single">Ver m&aacute;s</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Globos Buscan / Para --}}
        <div class="prf-bubbles-row">
            <div class="prf-bubble">
                <div class="prf-bubble__title">&#128269; Buscan</div>
                <div class="prf-tags">
                    @foreach($allLookingFor as $opt)
                        <span class="prf-tag {{ in_array($opt, $lookingFor) ? 'prf-tag--active' : 'prf-tag--inactive' }}">
                            {{ $opt }}
                        </span>
                    @endforeach
                </div>
            </div>
            <div class="prf-bubble">
                <div class="prf-bubble__title">&#10024; Para</div>
                <div class="prf-tags">
                    @foreach($allInterests as $opt)
                        <span class="prf-tag {{ in_array($opt, $interests) ? 'prf-tag--active' : 'prf-tag--inactive' }}">
                            {{ $opt }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Carrusel de fotos --}}
        @if($photos->isNotEmpty())
        <div class="prf-card">
            <h2 class="prf-card__title">
                &#128248; Fotos publicas
                <span style="font-weight:400;color:var(--_muted);font-size:.8rem;margin-left:.25rem;">
                    ({{ $photosCount }})
                </span>
            </h2>
            <div class="prf-carousel-wrap">
                <button class="prf-carousel-btn prf-carousel-btn--prev"
                        id="carousel-prev" aria-label="Anterior">&#8249;</button>
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
                                <span>{{ $iLiked ? 'Likes' : 'Likes' }} {{ $likeCount }}</span>
                                <span><i class="fas fa-comment" style="font-size:.65rem;"></i> Ver</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button class="prf-carousel-btn prf-carousel-btn--next"
                        id="carousel-next" aria-label="Siguiente">&#8250;</button>
            </div>
        </div>
        @endif

    </div>{{-- /.prf-central --}}

</div>{{-- /.prf-wrap --}}

{{-- Modal foto --}}
<div id="photo-modal" class="prf-photo-modal hidden">
    <div class="prf-photo-modal__box">
        <div class="prf-photo-modal__img-wrap">
            <button class="prf-photo-modal__nav prf-photo-modal__nav--prev"
                    id="pm-prev" aria-label="Anterior">&#8249;</button>
            <img id="pm-img" class="prf-photo-modal__img" src="" alt="">
            <button class="prf-photo-modal__nav prf-photo-modal__nav--next"
                    id="pm-next" aria-label="Siguiente">&#8250;</button>
        </div>
        <div class="prf-photo-modal__body">
            <div class="prf-photo-modal__meta">
                <span class="prf-photo-modal__caption" id="pm-caption"></span>
                <div class="prf-photo-modal__actions">
                    @auth
                    <button class="prf-like-btn" id="pm-like-btn" data-photo-uuid="">
                        <span class="prf-like-icon">&#9829;</span>
                        <span id="pm-like-count">0</span>
                    </button>
                    @endauth
                    <button class="prf-photo-modal__close" id="pm-close" aria-label="Cerrar">&#10005;</button>
                </div>
            </div>
            <div class="prf-comments-section">
                <div class="prf-comments-header">
                    <i class="fas fa-comments" style="color:var(--_pink);"></i>
                    Comentarios
                </div>
                <div class="prf-modal-comments" id="pm-comments">
                    <p class="prf-comment-empty">Cargando comentarios&hellip;</p>
                </div>
            </div>
            @auth
            <form class="prf-comment-form" id="pm-comment-form">
                @csrf
                <textarea id="pm-comment-body" name="body"
                          placeholder="Escribe un comentario&hellip;"
                          rows="2" maxlength="500"></textarea>
                <button type="submit">Comentar</button>
            </form>
            @endauth
        </div>
    </div>
</div>

{{-- Modal conversacion --}}
<div id="conv-modal" class="l69-modal-overlay hidden">
    <div class="l69-modal-box">
        <div class="l69-modal-header">
            <span id="conv-modal-name"></span>
            <button id="conv-modal-close" class="l69-modal-close" aria-label="Cerrar">&#10005;</button>
        </div>
        <div id="conv-modal-messages" class="l69-modal-messages"></div>
        <form id="conv-send-form" class="l69-modal-send">
            <input type="hidden" id="conv-receiver-id">
            <textarea id="conv-body" placeholder="Escribe un mensaje&hellip;"
                      rows="2" maxlength="1000"></textarea>
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
/* ═══════════════════════════════════════════════
   LOBBY69 — Profile JS
   ═══════════════════════════════════════════════ */
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const ME   = '{{ Auth::id() }}';

async function postJson(url, data) {
    const r = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    });
    return r.json();
}

function escHtml(s) {
    return String(s ?? '')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;');
}

function fmtTime(iso) {
    if (!iso) return '';
    try { return new Date(iso).toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'}); }
    catch { return ''; }
}

/* ── 0. Detector de contacto privado ── */
function contieneContacto(txt) {
    var t = txt || '';
    // Emails
    if (/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/.test(t)) return true;
    // Teléfonos: 7+ dígitos seguidos (con espacios/guiones/puntos opcionales)
    if (/(\+?[\d\s\-().]{7,}\d)/.test(t)) return true;
    // Handles @usuario
    if (/@[a-zA-Z0-9_.]{3,}/.test(t)) return true;
    // URLs
    if (/(https?:\/\/|www\.)[^\s]+/i.test(t)) return true;
    // Dominios comunes
    if (/[a-zA-Z0-9\-]+\.(com|net|org|mx|io|me|co|app|link|ly|gl|to)\b/i.test(t)) return true;
    // Redes sociales por nombre
    if (/\b(whatsapp|wsp|telegram|tg|signal|snapchat|instagram|insta|tiktok|facebook|fb|twitter|onlyfans|fansly)\b/i.test(t)) return true;
    return false;
}

/* ── 1. Toggle Sobre mí / ellos ── */
(function(){
    // Forzar ocultar via style (override al CSS .prf-row-extra { display:none })
    function initToggle(btnId, selector, iconId, labelId) {
        var btn = document.getElementById(btnId);
        if (!btn) return;
        var rows = Array.from(document.querySelectorAll(selector));
        if (!rows.length) { btn.style.display = 'none'; return; }
        // Ocultar todas al inicio
        rows.forEach(function(r){ r.style.setProperty('display','none','important'); });
        btn.style.display = '';
        btn.addEventListener('click', function(){
            var exp = btn.getAttribute('data-exp') === '1';
            rows.forEach(function(r){
                if (exp) {
                    r.style.setProperty('display','none','important');
                } else {
                    r.style.removeProperty('display');
                    r.style.display = 'flex';
                }
            });
            btn.setAttribute('data-exp', exp ? '0' : '1');
            var icon  = document.getElementById(iconId);
            var label = document.getElementById(labelId);
            if (icon)  icon.style.transform = exp ? '' : 'rotate(180deg)';
            if (label) label.innerHTML = exp ? 'Ver m&aacute;s' : 'Ver menos';
        });
    }
    initToggle('sobre-toggle-pareja', '.prf-couple-grid .prf-row-extra', 'sobre-icon-pareja', 'sobre-label-pareja');
    initToggle('sobre-toggle-single', '#sobre-single-wrap .prf-row-extra', 'sobre-icon-single', 'sobre-label-single');
})();

/* ── 2. Carrusel ── */
(function(){
    var track = document.getElementById('carousel-track');
    var prev  = document.getElementById('carousel-prev');
    var next  = document.getElementById('carousel-next');
    if (!track) return;
    if (prev) prev.addEventListener('click', function(){ track.scrollBy({left:-412,behavior:'smooth'}); });
    if (next) next.addEventListener('click', function(){ track.scrollBy({left: 412,behavior:'smooth'}); });
})();

/* ── 3. Modal de fotos ── */
(function(){
    var overlay     = document.getElementById('photo-modal');
    var imgEl       = document.getElementById('pm-img');
    var captionEl   = document.getElementById('pm-caption');
    var likeBtn     = document.getElementById('pm-like-btn');
    var likeCountEl = document.getElementById('pm-like-count');
    var commWrap    = document.getElementById('pm-comments');
    var commentForm = document.getElementById('pm-comment-form');
    var commentBody = document.getElementById('pm-comment-body');
    var closeBtn    = document.getElementById('pm-close');
    var prevBtn     = document.getElementById('pm-prev');
    var nextBtn     = document.getElementById('pm-next');

    var items        = Array.from(document.querySelectorAll('[data-photo-id]'));
    var currentIdx   = 0;
    var currentId    = null;

    if (!overlay) return;

    function abrir(idx) {
        if (idx < 0 || idx >= items.length) return;
        currentIdx = idx;
        var el = items[idx];
        currentId = el.dataset.photoId;

        overlay.classList.remove('hidden');
        overlay.style.display = 'flex';

        // Imagen directa
        imgEl.src = '/fotos/' + currentId + '/ver';
        if (captionEl) captionEl.textContent = el.dataset.caption || '';
        if (likeCountEl) likeCountEl.textContent = el.dataset.likes || 0;
        if (likeBtn) likeBtn.classList.toggle('liked', el.dataset.iliked === '1');
        if (commWrap) commWrap.innerHTML = '<p style="color:var(--theme-muted);font-size:.8rem;padding:.5rem">Cargando comentarios&hellip;</p>';

        // Info via API (likes actualizados + comentarios)
        fetch('/fotos/' + currentId + '/info', {
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (d.photo) {
                if (likeCountEl) likeCountEl.textContent = d.photo.likes_count || 0;
                if (likeBtn) likeBtn.classList.toggle('liked', !!d.photo.user_liked);
            }
            renderComentarios(d.photo ? (d.photo.comments || []) : []);
        })
        .catch(function(){
            if (commWrap) commWrap.innerHTML = '<p style="color:#e74c3c;padding:.5rem">Error al cargar comentarios.</p>';
        });
    }

    function cerrar() {
        overlay.classList.add('hidden');
        overlay.style.display = 'none';
        currentId = null;
        imgEl.src = '';
    }

    function renderComentarios(list) {
        if (!commWrap) return;
        if (!list.length) {
            commWrap.innerHTML = '<p class="prf-comment-empty">Sin comentarios a&uacute;n.</p>';
            return;
        }
        const PROFILE_OWNER = '{{ $profile->user_id ?? "" }}';
        const isOwner = ME && PROFILE_OWNER && ME === PROFILE_OWNER;

        commWrap.innerHTML = list.map(function(c){
            // Replies anidadas bajo este comentario
            var repliesHtml = '';
            if (c.replies && c.replies.length) {
                repliesHtml = c.replies.map(function(r){
                    return '<div class="prf-comment-reply">'
                        + '<div class="prf-modal-comment__ph prf-comment-reply__ph">'
                        + escHtml((r.user_nick||'?').charAt(0).toUpperCase())
                        + '</div>'
                        + '<div class="prf-comment-reply__body">'
                        + '<a class="prf-modal-comment__author" href="' + (r.commenter_nick ? '/u/' + encodeURIComponent(r.commenter_nick) : '#') + '" style="color:inherit;text-decoration:none;font-weight:600;" target="_self">' + escHtml(r.user_nick||'') + '</a>'
                        + '<div class="prf-modal-comment__text">' + escHtml(r.body) + '</div>'
                        + '</div></div>';
                }).join('');
            }

            // Botón reply solo para el dueño del perfil
            var replyBtn = isOwner && !c.parent_id
                ? '<button class="prf-btn-reply" data-comment-id="' + escHtml(c.id) + '">'
                  + '<i class="fas fa-reply"></i> Responder</button>'
                : '';

            return '<div class="prf-modal-comment" data-id="' + escHtml(c.id) + '">'
                + '<div class="prf-modal-comment__avatar">'
                + '<div class="prf-modal-comment__ph">'
                + escHtml((c.user_nick||'?').charAt(0).toUpperCase())
                + '</div></div>'
                + '<div class="prf-modal-comment__body">'
                + '<a class="prf-modal-comment__author" href="' + (c.commenter_nick ? '/u/' + encodeURIComponent(c.commenter_nick) : '#') + '" style="color:inherit;text-decoration:none;font-weight:600;" target="_self">' + escHtml(c.user_nick||'Anónimo') + '</a>'
                + ' <span class="prf-modal-comment__time">' + escHtml(c.created_at||'') + '</span>'
                + '<div class="prf-modal-comment__text">' + escHtml(c.body) + '</div>'
                + replyBtn
                + '</div>'
                + repliesHtml
                + '</div>';
        }).join('');
        commWrap.scrollTop = commWrap.scrollHeight;

        // Listener botón reply
        commWrap.querySelectorAll('.prf-btn-reply').forEach(function(btn){
            btn.addEventListener('click', function(){
                var commentId = btn.dataset.commentId;
                var texto = prompt('Escribe tu respuesta:');
                if (!texto || !texto.trim()) return;
                if (typeof contieneContacto === 'function' && contieneContacto(texto)) {
                    alert('⚠️ No puedes incluir emails, teléfonos ni redes sociales.');
                    return;
                }
                btn.disabled = true;
                postJson('/fotos/' + currentId + '/comentarios/' + commentId + '/reply', { body: texto.trim() })
                    .then(function(res){
                        if (res.success) {
                            // Insertar reply visualmente sin recargar
                            var replyHtml = '<div class="prf-comment-reply">'
                                + '<div class="prf-modal-comment__ph prf-comment-reply__ph">'
                                + escHtml((res.reply.user_nick||'?').charAt(0).toUpperCase())
                                + '</div>'
                                + '<div class="prf-comment-reply__body">'
                        + '<a class="prf-modal-comment__author" href="' + (res.reply.commenter_nick ? '/u/' + encodeURIComponent(res.reply.commenter_nick) : '#') + '" style="color:inherit;text-decoration:none;font-weight:600;">' + escHtml(res.reply.user_nick||'') + '</a>'
                                + '<div class="prf-modal-comment__text">' + escHtml(res.reply.body) + '</div>'
                                + '</div></div>';
                            btn.closest('.prf-modal-comment').insertAdjacentHTML('afterend', replyHtml);
                            btn.remove();
                        }
                    })
                    .catch(function(){ btn.disabled = false; });
            });
        });
    }

    // Clicks en carrusel
    items.forEach(function(el, i){
        el.addEventListener('click', function(){ abrir(i); });
    });

    // Navegación
    if (prevBtn) prevBtn.addEventListener('click', function(e){ e.stopPropagation(); abrir(currentIdx-1); });
    if (nextBtn) nextBtn.addEventListener('click', function(e){ e.stopPropagation(); abrir(currentIdx+1); });

    // Cerrar
    if (closeBtn) closeBtn.addEventListener('click', cerrar);
    overlay.addEventListener('click', function(e){ if(e.target===overlay) cerrar(); });
    document.addEventListener('keydown', function(e){
        if (!currentId) return;
        if (e.key==='Escape') cerrar();
        if (e.key==='ArrowLeft')  abrir(currentIdx-1);
        if (e.key==='ArrowRight') abrir(currentIdx+1);
    });

    // Like
    if (likeBtn) {
        likeBtn.addEventListener('click', function(){
            if (!currentId) return;
            postJson('/fotos/' + currentId + '/like', {})
                .then(function(res){
                    if (likeCountEl) likeCountEl.textContent = res.likes_count || 0;
                    likeBtn.classList.toggle('liked', !!res.liked);
                    if (items[currentIdx]) {
                        items[currentIdx].dataset.likes = res.likes_count || 0;
                        items[currentIdx].dataset.iliked = res.liked ? '1' : '0';
                    }
                });
        });
    }

    // Comentar
    if (commentForm) {
        commentForm.addEventListener('submit', function(e){
            e.preventDefault();
            var body = commentBody ? commentBody.value.trim() : '';
            if (!body || !currentId) return;
            if (contieneContacto(body)) {
                alert('No se permite incluir correos, redes sociales ni números telefónicos en los comentarios.');
                return;
            }
            var btn = commentForm.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            postJson('/fotos/' + currentId + '/comentar', { body: body })
                .then(function(res){
                    if (res.success) {
                        if (commentBody) commentBody.value = '';
                        var div = document.createElement('div');
                        div.className = 'prf-modal-comment prf-modal-comment--new';
                        div.innerHTML = '<div class="prf-modal-comment__body">'
                    + '<a class="prf-modal-comment__author" href="' + (res.comment.commenter_nick ? '/u/' + encodeURIComponent(res.comment.commenter_nick) : '#') + '" style="color:inherit;text-decoration:none;font-weight:600;">' + escHtml(res.comment.user_nick) + '</a>'
                            + '<div class="prf-modal-comment__text">' + escHtml(res.comment.body) + '</div>'
                            + '</div>';
                        if (commWrap) { commWrap.appendChild(div); commWrap.scrollTop = commWrap.scrollHeight; }
                    } else {
                        alert(res.message || 'Error al comentar.');
                    }
                })
                .catch(function(){ alert('Error de red.'); })
                .finally(function(){ if (btn) btn.disabled = false; });
        });
    }
})();

/* ── 4. Modal conversación / mensajes ── */
(function(){
    var modal      = document.getElementById('conv-modal');
    var modalName  = document.getElementById('conv-modal-name');
    var msgWrap    = document.getElementById('conv-modal-messages');
    var sendForm   = document.getElementById('conv-send-form');
    var receiverEl = document.getElementById('conv-receiver-id');
    var bodyEl     = document.getElementById('conv-body');
    var closeBtn   = document.getElementById('conv-modal-close');
    var receiverId = '{{ $profile->user_id }}';
    var nickName   = '{{ addslashes($profile->nickname ?? "") }}';

    if (!modal) return;

    function abrir() {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        if (modalName)  modalName.textContent = nickName;
        if (receiverEl) receiverEl.value = receiverId;
        if (bodyEl)     { bodyEl.value = ''; bodyEl.focus(); }
        if (msgWrap)    msgWrap.innerHTML = '<p style="color:var(--theme-muted);font-size:.8rem;text-align:center;padding:1rem">Cargando mensajes&hellip;</p>';

        fetch('/mensajes/conversacion/' + receiverId, {
            headers: { 'Accept':'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(function(r){ return r.json(); })
        .then(function(data){
            if (!msgWrap) return;
            var msgs = data.messages || [];
            if (!msgs.length) {
                msgWrap.innerHTML = '<p style="color:var(--theme-muted);font-size:.8rem;text-align:center;padding:1rem">Sin mensajes a&uacute;n. &iexcl;S&eacute; el primero!</p>';
                return;
            }
            msgWrap.innerHTML = msgs.map(function(m){
                var mine = String(m.sender_id) === String(ME);
                return '<div class="l69-msg-bubble ' + (mine ? 'mine' : 'theirs') + '">'
                    + escHtml(m.body)
                    + '<span class="l69-msg-time">' + fmtTime(m.created_at) + '</span>'
                    + '</div>';
            }).join('');
            msgWrap.scrollTop = msgWrap.scrollHeight;
        })
        .catch(function(){
            if (msgWrap) msgWrap.innerHTML = '<p style="color:#e74c3c;padding:1rem">Error al cargar mensajes.</p>';
        });
    }

    function cerrar() {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    var btn1 = document.getElementById('btn-msg-profile');
    var btn2 = document.getElementById('btn-msg-profile-header');
    if (btn1) btn1.addEventListener('click', abrir);
    if (btn2) btn2.addEventListener('click', abrir);

    if (closeBtn) closeBtn.addEventListener('click', cerrar);
    modal.addEventListener('click', function(e){ if(e.target===modal) cerrar(); });

    if (sendForm) {
        sendForm.addEventListener('submit', function(e){
            e.preventDefault();
            var body = bodyEl ? bodyEl.value.trim() : '';
            if (!body) return;
            if (contieneContacto(body)) {
                alert('No se permite incluir correos, redes sociales ni números telefónicos en los mensajes.');
                return;
            }
            var btn = sendForm.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            postJson('{{ route("messages.send") }}', { receiver_id: receiverId, body: body })
                .then(function(res){
                    if (res.ok) {
                        if (bodyEl) bodyEl.value = '';
                        var div = document.createElement('div');
                        div.className = 'l69-msg-bubble mine';
                        div.innerHTML = escHtml(body)
                            + '<span class="l69-msg-time">ahora</span>';
                        if (msgWrap) { msgWrap.appendChild(div); msgWrap.scrollTop = msgWrap.scrollHeight; }
                    } else {
                        alert(res.error || 'Error al enviar el mensaje.');
                    }
                })
                .catch(function(){ alert('Error de red al enviar.'); })
                .finally(function(){ if (btn) btn.disabled = false; });
        });
    }
})();
</script>
<script>
// ── Botón Agregar amigo ──────────────────────────────────
(function() {
    var btn = document.getElementById('btn-add-friend');
    if (!btn) return;
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    btn.addEventListener('click', function() {
        var targetId = btn.dataset.target;
        if (!targetId) return;
        btn.disabled    = true;
        btn.textContent = 'Enviando…';

        fetch('/amigos/solicitud', {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  CSRF
            },
            body: JSON.stringify({ target_id: targetId })
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.ok) {
                btn.innerHTML   = '<i class="fas fa-clock"></i> Solicitud enviada';
                btn.disabled    = true;
                btn.classList.remove('prf-sb-btn--primary');
                btn.classList.add('prf-sb-btn--outline');
                btn.style.opacity = '.7';
                btn.style.cursor  = 'default';
            } else {
                btn.disabled    = false;
                btn.innerHTML   = '<i class="fas fa-user-plus"></i> Agregar amigo';
                alert(d.error ?? 'Error al enviar solicitud');
            }
        })
        .catch(function() {
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-user-plus"></i> Agregar amigo';
        });
    });
})();
</script>
@endpush
