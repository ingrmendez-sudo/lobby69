{{-- resources/views/profile/show.blade.php --}}
@extends('layouts.app')
@section('title', ($profile->nickname ?? 'Perfil') . ' — LOBBY69')

{{-- ══ SIDEBAR IZQUIERDO: Stats del perfil ══ --}}
@push('sidebar-left')
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

<div class="l69-sidebar-card">
    <div style="text-align:center;padding:.5rem 0 1rem;">
        <img src="{{ $avatarUrl }}"
             style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid rgba(180,60,120,.4);margin-bottom:.5rem;"
             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        <div style="font-weight:700;font-size:.95rem;color:var(--theme-text);">{{ $profile->nickname }}</div>
        <div style="font-size:.76rem;color:var(--theme-muted);margin-top:.15rem;">
            {{ ucfirst($profile->profile_type ?? 'single') }}
            @if(($user->verification_status ?? '') === 'approved')
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
            <div class="l69-stat__value">{{ $sbLikesCount }}</div>
            <div class="l69-stat__label">Likes</div>
        </div>
    </div>
    @if($sbReviews->count() > 0)
    <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid rgba(180,60,120,.12);">
        <div style="font-size:.75rem;font-weight:700;color:var(--theme-muted);margin-bottom:.4rem;text-transform:uppercase;letter-spacing:.04em;">Recomendaciones</div>
        <div style="display:flex;gap:.5rem;">
            <div style="flex:1;text-align:center;background:rgba(39,174,96,.1);border-radius:8px;padding:.4rem;">
                <div style="font-size:1rem;font-weight:800;color:#27ae60;">{{ $sbPos }}</div>
                <div style="font-size:.7rem;color:var(--theme-muted);">👍 Positivas</div>
            </div>
            <div style="flex:1;text-align:center;background:rgba(231,76,60,.1);border-radius:8px;padding:.4rem;">
                <div style="font-size:1rem;font-weight:800;color:#e74c3c;">{{ $sbNeg }}</div>
                <div style="font-size:.7rem;color:var(--theme-muted);">👎 Negativas</div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Acciones rápidas --}}
@auth
@if(!$isOwnProfile)
<div class="l69-sidebar-card" style="margin-top:.6rem;">
    <div class="l69-sidebar-card__title"><i class="fas fa-bolt"></i> Acciones</div>
    <div style="display:flex;flex-direction:column;gap:.45rem;">
        {{-- Seguir / Dejar de seguir --}}
        @if($isFollowing)
            <form method="POST" action="{{ route('unfollow', $profile->user_id) }}" style="margin:0;">
                @csrf @method('DELETE')
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
        {{-- Mensaje directo --}}
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
    <div class="l69-sidebar-card__title"><i class="fas fa-cog"></i> Mi Perfil</div>
    <a href="{{ route('profile.edit') }}" class="prf-sb-btn prf-sb-btn--outline" style="width:100%;text-align:center;display:block;">
        ✏️ Editar perfil
    </a>
</div>
@endif
@endauth
@endpush

{{-- ══ SIDEBAR DERECHO ══ --}}
@push('sidebar-right')
@include('layouts.sidebar-right')

