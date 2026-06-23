<?php
/**
 * fix_dashboard_styles.php
 * Rediseño visual del dashboard manteniendo tonos existentes
 * Ejecutar: C:\php\php.exe fix_dashboard_styles.php
 */

$base = __DIR__;

// ============================================================
// DASHBOARD — Rediseño visual completo
// ============================================================
$dashboard = <<<'BLADE'
@extends('layouts.app')
@section('title', 'Inicio')

{{-- ══ SIDEBAR IZQUIERDO ══ --}}
@push('sidebar-left')
@php
    $sbUser    = auth()->user();
    $sbProfile = $sbUser->profile ?? null;
    $sbAvatar  = $sbProfile?->avatar_url ?? asset('img/default-avatar.svg');
    $sbNick    = $sbProfile?->nickname ?? $sbUser->name ?? 'Usuario';
    $sbMember  = $sbUser->membership_type ?? 'trial';
    $memberCfg = [
        'trial'      => ['label'=>'Trial',      'icon'=>'fa-clock',   'color'=>'#9ca3af', 'bg'=>'rgba(156,163,175,.15)'],
        'explorer'   => ['label'=>'Explorer',   'icon'=>'fa-compass', 'color'=>'#60a5fa', 'bg'=>'rgba(96,165,250,.15)'],
        'connectors' => ['label'=>'Connectors', 'icon'=>'fa-link',    'color'=>'#34d399', 'bg'=>'rgba(52,211,153,.15)'],
        'influencer' => ['label'=>'Influencer', 'icon'=>'fa-star',    'color'=>'#a78bfa', 'bg'=>'rgba(167,139,250,.15)'],
        'vip_elite'  => ['label'=>'VIP Elite',  'icon'=>'fa-gem',     'color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,.15)'],
        'vitalicio'  => ['label'=>'Vitalicio',  'icon'=>'fa-crown',   'color'=>'#e056a0', 'bg'=>'rgba(224,86,160,.15)'],
    ];
    $mCfg = $memberCfg[$sbMember] ?? $memberCfg['trial'];
    $lastSeen = $sbUser->last_seen_at
        ? \Carbon\Carbon::parse($sbUser->last_seen_at)->diffForHumans()
        : 'Primera vez';

    // Calcular progreso
    $fields = ['nickname','bio','profile_type','age','gender','location_country'];
    $filled = collect($fields)->filter(fn($f) => !empty($sbProfile?->$f))->count();
    $progress = $sbProfile ? (int)(($filled / count($fields)) * 100) : 0;
    if ($sbProfile?->avatar_url) $progress = min(100, $progress + 10);

    // Stats rápidas
    $statsViews   = \Illuminate\Support\Facades\DB::table('profile_views')->where('viewed_id', $sbUser->id)->count();
    $statsPhotos  = \Illuminate\Support\Facades\DB::table('photos')->where('user_id', $sbUser->id)->where('status','approved')->count();
    $statsLikes   = \Illuminate\Support\Facades\DB::table('photo_likes')
                        ->join('photos', 'photo_likes.photo_id', '=', 'photos.id')
                        ->where('photos.user_id', $sbUser->id)->count();
@endphp

{{-- ── Tarjeta principal de perfil ── --}}
<div class="dsb-profile-card">
    {{-- Avatar con ring de membresía --}}
    <div class="dsb-avatar-ring" style="--ring-color: {{ $mCfg['color'] }};">
        <img src="{{ $sbAvatar }}"
             alt="{{ $sbNick }}"
             class="dsb-avatar-img"
             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        @if($sbUser->identity_verified ?? false)
        <span class="dsb-avatar-verified"><i class="fas fa-check"></i></span>
        @endif
    </div>

    {{-- Info básica --}}
    <h3 class="dsb-profile-nick">{{ $sbNick }}</h3>
    <p class="dsb-profile-location">
        @if($sbProfile?->city)
            <i class="fas fa-map-marker-alt"></i>
            {{ $sbProfile->city }}@if($sbProfile?->location_country), {{ $sbProfile->location_country }}@endif
        @endif
    </p>

    {{-- Badge membresía --}}
    <span class="dsb-membership-badge"
          style="color:{{ $mCfg['color'] }};background:{{ $mCfg['bg'] }};border-color:{{ $mCfg['color'] }}44;">
        <i class="fas {{ $mCfg['icon'] }}"></i> {{ $mCfg['label'] }}
    </span>

    {{-- Tipo de perfil --}}
    <p class="dsb-profile-type">
        @if($sbProfile?->profile_type === 'pareja') 👫 Pareja
        @elseif($sbProfile?->profile_type === 'unicornio') ⭐ Unicornio
        @else 👤 Single @endif
        @if($sbUser->identity_verified ?? false)
        · <span style="color:#22c55e;font-size:.75rem;"><i class="fas fa-check-circle"></i> Verificado</span>
        @endif
    </p>

    {{-- Grid de stats ── --}}
    <div class="dsb-stats-grid">
        <div class="dsb-stat-box">
            <i class="fas fa-eye" style="color:#e056a0;"></i>
            <span class="dsb-stat-num">{{ $statsViews }}</span>
            <span class="dsb-stat-lbl">Vistas</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-heart" style="color:#ef4444;"></i>
            <span class="dsb-stat-num">{{ $statsLikes }}</span>
            <span class="dsb-stat-lbl">Likes</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-images" style="color:#60a5fa;"></i>
            <span class="dsb-stat-num">{{ $statsPhotos }}</span>
            <span class="dsb-stat-lbl">Fotos</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-clock" style="color:#fbbf24;"></i>
            <span class="dsb-stat-num" style="font-size:.68rem;line-height:1.2;">
                {{ \Carbon\Carbon::parse($sbUser->last_seen_at ?? now())->format('d/m H:i') }}
            </span>
            <span class="dsb-stat-lbl">Última vez</span>
        </div>
    </div>

    {{-- Progreso del perfil --}}
    @if($progress < 100)
    <div class="dsb-progress-wrap">
        <div class="dsb-progress-label">
            <span>Completa tu perfil</span>
            <span style="color:#e056a0;font-weight:700;">{{ $progress }}%</span>
        </div>
        <div class="dsb-progress-bar">
            <div class="dsb-progress-fill" style="width:{{ $progress }}%;"></div>
        </div>
    </div>
    @endif

    {{-- Acciones rápidas --}}
    <div class="dsb-quick-actions">
        <a href="{{ route('profile.edit') }}" class="dsb-action-btn dsb-action-btn--primary">
            <i class="fas fa-user-edit"></i> Editar perfil
        </a>
        <a href="{{ route('photos.index') }}" class="dsb-action-btn dsb-action-btn--ghost">
            <i class="fas fa-camera"></i> Mis fotos
        </a>
    </div>