{{-- Amigos en común --}}
@auth
@if(!$isOwnProfile)
@php
    $meId = (string)auth()->id();
    $commonFriends = DB::table('friendships as f1')
        ->join('friendships as f2',
            DB::raw("CASE WHEN f2.sender_id::text = '{$meId}' THEN f2.receiver_id::text ELSE f2.sender_id::text END"),
            '=',
            DB::raw("CASE WHEN f1.sender_id::text = '{$uid}' THEN f1.receiver_id::text ELSE f1.sender_id::text END")
        )
        ->join('users as u', DB::raw('u.id::text'), '=',
            DB::raw("CASE WHEN f1.sender_id::text = '{$uid}' THEN f1.receiver_id::text ELSE f1.sender_id::text END")
        )
        ->leftJoin('profiles as pr', DB::raw('pr.user_id::text'), '=', DB::raw('u.id::text'))
        ->whereRaw('(f1.sender_id::text = ? OR f1.receiver_id::text = ?)', [$uid, $uid])
        ->whereRaw('(f2.sender_id::text = ? OR f2.receiver_id::text = ?)', [$meId, $meId])
        ->whereRaw("CASE WHEN f1.sender_id::text = '{$uid}' THEN f1.receiver_id::text ELSE f1.sender_id::text END != ?", [$meId])
        ->whereRaw("CASE WHEN f1.sender_id::text = '{$uid}' THEN f1.receiver_id::text ELSE f1.sender_id::text END != ?", [$uid])
        ->where('f1.status', 'accepted')
        ->where('f2.status', 'accepted')
        ->select([
            'u.id AS user_id',
            DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
            'pr.nickname',
            DB::raw("(SELECT ap.id FROM photos ap WHERE ap.user_id::text = u.id::text AND ap.is_profile_photo = true AND ap.status = 'approved' LIMIT 1) AS avatar_id"),
        ])
        ->limit(6)
        ->get();
@endphp
@if($commonFriends->count() > 0)
<div class="l69-sidebar-card" style="margin-top:.6rem;">
    <div class="l69-sidebar-card__title"><i class="fas fa-users"></i> Amigos en común ({{ $commonFriends->count() }})</div>
    <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
        @foreach($commonFriends as $cf)
        <a href="{{ $cf->nickname ? route('profile.show', $cf->nickname) : '#' }}"
           title="{{ $cf->nickname ?? $cf->display_name }}"
           style="display:block;">
            @if($cf->avatar_id)
                <img src="{{ route('photos.serve', $cf->avatar_id) }}"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid rgba(180,60,120,.3);"
                     onerror="this.style.display='none'">
            @else
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(180,60,120,.3);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#e056a0;">{{ mb_substr($cf->display_name ?? '?',0,1) }}</div>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif
@endif
@endauth
@endpush

@section('content')

@push('styles')
<style>
/* ══════════════════════════════════════════════
   VARIABLES SEMÁNTICAS — respetan modo día/noche
   ══════════════════════════════════════════════ */
.prf-wrap {
  --_bg:        var(--bg-card,     #ffffff);
  --_bg-input:  var(--bg-input,    #f0eee8);
  --_text:      var(--text-primary,   #1a1523);
  --_text-sub:  var(--text-secondary, #5a5470);
  --_muted:     var(--text-muted,     #9590a8);
  --_border:    var(--border-color,   rgba(26,21,35,.10));
  --_pink:      #e056a0;
  --_purple:    #8b5cf6;
  --_accent:    #c0392b;
  --_radius:    12px;
}

/* ══ HEADER ══ */
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
.prf-badge--member   { background: rgba(120,60,180,.15); color: var(--_purple); display:inline-flex;align-items:center;gap:.25rem; }
.prf-location { font-size: .85rem; color: var(--_muted); margin: 0 0 .5rem; }
.prf-bio { font-size: .9rem; color: var(--_text-sub); line-height: 1.65; margin: 0 0 .6rem; }

/* ── Follow row ── */
.prf-follow-row {
  display: flex; align-items: center; gap: .75rem;
  flex-wrap: wrap; margin-top: .5rem;
}
.prf-follow-stats { font-size: .85rem; color: var(--_muted); display:flex;gap:.5rem;align-items:center; }
.prf-follow-stats strong { color: var(--_text); font-weight: 700; }
.prf-follow-sep { color: var(--_muted); }

/* ══ CUERPO ══ */
.prf-body-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.1rem;
  margin-bottom: 1.1rem;
}

/* ══ CARD GENÉRICA ══ */
.prf-card {
  background: var(--_bg);
  border: 1px solid var(--_border);
  border-radius: var(--_radius);
  padding: 1.1rem 1.2rem;
  margin-bottom: 1.1rem;
}
.prf-card--full { margin-bottom: 1.1rem; }
.prf-card__title {
  font-size: .88rem; font-weight: 700;
  color: var(--_text); margin: 0 0 .85rem;
  padding-bottom: .55rem;
  border-bottom: 1px solid var(--_border);
  display: flex; align-items: center; gap: .4rem;
}

/* ══ TABLA DE DATOS ══ */
.prf-table { width: 100%; font-size: .82rem; border-collapse: collapse; }
.prf-table td { padding: .28rem 0; border-bottom: 1px solid var(--_border); vertical-align: top; }
.prf-table td:first-child { color: var(--_muted); width: 45%; padding-right: .5rem; }
.prf-table td:last-child  { color: var(--_text);  font-weight: 500; }
.prf-data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.prf-data-col-title { font-size: .82rem; font-weight: 700; margin: 0 0 .5rem; }
.prf-data-col-title--main    { color: var(--_purple); }
.prf-data-col-title--partner { color: var(--_pink);   }

/* ══ TAGS ══ */
.prf-tags { display: flex; flex-wrap: wrap; gap: .35rem; }
.prf-tag  { font-size: .76rem; font-weight: 600; padding: .22rem .55rem; border-radius: 20px; white-space: nowrap; }
.prf-tag--active   { background: rgba(180,60,120,.15); color: var(--_pink); border: 1px solid rgba(180,60,120,.25); }
.prf-tag--inactive { background: rgba(128,128,128,.07); color: var(--_muted); border: 1px solid rgba(128,128,128,.1); text-decoration: line-through; opacity: .5; }

/* ══ CARRUSEL ══ */
.prf-carousel-wrap { position: relative; overflow: hidden; border-radius: 10px; }
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
.prf-carousel-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.prf-carousel-item-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,.6) 0%, transparent 55%);
  opacity: 0; transition: opacity .2s;
  display: flex; align-items: flex-end; padding: .5rem;
}
.prf-carousel-item:hover .prf-carousel-item-overlay { opacity: 1; }
.prf-carousel-item-meta { display: flex; gap: .6rem; align-items: center; }
.prf-carousel-item-meta span { font-size: .75rem; color: #fff; display: flex; align-items: center; gap: .2rem; }

/* Botones prev/next */
.prf-carousel-btn {
  position: absolute; top: 50%; transform: translateY(-50%);
  z-index: 10; background: rgba(0,0,0,.55); color: #fff;
  border: none; border-radius: 50%; width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: .9rem; transition: background .15s;
}
.prf-carousel-btn:hover { background: rgba(0,0,0,.8); }
.prf-carousel-btn--prev { left: .5rem; }
.prf-carousel-btn--next { right: .5rem; }

/* ══ MODAL FOTO ══ */
.prf-photo-modal {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.82);
  z-index: 9999; display: flex;
  align-items: center; justify-content: center;
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
.prf-photo-modal__nav:hover { background: rgba(0,0,0,.85); }
.prf-photo-modal__nav--prev { left: .6rem; }
.prf-photo-modal__nav--next { right: .6rem; }
.prf-photo-modal__body { padding: 1rem 1.25rem; }
.prf-photo-modal__meta {
  display: flex; justify-content: space-between; align-items: center;
  flex-wrap: wrap; gap: .5rem; margin-bottom: .75rem;
}
.prf-photo-modal__caption { font-size: .9rem; font-weight: 600; color: var(--_text); }
.prf-photo-modal__actions { display: flex; align-items: center; gap: .6rem; }
.prf-like-btn {
  display: flex; align-items: center; gap: .35rem;
  background: transparent; border: 1px solid var(--_border);
  color: var(--_text-sub); border-radius: 8px;
  padding: .3rem .7rem; font-size: .83rem; font-weight: 600;
  cursor: pointer; transition: all .15s;
}
.prf-like-btn:hover, .prf-like-btn.liked { background: rgba(231,76,60,.12); color: #e74c3c; border-color: rgba(231,76,60,.3); }
.prf-like-btn.liked .prf-like-icon::before { content: '❤️'; }
.prf-like-icon::before { content: '🤍'; }
.prf-photo-modal__close {
  background: transparent; border: none; cursor: pointer;
  color: var(--_muted); font-size: 1.1rem; padding: .25rem .5rem;
  border-radius: 6px; transition: background .15s, color .15s;
}
.prf-photo-modal__close:hover { background: rgba(0,0,0,.08); color: var(--_text); }

/* Comentarios en modal */
.prf-modal-comments { max-height: 220px; overflow-y: auto; margin-bottom: .6rem; }
.prf-modal-comment {
  display: flex; gap: .55rem; padding: .4rem 0;
  border-bottom: 1px solid var(--_border);
  font-size: .82rem;
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
.prf-modal-comment__body { flex: 1; min-width: 0; }
.prf-modal-comment__author { font-weight: 600; color: var(--_text); }
.prf-modal-comment__text   { color: var(--_text-sub); line-height: 1.45; }
.prf-modal-comment__time   { font-size: .7rem; color: var(--_muted); white-space: nowrap; }
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
  background: var(--_accent); color: #fff;
  border: none; border-radius: 8px;
  padding: .4rem .85rem; font-size: .82rem; font-weight: 700;
  cursor: pointer; transition: opacity .15s; white-space: nowrap;
}
.prf-comment-form button:hover { opacity: .85; }
.prf-comment-empty { font-size: .8rem; color: var(--_muted); padding: .4rem 0; }

/* ══ MODAL CONVERSACIÓN (reutiliza clases de mensajes) ══ */
.l69-modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.55);
  display: flex; align-items: center; justify-content: center; z-index: 9999;
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
  border-radius: 10px; font-size: .85rem; line-height: 1.45; word-break: break-word;
}
.l69-msg-bubble.mine   { background: var(--_accent); color: #fff; align-self: flex-end; border-bottom-right-radius: 2px; }
.l69-msg-bubble.theirs { background: var(--_bg-input); color: var(--_text); align-self: flex-start; border-bottom-left-radius: 2px; }
.l69-msg-time { font-size: .69rem; opacity: .65; display: block; margin-top: .12rem; }
.l69-modal-send {
  display: flex; gap: .5rem; padding: .75rem 1rem; border-top: 1px solid var(--_border);
}
.l69-modal-send textarea {
  flex: 1; resize: none; background: var(--_bg-input);
  border: 1px solid var(--_border); color: var(--_text);
  border-radius: 8px; padding: .45rem .65rem; font-size: .85rem; font-family: inherit;
}
.l69-modal-send textarea:focus { outline: none; border-color: rgba(192,57,43,.4); }
.l69-modal-send button {
  background: var(--_accent); color: #fff; border: none; border-radius: 8px;
  padding: .4rem .85rem; font-size: .85rem; font-weight: 700; cursor: pointer; transition: opacity .15s;
}
.l69-modal-send button:hover { opacity: .85; }

/* ══ SIDEBAR BUTTONS ══ */
.prf-sb-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
  border-radius: 8px; padding: .45rem 1rem; font-size: .83rem; font-weight: 700;
  cursor: pointer; transition: all .15s; border: none; text-decoration: none;
}
.prf-sb-btn--primary { background: linear-gradient(135deg,var(--_purple),var(--_pink)); color: #fff; }
.prf-sb-btn--primary:hover { opacity: .85; }
.prf-sb-btn--outline { background: transparent; border: 1.5px solid var(--_border); color: var(--_text-sub); }
.prf-sb-btn--outline:hover { border-color: var(--_accent); color: var(--_accent); }
.prf-sb-btn--msg { background: var(--_accent); color: #fff; }
.prf-sb-btn--msg:hover { opacity: .85; }

/* ══ RESPONSIVE ══ */
@media (max-width: 700px) {
  .prf-body-grid { grid-template-columns: 1fr; }
  .prf-header    { flex-direction: column; align-items: center; text-align: center; }
  .prf-data-grid { grid-template-columns: 1fr; }
  .prf-carousel-item { width: 150px; height: 150px; }
}
</style>
@endpush

@php
    $isPairing = $profile->profile_type === 'pareja';
    $isUnicorn = $profile->profile_type === 'unicornio';
    $showName  = $profile->show_name ?? true;
    $showPName = $profile->show_partner_name ?? true;
    $mainName  = $showName  ? ($profile->display_name ?? '') : 'Nombre oculto';
    $partName  = $showPName ? ($profile->partner_name  ?? '') : 'Nombre oculto';

    $lookingFor = json_decode($profile->looking_for ?? '[]', true) ?? [];
    $interests  = json_decode($profile->interests   ?? '[]', true) ?? [];

    $allLookingFor = [
        'Parejas heterosexuales','Parejas bisexuales','Parejas (ella bisexual)',
        'Parejas (él bisexual)','Hombres heterosexuales','Hombres bisexuales',
        'Mujeres heterosexuales','Mujeres bisexuales',
    ];
    $allInterests = [
        'Intercambio completo','Intercambio light','Sexo en grupo','Tríos',
        'Sólo ellas','Mirar y ser vistos','Cuckold','Prácticas BDSM',
        'Compartir fetiches','Cybersexo','Intercambio de fotos',
        'Sexo por separado','Relaciones abiertas','Amistad','Otros',
    ];
@endphp

<div class="prf-wrap">

{{-- ══ HEADER ══ --}}
<div class="prf-header">
    <div class="prf-avatar-wrap">
        <img class="prf-avatar" src="{{ $avatarUrl }}" alt="{{ $profile->nickname }}"
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
                <img src="{{ $memberIcon }}" alt="{{ $memberLabel }}"
                     style="width:16px;height:16px;object-fit:contain;">
                {{ $memberLabel }}
            </span>
        </h1>

        @php
            $location = implode(', ', array_filter([$profile->city ?? null, $profile->state ?? null]));
        @endphp
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
                <span><strong>{{ $photos->count() }}</strong> fotos</span>
            </div>
            @auth
                @if(!$isOwnProfile)
                    @if($isFollowing)
                        <form method="POST" action="{{ route('unfollow', $profile->user_id) }}" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="prf-sb-btn prf-sb-btn--outline">✓ Siguiendo</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('follow', $profile->user_id) }}" style="margin:0;">
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
                    <a href="{{ route('profile.edit') }}" class="prf-sb-btn prf-sb-btn--outline">✏️ Editar perfil</a>
                @endif
            @endauth
        </div>
    </div>
</div>

{{-- ══ CUERPO — dos columnas ══ --}}
<div class="prf-body-grid">

    {{-- Columna izquierda: datos físicos --}}
    <div>
        <div class="prf-card">
            <h2 class="prf-card__title">
                👤 Sobre {{ $isPairing ? 'ellos' : ($isUnicorn ? 'ella/él' : 'mí') }}
            </h2>
            @if($isPairing)
                <div class="prf-data-grid">
                    <div>
                        <p class="prf-data-col-title prf-data-col-title--main">
                            {{ $profile->gender === 'masculino' ? '♂️' : '♀️' }} {{ $mainName }}
                        </p>
                        @include('profile._physical_data', ['p' => $profile, 'isPartner' => false])
                    </div>
                    @if($profile->partner_age || $profile->partner_name)
                    <div>
                        <p class="prf-data-col-title prf-data-col-title--partner">
                            {{ $profile->partner_gender === 'masculino' ? '♂️' : '♀️' }} {{ $partName }}
                        </p>
                        @include('profile._physical_data', ['p' => $profile, 'isPartner' => true])
                    </div>
                    @endif
                </div>
            @else
                @include('profile._physical_data', ['p' => $profile, 'isPartner' => false])
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

{{-- ══ FOTOS EN CARRUSEL ══ --}}
@if($photos->isNotEmpty())
<div class="prf-card prf-card--full">
    <h2 class="prf-card__title">
        📸 Fotos públicas
        <span style="font-weight:400;color:var(--_muted);font-size:.8rem;margin-left:.25rem;">({{ $photos->count() }})</span>
    </h2>
    <div class="prf-carousel-wrap">
        <button class="prf-carousel-btn prf-carousel-btn--prev" id="carousel-prev" aria-label="Anterior">‹</button>
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
                        <span><i class="fas fa-comment" style="font-size:.65rem;"></i> Ver</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <button class="prf-carousel-btn prf-carousel-btn--next" id="carousel-next" aria-label="Siguiente">›</button>
    </div>
</div>
@endif

</div>{{-- /.prf-wrap --}}

{{-- ══ MODAL FOTO ══ --}}
<div id="photo-modal" class="prf-photo-modal hidden">
    <div class="prf-photo-modal__box">
        {{-- Imagen --}}
        <div class="prf-photo-modal__img-wrap">
            <button class="prf-photo-modal__nav prf-photo-modal__nav--prev" id="pm-prev">‹</button>
            <img id="pm-img" class="prf-photo-modal__img" src="" alt="">
            <button class="prf-photo-modal__nav prf-photo-modal__nav--next" id="pm-next">›</button>
        </div>
        {{-- Cuerpo --}}
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
                    <button class="prf-photo-modal__close" id="pm-close">✕</button>
                </div>
            </div>
            {{-- Comentarios --}}
            <div class="prf-modal-comments" id="pm-comments">
                <p class="prf-comment-empty">Cargando comentarios…</p>
            </div>
            @auth
            <form class="prf-comment-form" id="pm-comment-form">
                <textarea placeholder="Escribe un comentario…" rows="2" maxlength="400" id="pm-comment-body"></textarea>
                <button type="submit">Comentar</button>
            </form>
            @endauth
        </div>
    </div>
</div>

{{-- ══ MODAL CONVERSACIÓN ══ --}}
<div id="conv-modal" class="l69-modal-overlay hidden">
    <div class="l69-modal-box">
        <div class="l69-modal-header">
            <span id="conv-modal-name"></span>
            <button id="conv-modal-close" class="l69-modal-close">✕</button>
        </div>
        <div id="conv-modal-messages" class="l69-modal-messages"></div>
        <form id="conv-send-form" class="l69-modal-send">
            <input type="hidden" id="conv-receiver-id">
            <textarea id="conv-body" placeholder="Escribe un mensaje…" rows="2" maxlength="1000"></textarea>
            <button type="submit">Enviar</button>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const ME   = '{{ Auth::id() }}';

/* ══ HELPERS ══ */
async function postJson(url, data) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(data),
    });
    return r.json();
}
function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtTime(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' });
}