</div>

{{-- ── Te han visitado ── --}}
<div class="dsb-section-card">
    <div class="dsb-section-header">
        <span><i class="fas fa-eye"></i> Te han visitado</span>
        @if($whoViewedMeCount > 0)
        <span class="dsb-section-badge">{{ $whoViewedMeCount }}</span>
        @endif
    </div>
    @forelse($whoViewedMe as $view)
    @php $vp = $view->viewer?->profile; @endphp
    <a href="{{ $vp?->nickname ? route('profile.show', $vp->nickname) : '#' }}"
       class="dsb-user-row">
        <div class="dsb-user-avatar-wrap">
            <img src="{{ $vp?->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        </div>
        <div class="dsb-user-info">
            <span class="dsb-user-nick">{{ $vp?->nickname ?? 'Usuario' }}</span>
            <span class="dsb-user-time">
                <i class="fas fa-clock"></i>
                {{ \Carbon\Carbon::parse($view->viewed_at)->diffForHumans() }}
            </span>
        </div>
        <i class="fas fa-chevron-right" style="color:rgba(226,217,243,.2);font-size:.7rem;"></i>
    </a>
    @empty
    <div class="dsb-empty-state">
        <i class="fas fa-eye-slash"></i>
        <span>Aún nadie ha visitado tu perfil</span>
    </div>
    @endforelse
    @if($whoViewedMeCount > 5)
    <a href="#" class="dsb-see-more">Ver todos ({{ $whoViewedMeCount }}) →</a>
    @endif
</div>

{{-- ── Perfiles que visité ── --}}
<div class="dsb-section-card">
    <div class="dsb-section-header">
        <span><i class="fas fa-walking"></i> Perfiles que visité</span>
    </div>
    @forelse($iViewed as $view)
    @php $vp = $view->viewed?->profile; @endphp
    <a href="{{ $vp?->nickname ? route('profile.show', $vp->nickname) : '#' }}"
       class="dsb-user-row">
        <div class="dsb-user-avatar-wrap">
            <img src="{{ $vp?->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        </div>
        <div class="dsb-user-info">
            <span class="dsb-user-nick">{{ $vp?->nickname ?? 'Usuario' }}</span>
            <span class="dsb-user-time">
                <i class="fas fa-clock"></i>
                {{ \Carbon\Carbon::parse($view->viewed_at)->diffForHumans() }}
            </span>
        </div>
        <i class="fas fa-chevron-right" style="color:rgba(226,217,243,.2);font-size:.7rem;"></i>
    </a>
    @empty
    <div class="dsb-empty-state">
        <i class="fas fa-compass"></i>
        <span>No has visitado perfiles aún</span>
    </div>
    @endforelse
    @if($iViewedCount > 5)
    <a href="#" class="dsb-see-more">Ver historial completo →</a>
    @endif
</div>
@endpush

{{-- ══ SIDEBAR DERECHO ══ --}}
@push('sidebar-right')

{{-- ── En línea ahora ── --}}
<div class="dsb-section-card">
    <div class="dsb-section-header">
        <span>
            <span class="dsb-online-dot"></span>
            En línea ahora
        </span>
        @if($onlineUsers->count() > 0)
        <span class="dsb-section-badge" style="background:rgba(34,197,94,.15);color:#22c55e;">
            {{ $onlineUsers->count() }}
        </span>
        @endif
    </div>
    @forelse($onlineUsers->take(8) as $ou)
    <a href="{{ route('profile.show', $ou->nickname) }}" class="dsb-user-row">
        <div class="dsb-user-avatar-wrap">
            <img src="{{ $ou->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span class="dsb-online-indicator"></span>
        </div>
        <div class="dsb-user-info">
            <span class="dsb-user-nick">{{ $ou->nickname }}</span>
            <span class="dsb-user-time" style="color:#22c55e;">
                <i class="fas fa-circle" style="font-size:.45rem;"></i> En línea
            </span>
        </div>
        <a href="{{ route('profile.show', $ou->nickname) }}"
           class="dsb-ver-btn">Ver</a>
    </a>
    @empty
    <div class="dsb-empty-state">
        <i class="fas fa-moon"></i>
        <span>No hay usuarios en línea</span>
    </div>
    @endforelse