/* ══ CARRUSEL ══ */
const track    = document.getElementById('carousel-track');
const btnPrev  = document.getElementById('carousel-prev');
const btnNext  = document.getElementById('carousel-next');
const STEP     = 216; // 200px + 16px gap
if (track) {
    btnPrev?.addEventListener('click', () => track.scrollBy({ left: -STEP * 2, behavior: 'smooth' }));
    btnNext?.addEventListener('click', () => track.scrollBy({ left:  STEP * 2, behavior: 'smooth' }));
}

/* ══ MODAL FOTO ══ */
const photoModal  = document.getElementById('photo-modal');
const pmImg       = document.getElementById('pm-img');
const pmCaption   = document.getElementById('pm-caption');
const pmLikeBtn   = document.getElementById('pm-like-btn');
const pmLikeCount = document.getElementById('pm-like-count');
const pmComments  = document.getElementById('pm-comments');
const pmForm      = document.getElementById('pm-comment-form');
const pmBody      = document.getElementById('pm-comment-body');

let photoItems = [];
let currentIdx = 0;

// Recopilar items del carrusel
function rebuildPhotoItems() {
    photoItems = Array.from(document.querySelectorAll('.prf-carousel-item'));
}
rebuildPhotoItems();

function openPhoto(idx) {
    if (!photoItems.length) return;
    currentIdx = ((idx % photoItems.length) + photoItems.length) % photoItems.length;
    const el = photoItems[currentIdx];
    const photoId   = el.dataset.photoId;
    const photoUuid = el.dataset.photoUuid;
    const caption   = el.dataset.caption;
    const likes     = parseInt(el.dataset.likes ?? '0');
    const iLiked    = el.dataset.iliked === '1';

    pmImg.src = `/fotos/${photoId}/ver`;
    pmCaption.textContent = caption || `Foto ${currentIdx + 1} de ${photoItems.length}`;

    if (pmLikeBtn) {
        pmLikeBtn.dataset.photoUuid = photoUuid;
        pmLikeCount.textContent = likes;
        pmLikeBtn.classList.toggle('liked', iLiked);
    }

    loadComments(photoUuid);
    photoModal.classList.remove('hidden');
}

document.querySelectorAll('.prf-carousel-item').forEach((el, i) => {
    el.addEventListener('click', () => openPhoto(i));
});

document.getElementById('pm-close')?.addEventListener('click', () => photoModal.classList.add('hidden'));
photoModal?.addEventListener('click', e => { if (e.target === photoModal) photoModal.classList.add('hidden'); });
document.getElementById('pm-prev')?.addEventListener('click', () => openPhoto(currentIdx - 1));
document.getElementById('pm-next')?.addEventListener('click', () => openPhoto(currentIdx + 1));

// Teclado
document.addEventListener('keydown', e => {
    if (photoModal?.classList.contains('hidden')) return;
    if (e.key === 'ArrowLeft')  openPhoto(currentIdx - 1);
    if (e.key === 'ArrowRight') openPhoto(currentIdx + 1);
    if (e.key === 'Escape')     photoModal.classList.add('hidden');
});

/* ── Like en modal ── */
pmLikeBtn?.addEventListener('click', async () => {
    const uuid = pmLikeBtn.dataset.photoUuid;
    if (!uuid) return;
    // Necesitamos el id numérico de la foto — lo buscamos en el item
    const item = photoItems.find(el => el.dataset.photoUuid === uuid);
    if (!item) return;
    const photoId = item.dataset.photoId;

    pmLikeBtn.disabled = true;
    try {
        const data = await postJson(`/fotos/${photoId}/like`, {});
        if (data.liked !== undefined) {
            const liked = data.liked;
            pmLikeBtn.classList.toggle('liked', liked);
            const newCount = parseInt(pmLikeCount.textContent) + (liked ? 1 : -1);
            pmLikeCount.textContent = Math.max(0, newCount);
            // Actualizar dataset del item del carrusel
            item.dataset.iliked = liked ? '1' : '0';
            item.dataset.likes  = Math.max(0, newCount);
            // Actualizar overlay del carrusel
            const overlayCount = item.querySelector('.prf-carousel-item-meta span:first-child');
            if (overlayCount) overlayCount.textContent = (liked ? '❤️' : '🤍') + ' ' + Math.max(0, newCount);
        }
    } finally {
        pmLikeBtn.disabled = false;
    }
});