</div>

{{-- ── Nuevos miembros ── --}}
<div class="dsb-section-card">
    <div class="dsb-section-header">
        <span><i class="fas fa-user-plus"></i> Nuevos miembros</span>
    </div>
    @forelse($newUsers as $nu)
    <a href="{{ route('profile.show', $nu->nickname) }}" class="dsb-user-row">
        <div class="dsb-user-avatar-wrap">
            <img src="{{ $nu->avatar_url ?? asset('img/default-avatar.svg') }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        </div>
        <div class="dsb-user-info">
            <span class="dsb-user-nick">{{ $nu->nickname }}</span>
            <span class="dsb-user-time">
                @if($nu->profile_type==='pareja') 👫
                @elseif($nu->profile_type==='unicornio') ⭐
                @else 👤 @endif
                {{ \Carbon\Carbon::parse($nu->created_at)->diffForHumans() }}
            </span>
        </div>
        <a href="{{ route('profile.show', $nu->nickname) }}"
           class="dsb-ver-btn">Ver</a>
    </a>
    @empty
    <div class="dsb-empty-state">
        <i class="fas fa-users"></i>
        <span>Sin nuevos miembros recientes</span>
    </div>
    @endforelse
</div>
@endpush

{{-- ══ CONTENIDO CENTRAL ══ --}}
@section('content')

{{-- Tabs --}}
<div class="dsb-feed-tabs">
    <a href="?tab=new"
       class="dsb-feed-tab {{ $tab==='new' ? 'is-active' : '' }}">
        <i class="fas fa-clock"></i> Fotos Nuevas
    </a>
    <a href="?tab=likes"
       class="dsb-feed-tab {{ $tab==='likes' ? 'is-active' : '' }}">
        <i class="fas fa-fire"></i> Más Populares
    </a>
</div>

{{-- Grid de fotos --}}
<div class="dsb-feed-grid" id="feedGrid">
    @forelse($feed as $photo)
    @php
        $owner     = $photo->user?->profile;
        $ownerNick = $owner?->nickname ?? $photo->user?->name ?? 'Usuario';
        $ownerAvatar = $owner?->avatar_url ?? asset('img/default-avatar.svg');
        $isLiked   = $photo->isLikedBy(auth()->id());
    @endphp
    <div class="dsb-photo-card" data-photo-id="{{ $photo->id }}">
        {{-- Header de la tarjeta --}}
        <div class="dsb-photo-card__header">
            <a href="{{ $owner?->nickname ? route('profile.show', $owner->nickname) : '#' }}"
               class="dsb-photo-card__owner">
                <img src="{{ $ownerAvatar }}"
                     alt="{{ $ownerNick }}"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                <div>
                    <span class="dsb-photo-card__owner-nick">{{ $ownerNick }}</span>
                    @if($owner?->city)
                    <span class="dsb-photo-card__owner-loc">
                        <i class="fas fa-map-marker-alt"></i> {{ $owner->city }}
                    </span>
                    @endif
                </div>
            </a>
        </div>

        {{-- Foto --}}
        <div class="dsb-photo-card__img-wrap">
            <img src="{{ asset('storage/' . $photo->file_path) }}"
                 alt="{{ $ownerNick }}"
                 class="dsb-photo-card__img"
                 loading="lazy"
                 onerror="this.parentElement.style.background='#1a1028'">
        </div>

        {{-- Footer con acciones --}}
        <div class="dsb-photo-card__footer">
            @if($photo->caption)
            <p class="dsb-photo-card__caption">{{ Str::limit($photo->caption, 80) }}</p>
            @endif
            <div class="dsb-photo-card__actions">
                <button class="dsb-like-btn {{ $isLiked ? 'is-liked' : '' }}"
                        data-photo-id="{{ $photo->id }}"
                        title="{{ $isLiked ? 'Quitar like' : 'Me gusta' }}">
                    <i class="{{ $isLiked ? 'fas' : 'far' }} fa-heart"></i>
                    <span>{{ $photo->likes_count }}</span>
                </button>
                <button class="dsb-comment-btn" data-photo-id="{{ $photo->id }}"
                        title="Comentarios">
                    <i class="far fa-comment"></i>
                    <span>{{ $photo->comments_count }}</span>
                </button>
                <a href="{{ $owner?->nickname ? route('profile.show', $owner->nickname) : '#' }}"
                   class="dsb-profile-btn" title="Ver perfil">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="dsb-feed-empty">
        <i class="fas fa-images"></i>
        <p>No hay fotos disponibles aún</p>
        <a href="{{ route('photos.index') }}" class="dsb-action-btn dsb-action-btn--primary">
            Subir mis fotos
        </a>
    </div>
    @endforelse