/* ── Cargar comentarios ── */
async function loadComments(photoUuid) {
    if (!pmComments) return;
    pmComments.innerHTML = '<p class="prf-comment-empty">Cargando…</p>';
    try {
        // Reutilizamos la ruta /fotos/{id}/info con el uuid de la foto
        // Buscamos el photoId desde el item
        const item = photoItems.find(el => el.dataset.photoUuid === photoUuid);
        if (!item) { pmComments.innerHTML = '<p class="prf-comment-empty">Sin comentarios.</p>'; return; }
        const photoId = item.dataset.photoId;
        const r = await fetch(`/fotos/${photoId}/info`, { headers: { Accept: 'application/json' } });
        const data = await r.json();
        renderComments(data.comments ?? []);
    } catch(e) {
        pmComments.innerHTML = '<p class="prf-comment-empty">Error al cargar comentarios.</p>';
    }
}

function renderComments(comments) {
    if (!comments.length) {
        pmComments.innerHTML = '<p class="prf-comment-empty">Sin comentarios aún. ¡Sé el primero!</p>';
        return;
    }
    pmComments.innerHTML = comments.map(c => `
        <div class="prf-modal-comment">
            ${c.avatar_url
                ? `<img class="prf-modal-comment__avatar" src="${escHtml(c.avatar_url)}" onerror="this.style.display='none'">`
                : `<div class="prf-modal-comment__ph">${escHtml((c.commenter_name ?? '?')[0].toUpperCase())}</div>`
            }
            <div class="prf-modal-comment__body">
                <div class="prf-modal-comment__author">${escHtml(c.commenter_nick ?? c.commenter_name ?? 'Usuario')}</div>
                <div class="prf-modal-comment__text">${escHtml(c.body)}</div>
            </div>
            <span class="prf-modal-comment__time">${fmtTime(c.created_at)}</span>
        </div>
    `).join('');
    pmComments.scrollTop = pmComments.scrollHeight;
}

/* ── Enviar comentario ── */
pmForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const body = pmBody?.value.trim();
    if (!body) return;
    const item = photoItems[currentIdx];
    if (!item) return;
    const photoId = item.dataset.photoId;
    const btn = pmForm.querySelector('button');
    btn.disabled = true;
    try {
        const data = await postJson(`/fotos/${photoId}/comentar`, { body });
        if (data.ok || data.comment) {
            pmBody.value = '';
            loadComments(item.dataset.photoUuid);
        } else {
            alert(data.error ?? 'Error al comentar.');
        }
    } finally {
        btn.disabled = false;
    }
});

/* ══ MODAL CONVERSACIÓN ══ */
const convModal  = document.getElementById('conv-modal');
const convName   = document.getElementById('conv-modal-name');
const convMsgs   = document.getElementById('conv-modal-messages');
const convForm   = document.getElementById('conv-send-form');
const convBody   = document.getElementById('conv-body');
const convRecvId = document.getElementById('conv-receiver-id');

function renderMessages(msgs) {
    convMsgs.innerHTML = '';
    if (!msgs.length) {
        convMsgs.innerHTML = '<p style="text-align:center;font-size:.8rem;color:var(--text-muted,#999)">Sin mensajes. ¡Escribe el primero!</p>';
        return;
    }
    msgs.forEach(m => {
        const mine = String(m.sender_id) === ME;
        const d = document.createElement('div');
        d.className = `l69-msg-bubble ${mine ? 'mine' : 'theirs'}`;
        d.innerHTML = `${escHtml(m.body)}<span class="l69-msg-time">${fmtTime(m.created_at)}</span>`;
        convMsgs.appendChild(d);
    });
    convMsgs.scrollTop = convMsgs.scrollHeight;
}