</div>

{{-- Cargar más --}}
@if($feed->hasMorePages())
<div style="text-align:center;margin:1.5rem 0;" id="loadMoreWrap">
    <button class="dsb-load-more-btn" id="loadMoreBtn"
            data-page="2" data-tab="{{ $tab }}">
        <i class="fas fa-chevron-down"></i> Cargar más fotos
    </button>
</div>
@endif

{{-- ══ MODAL DE FOTO ══ --}}
<div class="dsb-modal" id="photoModal" style="display:none;">
    <div class="dsb-modal__overlay" id="photoModalOverlay"></div>
    <div class="dsb-modal__box">
        <button class="dsb-modal__close" id="photoModalClose">
            <i class="fas fa-times"></i>
        </button>
        <div class="dsb-modal__layout">
            {{-- Foto grande --}}
            <div class="dsb-modal__photo-side">
                <img src="" alt="" id="modalPhoto" class="dsb-modal__photo">
                <div class="dsb-modal__photo-actions">
                    <button class="dsb-like-btn" id="modalLikeBtn" data-photo-id="">
                        <i class="far fa-heart"></i>
                        <span id="modalLikeCount">0</span>
                    </button>
                    <span class="dsb-comment-count">
                        <i class="far fa-comment"></i>
                        <span id="modalCommentCount">0</span>
                    </span>
                </div>
            </div>
            {{-- Panel lateral --}}
            <div class="dsb-modal__panel">
                {{-- Owner --}}
                <div class="dsb-modal__owner">
                    <a href="#" id="modalOwnerLink" class="dsb-modal__owner-link">
                        <img src="" alt="" id="modalOwnerAvatar" class="dsb-modal__owner-avatar">
                        <div>
                            <div class="dsb-modal__owner-nick" id="modalOwnerNick"></div>
                            <div class="dsb-modal__owner-meta" id="modalOwnerMeta"></div>
                        </div>
                    </a>
                    <a href="#" id="modalProfileLink" class="dsb-ver-btn">
                        Ver perfil
                    </a>
                </div>
                <p class="dsb-modal__caption" id="modalCaption" style="display:none;"></p>
                {{-- Comentarios --}}
                <div class="dsb-modal__comments" id="commentsList">
                    <div class="dsb-empty-state">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Cargando...</span>
                    </div>
                </div>
                {{-- Form comentario --}}
                <form class="dsb-modal__comment-form" id="commentForm">
                    @csrf
                    <input type="hidden" id="commentPhotoId">
                    <div class="dsb-modal__comment-row">
                        <img src="{{ auth()->user()->profile?->avatar_url ?? asset('img/default-avatar.svg') }}"
                             class="dsb-modal__comment-avatar"
                             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                        <input type="text" id="commentBody"
                               placeholder="Escribe un comentario..."
                               class="dsb-modal__comment-input"
                               maxlength="500" autocomplete="off">
                        <button type="submit" class="dsb-modal__comment-send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <p class="dsb-modal__comment-note" id="commentNote">
                        <i class="fas fa-info-circle"></i>
                        Los comentarios se publican tras revisión del admin
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ══════════════════════════════════════════════════
   DASHBOARD — Estilos visuales completos
   ══════════════════════════════════════════════════ */

/* ── Tarjeta principal de perfil ── */
.dsb-profile-card {
    background: linear-gradient(160deg, rgba(30,15,50,.95) 0%, rgba(20,10,35,.95) 100%);
    border: 1px solid rgba(180,60,120,.25);
    border-radius: 20px;
    padding: 1.5rem 1.25rem 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: .5rem;
}
.dsb-avatar-ring {
    position: relative;
    width: 84px; height: 84px;
    border-radius: 50%;
    padding: 3px;
    background: linear-gradient(135deg, var(--ring-color, #e056a0), rgba(142,68,173,.6));
    margin-bottom: .25rem;
}
.dsb-avatar-img {
    width: 100%; height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #12091e;
}
.dsb-avatar-verified {
    position: absolute; bottom: 2px; right: 2px;
    width: 22px; height: 22px;
    background: #22c55e;
    border-radius: 50%;
    border: 2px solid #12091e;
    display: flex; align-items: center; justify-content: center;
    font-size: .6rem; color: #fff;
}
.dsb-profile-nick {
    font-size: 1.1rem; font-weight: 800;
    color: #fff; margin: 0;
}
.dsb-profile-location {
    font-size: .78rem; color: rgba(226,217,243,.5);
    display: flex; align-items: center; gap: .3rem;
    margin: 0;
}
.dsb-membership-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .22rem .75rem; border-radius: 20px;
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    border: 1px solid;
}
.dsb-profile-type {
    font-size: .8rem; color: rgba(226,217,243,.55);
    margin: 0;
}

/* ── Stats grid ── */
.dsb-stats-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: .5rem; width: 100%; margin: .5rem 0;
}
.dsb-stat-box {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(180,60,120,.12);
    border-radius: 10px;
    padding: .55rem .3rem;
    display: flex; flex-direction: column;
    align-items: center; gap: .2rem;
}
.dsb-stat-box i { font-size: .85rem; }
.dsb-stat-num {
    font-size: .9rem; font-weight: 800;
    color: #fff; line-height: 1;
}
.dsb-stat-lbl {
    font-size: .62rem; color: rgba(226,217,243,.45);
    text-transform: uppercase; letter-spacing: .04em;
}