async function openConversation(partnerId, name) {
    if (!partnerId) return;
    convName.textContent = name;
    convRecvId.value = partnerId;
    convModal.classList.remove('hidden');
    convMsgs.innerHTML = '<p style="text-align:center;font-size:.8rem;color:var(--text-muted,#999)">Cargando…</p>';
    try {
        const data = await fetch(`/mensajes/conversacion/${partnerId}`, { headers: { Accept: 'application/json' } }).then(r => r.json());
        renderMessages(data.messages ?? []);
    } catch(e) {
        convMsgs.innerHTML = '<p style="text-align:center;color:#e74c3c;font-size:.8rem">Error al cargar.</p>';
    }
}

// Botones "Mensaje"
document.getElementById('btn-msg-profile')?.addEventListener('click', function() {
    openConversation(this.dataset.partner, this.dataset.name);
});
document.getElementById('btn-msg-profile-header')?.addEventListener('click', function() {
    openConversation(this.dataset.partner, this.dataset.name);
});

document.getElementById('conv-modal-close')?.addEventListener('click', () => convModal.classList.add('hidden'));
convModal?.addEventListener('click', e => { if (e.target === convModal) convModal.classList.add('hidden'); });

convForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const body = convBody.value.trim();
    if (!body) return;
    const btn = convForm.querySelector('button');
    btn.disabled = true;
    const data = await postJson('/mensajes/enviar', { receiver_id: convRecvId.value, body });
    btn.disabled = false;
    if (data.ok) { convBody.value = ''; await openConversation(convRecvId.value, convName.textContent); }
});
</script>
@endpush