/* ── Progress ── */
.dsb-progress-wrap { width: 100%; margin: .25rem 0; }
.dsb-progress-label {
    display: flex; justify-content: space-between;
    font-size: .74rem; color: rgba(226,217,243,.6);
    margin-bottom: .35rem;
}
.dsb-progress-bar {
    height: 6px; background: rgba(255,255,255,.08);
    border-radius: 4px; overflow: hidden;
}
.dsb-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #c0392b, #8e44ad);
    border-radius: 4px;
    transition: width .6s ease;
}

/* ── Acciones rápidas ── */
.dsb-quick-actions {
    display: flex; gap: .5rem; width: 100%; margin-top: .25rem;
}
.dsb-action-btn {
    flex: 1; display: inline-flex; align-items: center;
    justify-content: center; gap: .4rem;
    padding: .55rem .75rem; border-radius: 10px;
    font-size: .82rem; font-weight: 600;
    text-decoration: none; transition: all .18s;
    border: none; cursor: pointer;
}
.dsb-action-btn--primary {
    background: linear-gradient(135deg, #c0392b, #8e44ad);
    color: #fff;
}
.dsb-action-btn--primary:hover { opacity: .88; transform: translateY(-1px); }
.dsb-action-btn--ghost {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(180,60,120,.2);
    color: rgba(226,217,243,.85);
}
.dsb-action-btn--ghost:hover {
    background: rgba(180,60,120,.15);
    color: #fff;
}

/* ── Sección card (visitantes, etc.) ── */
.dsb-section-card {
    background: rgba(20,12,35,.9);
    border: 1px solid rgba(180,60,120,.18);
    border-radius: 16px;
    padding: 1rem;
    margin-bottom: .85rem;
    overflow: hidden;
}
.dsb-section-header {
    display: flex; align-items: center;
    justify-content: space-between;
    font-size: .75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    color: rgba(224,86,160,.9);
    margin-bottom: .75rem;
    padding-bottom: .6rem;
    border-bottom: 1px solid rgba(180,60,120,.12);
}
.dsb-section-badge {
    background: rgba(180,60,120,.2);
    color: #e056a0;
    border-radius: 10px;
    padding: .1rem .5rem;
    font-size: .72rem; font-weight: 700;
}

/* ── Fila de usuario ── */
.dsb-user-row {
    display: flex; align-items: center; gap: .65rem;
    padding: .5rem .25rem;
    border-bottom: 1px solid rgba(255,255,255,.04);
    text-decoration: none;
    transition: background .15s;
    border-radius: 8px;
    margin: 0 -.25rem;
    padding-left: .5rem; padding-right: .5rem;
}
.dsb-user-row:last-of-type { border-bottom: none; }
.dsb-user-row:hover { background: rgba(180,60,120,.08); }
.dsb-user-avatar-wrap { position: relative; flex-shrink: 0; }
.dsb-user-avatar-wrap img {
    width: 36px; height: 36px;
    border-radius: 50%; object-fit: cover;
    border: 2px solid rgba(180,60,120,.3);
}
.dsb-online-indicator {
    position: absolute; bottom: 1px; right: 1px;
    width: 9px; height: 9px;
    background: #22c55e; border-radius: 50%;
    border: 2px solid #14091f;
}
.dsb-user-info {
    flex: 1; min-width: 0;
    display: flex; flex-direction: column;
}
.dsb-user-nick {
    font-size: .84rem; font-weight: 600;
    color: #e2d9f3; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.dsb-user-time {
    font-size: .72rem; color: rgba(226,217,243,.4);
    display: flex; align-items: center; gap: .25rem;
}
.dsb-online-dot {
    display: inline-block;
    width: 8px; height: 8px;
    background: #22c55e; border-radius: 50%;
}
.dsb-ver-btn {
    display: inline-flex; align-items: center;
    padding: .25rem .65rem;
    background: rgba(180,60,120,.15);
    border: 1px solid rgba(180,60,120,.3);
    border-radius: 20px;
    color: #e056a0; font-size: .74rem; font-weight: 600;
    text-decoration: none; white-space: nowrap;
    transition: all .15s; flex-shrink: 0;
}
.dsb-ver-btn:hover {
    background: rgba(180,60,120,.3);
    color: #fff;
}
.dsb-empty-state {
    display: flex; flex-direction: column;
    align-items: center; gap: .4rem;
    padding: 1rem;
    color: rgba(226,217,243,.3);
    font-size: .8rem; text-align: center;
}
.dsb-empty-state i { font-size: 1.4rem; opacity: .4; }
.dsb-see-more {
    display: block; text-align: right;
    font-size: .78rem; color: #e056a0;
    text-decoration: none; margin-top: .6rem;
    font-weight: 600;
}

/* ── Feed de fotos ── */
.dsb-feed-tabs {
    display: flex; gap: .5rem;
    margin-bottom: 1.25rem;
}
.dsb-feed-tab {
    display: flex; align-items: center; gap: .4rem;
    padding: .5rem 1.1rem; border-radius: 10px;
    color: rgba(226,217,243,.6);
    text-decoration: none; font-size: .88rem; font-weight: 600;
    border: 1px solid rgba(180,60,120,.15);
    transition: all .18s;
}
.dsb-feed-tab:hover { color: #fff; background: rgba(180,60,120,.1); }
.dsb-feed-tab.is-active {
    color: #e056a0; background: rgba(180,60,120,.15);
    border-color: rgba(180,60,120,.4);
}

.dsb-feed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1rem;
}
.dsb-feed-empty {
    grid-column: 1/-1;
    display: flex; flex-direction: column;
    align-items: center; gap: 1rem;
    padding: 3rem;
    color: rgba(226,217,243,.4);
    font-size: .9rem;
}
.dsb-feed-empty i { font-size: 3rem; opacity: .3; }

/* ── Tarjeta de foto ── */
.dsb-photo-card {
    background: rgba(20,12,35,.9);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s, border-color .2s;
}
.dsb-photo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(0,0,0,.5);
    border-color: rgba(180,60,120,.4);
}
.dsb-photo-card__header {
    padding: .65rem .8rem;
    border-bottom: 1px solid rgba(255,255,255,.05);
}
.dsb-photo-card__owner {
    display: flex; align-items: center; gap: .5rem;
    text-decoration: none;
}
.dsb-photo-card__owner img {
    width: 28px; height: 28px;
    border-radius: 50%; object-fit: cover;
    border: 1px solid rgba(180,60,120,.35);
    flex-shrink: 0;
}
.dsb-photo-card__owner-nick {
    font-size: .82rem; font-weight: 700; color: #e2d9f3;
    display: block; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.dsb-photo-card__owner-loc {
    font-size: .7rem; color: rgba(226,217,243,.4);
    display: flex; align-items: center; gap: .2rem;
}
.dsb-photo-card__img-wrap {
    aspect-ratio: 4/3;
    overflow: hidden;
    background: #0f0a1a;
}
.dsb-photo-card__img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .35s;
}
.dsb-photo-card:hover .dsb-photo-card__img { transform: scale(1.05); }
.dsb-photo-card__footer {
    padding: .65rem .8rem;
}
.dsb-photo-card__caption {
    font-size: .78rem; color: rgba(226,217,243,.55);
    margin: 0 0 .5rem; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.dsb-photo-card__actions {
    display: flex; align-items: center; gap: .5rem;
}
.dsb-like-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .7rem;
    background: rgba(239,68,68,.1);
    border: 1px solid rgba(239,68,68,.25);
    border-radius: 20px;
    color: rgba(226,217,243,.8); font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .15s;
}
.dsb-like-btn:hover { background: rgba(239,68,68,.2); color: #fca5a5; }
.dsb-like-btn.is-liked {
    background: rgba(239,68,68,.25);
    border-color: rgba(239,68,68,.5);
    color: #fca5a5;
}
.dsb-like-btn.is-liked i { color: #ef4444; }
.dsb-comment-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .7rem;
    background: rgba(96,165,250,.08);
    border: 1px solid rgba(96,165,250,.2);
    border-radius: 20px;
    color: rgba(226,217,243,.7); font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .15s;
}
.dsb-comment-btn:hover { background: rgba(96,165,250,.15); color: #93c5fd; }
.dsb-profile-btn {
    margin-left: auto;
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px;
    background: rgba(180,60,120,.1);
    border: 1px solid rgba(180,60,120,.2);
    border-radius: 50%;
    color: rgba(226,217,243,.6); font-size: .78rem;
    text-decoration: none; transition: all .15s;
}
.dsb-profile-btn:hover { background: rgba(180,60,120,.25); color: #e056a0; }

/* ── Botón cargar más ── */
.dsb-load-more-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .65rem 2rem;
    background: rgba(180,60,120,.1);
    border: 1px solid rgba(180,60,120,.3);
    border-radius: 12px;
    color: #e2d9f3; font-size: .9rem; font-weight: 600;
    cursor: pointer; transition: all .18s;
}
.dsb-load-more-btn:hover { background: rgba(180,60,120,.2); color: #fff; }

/* ── Modal ── */
.dsb-modal {
    position: fixed; inset: 0; z-index: 99990;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}
.dsb-modal__overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.88);
    backdrop-filter: blur(8px);
}
.dsb-modal__box {
    position: relative; z-index: 1;
    background: #12091e;
    border: 1px solid rgba(180,60,120,.3);
    border-radius: 20px;
    max-width: 900px; width: 100%;
    max-height: 90vh; overflow: hidden;
    box-shadow: 0 32px 80px rgba(0,0,0,.7);
}
.dsb-modal__close {
    position: absolute; top: .75rem; right: .75rem;
    width: 32px; height: 32px;
    background: rgba(255,255,255,.08);
    border: none; border-radius: 50%;
    color: #fff; cursor: pointer; z-index: 10;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; transition: background .15s;
}
.dsb-modal__close:hover { background: rgba(239,68,68,.35); }
.dsb-modal__layout {
    display: grid; grid-template-columns: 1fr 300px;
    max-height: 90vh;
}
.dsb-modal__photo-side {
    position: relative; background: #000;
    display: flex; align-items: center; justify-content: center;
    min-height: 380px;
}
.dsb-modal__photo {
    max-width: 100%; max-height: 78vh;
    object-fit: contain;
}
.dsb-modal__photo-actions {
    position: absolute; bottom: .85rem; left: .85rem;
    display: flex; gap: .5rem; align-items: center;
}
.dsb-comment-count {
    display: flex; align-items: center; gap: .3rem;
    color: rgba(255,255,255,.8); font-size: .82rem;
    background: rgba(0,0,0,.4); padding: .3rem .65rem;
    border-radius: 20px;
}
.dsb-modal__panel {
    display: flex; flex-direction: column;
    border-left: 1px solid rgba(180,60,120,.15);
    max-height: 90vh; overflow: hidden;
}
.dsb-modal__owner {
    display: flex; align-items: center; justify-content: space-between;
    padding: .9rem 1rem;
    border-bottom: 1px solid rgba(180,60,120,.12);
    gap: .5rem;
}
.dsb-modal__owner-link {
    display: flex; align-items: center; gap: .6rem;
    text-decoration: none;
}
.dsb-modal__owner-avatar {
    width: 38px; height: 38px;
    border-radius: 50%; object-fit: cover;
    border: 2px solid rgba(180,60,120,.4);
    flex-shrink: 0;
}
.dsb-modal__owner-nick {
    font-weight: 700; font-size: .9rem; color: #fff;
}
.dsb-modal__owner-meta {
    font-size: .75rem; color: rgba(226,217,243,.5);
}
.dsb-modal__caption {
    padding: .6rem 1rem;
    font-size: .83rem; color: rgba(226,217,243,.65);
    border-bottom: 1px solid rgba(180,60,120,.1);
    margin: 0;
}
.dsb-modal__comments {
    flex: 1; overflow-y: auto;
    padding: .75rem 1rem;
    scrollbar-width: thin;
    scrollbar-color: rgba(180,60,120,.3) transparent;
}
.dsb-comment-item {
    display: flex; gap: .5rem; margin-bottom: .75rem;
}
.dsb-comment-item img {
    width: 26px; height: 26px;
    border-radius: 50%; object-fit: cover; flex-shrink: 0;
}
.dsb-comment-nick {
    font-size: .78rem; font-weight: 700; color: #e2d9f3;
}
.dsb-comment-time {
    font-size: .7rem; color: rgba(226,217,243,.4); margin-left: .35rem;
}
.dsb-comment-body {
    font-size: .81rem; color: rgba(226,217,243,.75);
    margin: .15rem 0 0; line-height: 1.45;
}
.dsb-modal__comment-form {
    padding: .75rem 1rem;
    border-top: 1px solid rgba(180,60,120,.12);
}
.dsb-modal__comment-row {
    display: flex; align-items: center; gap: .5rem;
}
.dsb-modal__comment-avatar {
    width: 26px; height: 26px;
    border-radius: 50%; object-fit: cover; flex-shrink: 0;
}
.dsb-modal__comment-input {
    flex: 1; padding: .42rem .75rem;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(180,60,120,.2);
    border-radius: 20px; color: #fff;
    font-size: .83rem; outline: none;
    transition: border-color .2s;
}
.dsb-modal__comment-input:focus { border-color: rgba(180,60,120,.5); }
.dsb-modal__comment-input::placeholder { color: rgba(226,217,243,.35); }
.dsb-modal__comment-send {
    width: 30px; height: 30px;
    background: linear-gradient(135deg, #c0392b, #8e44ad);
    border: none; border-radius: 50%; color: #fff;
    cursor: pointer; font-size: .8rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: opacity .15s;
}
.dsb-modal__comment-send:hover { opacity: .85; }
.dsb-modal__comment-note {
    font-size: .71rem; color: rgba(226,217,243,.35);
    margin: .4rem 0 0; display: flex; gap: .3rem; align-items: center;
}
@media (max-width: 640px) {
    .dsb-modal__layout { grid-template-columns: 1fr; }
    .dsb-modal__panel { max-height: 45vh; border-left: none; border-top: 1px solid rgba(180,60,120,.15); }
    .dsb-stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
(function(){
var CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Abrir modal al click en tarjeta (no en botones) ──
document.addEventListener('click', function(e) {
    var card = e.target.closest('.dsb-photo-card');
    if (!card) return;
    if (e.target.closest('.dsb-like-btn') ||
        e.target.closest('.dsb-comment-btn') ||
        e.target.closest('.dsb-profile-btn') ||
        e.target.closest('.dsb-photo-card__owner')) return;
    openModal(card.dataset.photoId);
});

// ── Like en feed ──
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.dsb-like-btn');
    if (!btn || !btn.dataset.photoId) return;
    e.preventDefault(); e.stopPropagation();
    toggleLike(btn.dataset.photoId, btn);
});

// ── Comentario en feed (abre modal) ──
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.dsb-comment-btn');
    if (!btn) return;
    e.preventDefault();
    openModal(btn.dataset.photoId, true);
});

function toggleLike(photoId, btn) {
    fetch('/fotos/' + photoId + '/like', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
        var icon  = btn.querySelector('i');
        var count = btn.querySelector('span');
        icon.className = d.liked ? 'fas fa-heart' : 'far fa-heart';
        d.liked ? btn.classList.add('is-liked') : btn.classList.remove('is-liked');
        if (count) count.textContent = d.count;
        // Sync modal
        var mBtn = document.getElementById('modalLikeBtn');
        if (mBtn && mBtn.dataset.photoId === photoId) {
            mBtn.querySelector('i').className = d.liked ? 'fas fa-heart' : 'far fa-heart';
            d.liked ? mBtn.classList.add('is-liked') : mBtn.classList.remove('is-liked');
            document.getElementById('modalLikeCount').textContent = d.count;
        }
    });
}

function openModal(photoId, focusComment) {
    var modal = document.getElementById('photoModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.getElementById('modalPhoto').src = '';
    document.getElementById('commentsList').innerHTML =
        '<div class="dsb-empty-state"><i class="fas fa-spinner fa-spin"></i><span>Cargando...</span></div>';

    fetch('/fotos/' + photoId + '/info', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => {
        document.getElementById('modalPhoto').src = d.photo.url;
        document.getElementById('modalLikeCount').textContent = d.photo.likes_count;
        document.getElementById('modalCommentCount').textContent = d.comments.length;
        var lb = document.getElementById('modalLikeBtn');
        lb.dataset.photoId = d.photo.id;
        lb.querySelector('i').className = d.photo.liked ? 'fas fa-heart' : 'far fa-heart';
        d.photo.liked ? lb.classList.add('is-liked') : lb.classList.remove('is-liked');
        var cap = document.getElementById('modalCaption');
        if (d.photo.caption) { cap.textContent = d.photo.caption; cap.style.display = 'block'; }
        else { cap.style.display = 'none'; }
        document.getElementById('modalOwnerAvatar').src = d.owner.avatar;
        document.getElementById('modalOwnerNick').textContent = d.owner.nick;
        document.getElementById('modalOwnerMeta').textContent =
            (d.owner.profile_type === 'pareja' ? '👫 Pareja' :
             d.owner.profile_type === 'unicornio' ? '⭐ Unicornio' : '👤 Single') +
            (d.owner.city ? ' · ' + d.owner.city : '');
        document.getElementById('modalOwnerLink').href = d.owner.profile_url;
        document.getElementById('modalProfileLink').href = d.owner.profile_url;
        document.getElementById('commentPhotoId').value = d.photo.id;
        var list = document.getElementById('commentsList');
        list.innerHTML = d.comments.length === 0
            ? '<div class="dsb-empty-state"><i class="far fa-comment"></i><span>Sé el primero en comentar</span></div>'
            : d.comments.map(c =>
                '<div class="dsb-comment-item">' +
                '<img src="' + c.user_avatar + '" onerror="this.src=\'/img/default-avatar.svg\'">' +
                '<div><span class="dsb-comment-nick">' + c.user_nick + '</span>' +
                '<span class="dsb-comment-time">' + c.created_at + '</span>' +
                '<p class="dsb-comment-body">' + c.body + '</p></div></div>'
              ).join('');
        if (focusComment) {
            setTimeout(() => document.getElementById('commentBody')?.focus(), 100);
        }
    });
}

// Cerrar modal
document.getElementById('photoModalClose').addEventListener('click', closeModal);
document.getElementById('photoModalOverlay').addEventListener('click', closeModal);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
function closeModal() {
    document.getElementById('photoModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Like en modal
document.getElementById('modalLikeBtn').addEventListener('click', function() {
    toggleLike(this.dataset.photoId, this);
});

// Comentario
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var photoId = document.getElementById('commentPhotoId').value;
    var body    = document.getElementById('commentBody').value.trim();
    if (!body) return;
    fetch('/fotos/' + photoId + '/comentario', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ body })
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('commentBody').value = '';
        var note = document.getElementById('commentNote');
        note.style.color = '#34d399';
        note.innerHTML = '<i class="fas fa-check-circle"></i> ' + d.message;
        setTimeout(() => {
            note.style.color = '';
            note.innerHTML = '<i class="fas fa-info-circle"></i> Los comentarios se publican tras revisión del admin';
        }, 3500);
    });
});

// Cargar más
var loadBtn = document.getElementById('loadMoreBtn');
if (loadBtn) {
    loadBtn.addEventListener('click', function() {
        var page = this.dataset.page;
        var tab  = this.dataset.tab;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
        this.disabled = true;
        fetch('/dashboard/feed?tab=' + tab + '&page=' + page, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            document.getElementById('feedGrid').insertAdjacentHTML('beforeend', d.html);
            if (d.hasMore) {
                this.dataset.page = d.nextPage;
                this.innerHTML = '<i class="fas fa-chevron-down"></i> Cargar más fotos';
                this.disabled = false;
            } else {
                document.getElementById('loadMoreWrap').style.display = 'none';
            }
        });
    });
}
})();
</script>
@endpush
BLADE;

file_put_contents($base . '/resources/views/dashboard/index.blade.php', $dashboard);
echo "✓ dashboard/index.blade.php rediseñado\n";

echo "\n════════════════════════════════════════\n";
echo "  Ejecuta:\n";
echo "  C:\\php\\php.exe artisan view:clear\n";
echo "  C:\\php\\php.exe artisan serve\n";
echo "════════════════════════════════════════\n";
