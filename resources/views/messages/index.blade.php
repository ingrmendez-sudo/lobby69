@extends('layouts.app')
@section('title', 'Mensajes · LOBBY69')

{{-- ══ SIDEBAR IZQUIERDO ══ --}}
@push('sidebar-left')
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title"><i class="fas fa-envelope"></i> Navegación</div>
    @php
        $msgTabs = [
            'inbox'         => ['icon' => 'fa-inbox',        'label' => 'Bandeja de entrada'],
            'comments'      => ['icon' => 'fa-comments',     'label' => 'Comentarios'],
            'friends'       => ['icon' => 'fa-user-friends', 'label' => 'Amistades'],
            'reviews'       => ['icon' => 'fa-star',         'label' => 'Recomendaciones'],
            'announcements' => ['icon' => 'fa-bullhorn',     'label' => 'Anuncios'],
        ];
    @endphp
    <ul class="l69-sidebar-nav">
        @foreach($msgTabs as $key => $meta)
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('messages.index', ['tab' => $key]) }}"
               class="{{ $tab === $key ? 'is-active' : '' }}">
                <i class="fas {{ $meta['icon'] }}"></i>
                {{ $meta['label'] }}
                @if($key === 'inbox' && ($unreadTotal ?? 0) > 0)
                    <span style="margin-left:auto;background:#e74c3c;color:#fff;font-size:.65rem;font-weight:700;border-radius:99px;padding:1px 6px;">{{ $unreadTotal }}</span>
                @endif
            </a>
        </li>
        @endforeach
    </ul>
</div>

<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title"><i class="fas fa-info-circle"></i> Información</div>
    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.6rem;">
        <li style="font-size:.78rem;color:var(--theme-muted);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-lock" style="color:var(--accent-pink,#e056a0);margin-top:.15rem;flex-shrink:0;"></i>
            Los mensajes son privados y solo tú y tu contacto pueden verlos.
        </li>
        <li style="font-size:.78rem;color:var(--theme-muted);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-user-check" style="color:var(--accent-pink,#e056a0);margin-top:.15rem;flex-shrink:0;"></i>
            Solo puedes recomendar a perfiles con los que tienes amistad.
        </li>
        <li style="font-size:.78rem;color:var(--theme-muted);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-clock" style="color:var(--accent-pink,#e056a0);margin-top:.15rem;flex-shrink:0;"></i>
            Los anuncios expiran automáticamente a los 4 días.
        </li>
    </ul>
</div>
@endpush

{{-- ══ SIDEBAR DERECHO ══ --}}
@push('sidebar-right')
@include('layouts.sidebar-right')

{{-- Card extra: Anuncios activos recientes --}}
@if($tab === 'announcements')
<div class="l69-sidebar-card" style="margin-top:.75rem;">
    <div class="l69-sidebar-card__title"><i class="fas fa-fire"></i> Anuncios Activos</div>
    @php
        $sidebarAnns = \Illuminate\Support\Facades\DB::table('announcements as a')
            ->join('users as u', \Illuminate\Support\Facades\DB::raw('u.id::text'), '=', \Illuminate\Support\Facades\DB::raw('a.user_id::text'))
            ->leftJoin('profiles as pr', \Illuminate\Support\Facades\DB::raw('pr.user_id::text'), '=', \Illuminate\Support\Facades\DB::raw('u.id::text'))
            ->where('a.status', 'active')
            ->whereRaw("COALESCE(a.expires_at, a.created_at + INTERVAL '4 days') > NOW()")
            ->orderByDesc('a.created_at')
            ->limit(5)
            ->select([
                'a.id', 'a.title', 'a.created_at',
                \Illuminate\Support\Facades\DB::raw('COALESCE(pr.display_name, u.username) AS display_name'),
                'pr.nickname', 'pr.profile_type',
            ])
            ->get();
    @endphp
    @forelse($sidebarAnns as $sa)
    <div style="padding:.4rem 0;border-bottom:1px solid rgba(180,60,120,.1);font-size:.78rem;">
        <div style="font-weight:600;color:var(--theme-text);margin-bottom:.1rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
            {{ $sa->title }}
        </div>
        <div style="color:var(--theme-muted);">
            {{ $sa->nickname ?? $sa->display_name }} · {{ \Carbon\Carbon::parse($sa->created_at)->diffForHumans() }}
        </div>
    </div>
    @empty
    <p style="font-size:.78rem;color:var(--theme-muted);margin:0;">Sin anuncios activos.</p>
    @endforelse
</div>
@endif

{{-- Card: Mis estadísticas de mensajes --}}
<div class="l69-sidebar-card" style="margin-top:.75rem;">
    <div class="l69-sidebar-card__title"><i class="fas fa-chart-pie"></i> Mis Mensajes</div>
    @php
        $uid = (string) auth()->id();
        $statUnread = \Illuminate\Support\Facades\DB::table('messages')->whereRaw('receiver_id::text = ?', [$uid])->where('read', false)->count();
        $statTotal  = \Illuminate\Support\Facades\DB::table('messages')->whereRaw('receiver_id::text = ? OR sender_id::text = ?', [$uid, $uid])->count();
        $statFriends = \Illuminate\Support\Facades\DB::table('friendships')->whereRaw('(sender_id::text = ? OR receiver_id::text = ?)', [$uid, $uid])->where('status', 'accepted')->count();
    @endphp
    <div class="l69-stat-grid">
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $statUnread }}</div>
            <div class="l69-stat__label">Sin leer</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $statTotal }}</div>
            <div class="l69-stat__label">Total</div>
        </div>
        <div class="l69-stat" style="grid-column:span 2;">
            <div class="l69-stat__value">{{ $statFriends }}</div>
            <div class="l69-stat__label">Amigos confirmados</div>
        </div>
    </div>
</div>
@endpush

@section('content')
<div class="l69-msg-wrap">

  {{-- ══ TABS ══ --}}
  <nav class="l69-msg-tabs">
    @php
      $tabs = [
        'inbox'         => ['icon' => '💬', 'label' => 'Bandeja'],
        'comments'      => ['icon' => '🗨️',  'label' => 'Comentarios'],
        'friends'       => ['icon' => '🤝', 'label' => 'Amistades'],
        'reviews'       => ['icon' => '⭐', 'label' => 'Recomendaciones'],
        'announcements' => ['icon' => '📢', 'label' => 'Anuncios'],
      ];
    @endphp
    @foreach($tabs as $key => $meta)
      <a href="{{ route('messages.index', ['tab' => $key]) }}"
         class="l69-msg-tab {{ $tab === $key ? 'active' : '' }}">
        <span class="tab-icon">{{ $meta['icon'] }}</span>
        <span class="tab-label">{{ $meta['label'] }}</span>
        @if($key === 'inbox' && ($unreadTotal ?? 0) > 0)
          <span class="l69-tab-badge">{{ $unreadTotal }}</span>
        @endif
      </a>
    @endforeach
  </nav>

  <div class="l69-msg-body">

    {{-- ─── BANDEJA ─── --}}
    @if($tab === 'inbox')
    <h2 class="l69-msg-title">💬 Conversaciones</h2>
    @forelse($conversations as $c)
      @php $isMe = (string)$c->sender_id === (string)Auth::id(); @endphp
      <div class="l69-card l69-conv-card" data-partner="{{ $c->partner_id }}">
        <div class="l69-card-avatar">
          @if($c->avatar_photo_id)
            <img src="{{ route('photos.serve', $c->avatar_photo_id) }}" alt="">
          @else
            <div class="l69-avatar-ph">{{ mb_substr($c->display_name ?? '?', 0, 1) }}</div>
          @endif
          @if(($c->unread_count ?? 0) > 0)
            <span class="l69-unread-dot">{{ $c->unread_count }}</span>
          @endif
        </div>
        <div class="l69-card-info">
          <div class="l69-card-name">
            {{ $c->nickname ?? $c->display_name }}
            @if($c->verified_profile)<span class="l69-verified">✓</span>@endif
          </div>
          <div class="l69-card-preview {{ ($c->unread_count ?? 0) > 0 ? 'is-unread' : '' }}">
            {{ $isMe ? 'Tú: ' : '' }}{{ Str::limit($c->last_message, 55) }}
          </div>
        </div>
        <div class="l69-card-meta">
          <span class="l69-card-time">{{ \Carbon\Carbon::parse($c->last_at)->diffForHumans() }}</span>
          <button class="l69-btn-accent btn-open-conv"
                  data-partner="{{ $c->partner_id }}"
                  data-name="{{ $c->nickname ?? $c->display_name }}">
            Abrir
          </button>
        </div>
      </div>
    @empty
      <p class="l69-msg-empty">No tienes conversaciones todavía.</p>
    @endforelse

    {{-- Modal --}}
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
          <button type="submit" class="l69-btn-accent">Enviar</button>
        </form>
      </div>
    </div>
    @endif

    {{-- ─── COMENTARIOS ─── --}}
    @if($tab === 'comments')
    <h2 class="l69-msg-title">🗨️ Comentarios en fotos</h2>
    @forelse($photoComments as $pc)
      <div class="l69-card">
        <div class="l69-card-avatar">
          @if($pc->commenter_avatar_id)
            <img src="{{ route('photos.serve', $pc->commenter_avatar_id) }}" alt="">
          @else
            <div class="l69-avatar-ph">{{ mb_substr($pc->commenter_name ?? '?', 0, 1) }}</div>
          @endif
        </div>
        <div class="l69-card-info">
          <div class="l69-card-name">{{ $pc->commenter_nick ?? $pc->commenter_name }}</div>
          <div class="l69-card-preview">{{ $pc->body }}</div>
          <div class="l69-card-sub">En: <em>{{ $pc->caption ?? 'Sin título' }}</em></div>
        </div>
        <div class="l69-card-meta">
          <span class="l69-card-time">{{ \Carbon\Carbon::parse($pc->created_at)->diffForHumans() }}</span>
        </div>
      </div>
    @empty
      <p class="l69-msg-empty">Sin comentarios en tus fotos aún.</p>
    @endforelse

    @if($videoComments->isNotEmpty())
      <h2 class="l69-msg-title" style="margin-top:1.5rem;">🎬 Comentarios en videos</h2>
      @foreach($videoComments as $vc)
        <div class="l69-card">
          <div class="l69-card-info">
            <div class="l69-card-name">{{ $vc->commenter_nick ?? $vc->commenter_name }}</div>
            <div class="l69-card-preview">{{ $vc->body }}</div>
            <div class="l69-card-sub">En: <em>{{ $vc->title ?? 'Video' }}</em></div>
          </div>
          <div class="l69-card-meta">
            <span class="l69-card-time">{{ \Carbon\Carbon::parse($vc->created_at)->diffForHumans() }}</span>
          </div>
        </div>
      @endforeach
    @endif
    @endif

    {{-- ─── AMISTADES ─── --}}
    @if($tab === 'friends')
    @if($friendsPending->isNotEmpty())
      <h2 class="l69-msg-title">🔔 Solicitudes recibidas ({{ $friendsPending->count() }})</h2>
      @foreach($friendsPending as $f)
        <div class="l69-card">
          <div class="l69-card-avatar">
            @if($f->avatar_photo_id)
              <img src="{{ route('photos.serve', $f->avatar_photo_id) }}" alt="">
            @else
              <div class="l69-avatar-ph">{{ mb_substr($f->display_name ?? '?', 0, 1) }}</div>
            @endif
          </div>
          <div class="l69-card-info">
            <div class="l69-card-name">
              {{ $f->nickname ?? $f->display_name }}
              @if($f->verified_profile)<span class="l69-verified">✓</span>@endif
            </div>
            <div class="l69-card-sub">{{ $f->profile_type ?? '' }} · {{ $f->city ?? '' }}</div>
          </div>
          <div class="l69-friend-actions">
            <button class="l69-btn-accept btn-friend-action" data-id="{{ $f->friendship_id }}" data-action="accept">✓ Aceptar</button>
            <button class="l69-btn-muted btn-friend-action"  data-id="{{ $f->friendship_id }}" data-action="reject">✕ Rechazar</button>
          </div>
        </div>
      @endforeach
    @endif

    @if($friendsSent->isNotEmpty())
      <h2 class="l69-msg-title" style="margin-top:1.5rem;">📤 Enviadas ({{ $friendsSent->count() }})</h2>
      @foreach($friendsSent as $f)
        <div class="l69-card">
          <div class="l69-card-avatar">
            @if($f->avatar_photo_id)
              <img src="{{ route('photos.serve', $f->avatar_photo_id) }}" alt="">
            @else
              <div class="l69-avatar-ph">{{ mb_substr($f->display_name ?? '?', 0, 1) }}</div>
            @endif
          </div>
          <div class="l69-card-info">
            <div class="l69-card-name">{{ $f->nickname ?? $f->display_name }}</div>
            <div class="l69-card-sub">{{ $f->profile_type ?? '' }} · {{ $f->city ?? '' }}</div>
          </div>
          <div class="l69-card-meta">
            <span class="l69-card-time">{{ \Carbon\Carbon::parse($f->created_at)->diffForHumans() }}</span>
          </div>
        </div>
      @endforeach
    @endif

    @if($friendsAccepted->isNotEmpty())
      <h2 class="l69-msg-title" style="margin-top:1.5rem;">✅ Amigos ({{ $friendsAccepted->count() }})</h2>
      <div class="l69-friends-grid">
        @foreach($friendsAccepted as $f)
          <div class="l69-friend-chip">
            @if($f->avatar_photo_id)
              <img src="{{ route('photos.serve', $f->avatar_photo_id) }}" alt="">
            @else
              <div class="l69-avatar-ph sm">{{ mb_substr($f->display_name ?? '?', 0, 1) }}</div>
            @endif
            <span>{{ $f->nickname ?? $f->display_name }}</span>
            @if($f->verified_profile)<span class="l69-verified">✓</span>@endif
          </div>
        @endforeach
      </div>
    @endif

    @if($friendsPending->isEmpty() && $friendsSent->isEmpty() && $friendsAccepted->isEmpty())
      <p class="l69-msg-empty">No tienes solicitudes ni amigos todavía.</p>
    @endif
    @endif

    {{-- ─── RECOMENDACIONES ─── --}}
    @if($tab === 'reviews')
    @if($reviewsReceived->isNotEmpty())
      <h2 class="l69-msg-title">⭐ Recomendaciones recibidas</h2>
      @foreach($reviewsReceived as $r)
        <div class="l69-card l69-review-card {{ $r->type }}">
          <div class="l69-card-avatar">
            @if($r->avatar_photo_id)
              <img src="{{ route('photos.serve', $r->avatar_photo_id) }}" alt="">
            @else
              <div class="l69-avatar-ph">{{ mb_substr($r->reviewer_name ?? '?', 0, 1) }}</div>
            @endif
          </div>
          <div class="l69-card-info">
            <div class="l69-card-name">{{ $r->reviewer_nick ?? $r->reviewer_name }}</div>
            @if($r->body)<div class="l69-card-preview">{{ $r->body }}</div>@endif
          </div>
          <div class="l69-card-meta">
            <span class="l69-review-badge {{ $r->type }}">{{ $r->type === 'positive' ? '👍 Positiva' : '👎 Negativa' }}</span>
            <span class="l69-card-time">{{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</span>
          </div>
        </div>
      @endforeach
    @endif

    @if($reviewsGiven->isNotEmpty())
      <h2 class="l69-msg-title" style="margin-top:1.5rem;">📝 Recomendaciones que dejé</h2>
      @foreach($reviewsGiven as $r)
        <div class="l69-card l69-review-card {{ $r->type }}">
          <div class="l69-card-info">
            <div class="l69-card-name">Para: {{ $r->reviewed_nick ?? $r->reviewed_name }}</div>
            @if($r->body)<div class="l69-card-preview">{{ $r->body }}</div>@endif
          </div>
          <div class="l69-card-meta">
            <span class="l69-review-badge {{ $r->type }}">{{ $r->type === 'positive' ? '👍 Positiva' : '👎 Negativa' }}</span>
            <span class="l69-card-time">{{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</span>
          </div>
        </div>
      @endforeach
    @endif

    @if($canReview->isNotEmpty())
      <h2 class="l69-msg-title" style="margin-top:1.5rem;">✍️ Puedes recomendar</h2>
      @foreach($canReview as $fr)
        <div class="l69-card">
          <div class="l69-card-avatar">
            @if($fr->avatar_photo_id)
              <img src="{{ route('photos.serve', $fr->avatar_photo_id) }}" alt="">
            @else
              <div class="l69-avatar-ph">{{ mb_substr($fr->display_name ?? '?', 0, 1) }}</div>
            @endif
          </div>
          <div class="l69-card-info">
            <div class="l69-card-name">{{ $fr->nickname ?? $fr->display_name }}</div>
          </div>
          <form class="review-send-form" data-reviewed="{{ $fr->user_id }}">
            <select name="type" class="l69-select" required>
              <option value="">Tipo…</option>
              <option value="positive">👍 Positiva</option>
              <option value="negative">👎 Negativa</option>
            </select>
            <textarea name="body" class="l69-textarea" placeholder="Comentario opcional…" maxlength="500" rows="2"></textarea>
            <button type="submit" class="l69-btn-accent">Enviar</button>
          </form>
        </div>
      @endforeach
    @endif

    @if($reviewsReceived->isEmpty() && $reviewsGiven->isEmpty() && $canReview->isEmpty())
      <p class="l69-msg-empty">Sin recomendaciones aún. Agrega amigos primero.</p>
    @endif
    @endif

    {{-- ─── ANUNCIOS ─── --}}
    @if($tab === 'announcements')

    {{-- Formulario --}}
    <div class="l69-ann-form-wrap">
      <h2 class="l69-msg-title">📢 Publicar un anuncio</h2>
      <form id="announcement-form" class="l69-ann-form">
        @csrf
        <div class="l69-field">
          <label class="l69-label">Título <span class="l69-req">*</span></label>
          <input type="text" name="title" class="l69-input" maxlength="120"
                 placeholder="Ej: Buscamos pareja para intercambio…" required>
        </div>
        <div class="l69-field">
          <label class="l69-label">Dirigido a</label>
          <div class="l69-checkgroup">
            <label class="l69-check"><input type="checkbox" name="directed_to[]" value="singles"> Singles</label>
            <label class="l69-check"><input type="checkbox" name="directed_to[]" value="parejas"> Parejas</label>
            <label class="l69-check"><input type="checkbox" name="directed_to[]" value="unicornio"> Chicas unicornio</label>
          </div>
        </div>
        <div class="l69-field">
          <label class="l69-label">¿Qué busco?</label>
          <div class="l69-checkgroup two-cols">
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="intercambios"> Intercambios</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="cuckold"> Cuckold</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="fiesta"> Fiesta / Antro</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="trio_mhm"> Trío MHM</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="trio_hmh"> Trío HMH</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="gangbang"> Gang bang</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="cita_soft"> Cita Soft</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="reunion_swinger"> Reunión Swinger</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="encuentro_casual"> Encuentro casual</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="voyeurismo"> Voyeurismo</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="jugar"> Jugar / Divertirnos</label>
            <label class="l69-check"><input type="checkbox" name="what_looking[]" value="conocernos"> Conocernos y después ver</label>
          </div>
        </div>
        <div class="l69-field">
          <label class="l69-label">Descripción</label>
          <textarea name="proposal" class="l69-textarea" maxlength="600" rows="3"
                    placeholder="Cuéntanos más sobre lo que buscas…"></textarea>
        </div>
        <div class="l69-field">
          <label class="l69-label">
            Fecha del encuentro
            <span class="l69-hint">(máx. 4 días a partir de hoy)</span>
          </label>
          <input type="date" name="event_date" class="l69-input"
                 min="{{ now()->addDay()->toDateString() }}"
                 max="{{ now()->addDays(4)->toDateString() }}">
        </div>
        <button type="submit" class="l69-btn-accent l69-btn-full">📢 Publicar anuncio</button>
      </form>
    </div>

    {{-- Mis anuncios --}}
    @if($myAnnouncements->isNotEmpty())
      <h2 class="l69-msg-title" style="margin-top:2rem;">📋 Mis anuncios</h2>
      @foreach($myAnnouncements as $a)
        <div class="l69-ann-card {{ $a->is_expired || $a->status === 'closed' ? 'is-expired' : '' }}">
          <div class="l69-ann-header">
            <strong class="l69-ann-title">{{ $a->title }}</strong>
            <div class="l69-ann-badges">
              @if($a->status === 'closed')
                <span class="l69-ann-badge closed">Cerrado</span>
              @elseif($a->is_expired)
                <span class="l69-ann-badge expired">Expirado</span>
              @else
                <span class="l69-ann-badge active">Activo</span>
              @endif
            </div>
          </div>
          @if(!empty($a->directed_to))
            <div class="l69-ann-tags">
              <span class="l69-ann-tag-label">Para:</span>
              @foreach($a->directed_to as $t)<span class="l69-tag-pink">{{ $t }}</span>@endforeach
            </div>
          @endif
          @if(!empty($a->what_looking))
            <div class="l69-ann-tags">
              @foreach($a->what_looking as $t)<span class="l69-tag-blue">{{ str_replace('_', ' ', $t) }}</span>@endforeach
            </div>
          @endif
          @if($a->proposal)
            <p class="l69-ann-body">{{ $a->proposal }}</p>
          @endif
          <div class="l69-ann-footer">
            <span class="l69-card-time">{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
            @if($a->status === 'active' && !$a->is_expired)
              <button class="l69-btn-ghost btn-close-ann" data-id="{{ $a->id }}">Cerrar anuncio</button>
            @endif
          </div>
        </div>
      @endforeach
    @endif

    {{-- Anuncios de otros --}}
    <h2 class="l69-msg-title" style="margin-top:2rem;">🔍 Anuncios recientes</h2>
    @forelse($announcements as $a)
      <div class="l69-ann-card {{ $a->is_expired ? 'is-expired' : '' }}">

        {{-- Perfil del autor --}}
        <div class="l69-ann-author">
          <div class="l69-ann-author-left">
            <div class="l69-card-avatar">
              @if($a->avatar_photo_id)
                <img src="{{ route('photos.serve', $a->avatar_photo_id) }}" alt="">
              @else
                <div class="l69-avatar-ph">{{ mb_substr($a->display_name ?? '?', 0, 1) }}</div>
              @endif
            </div>
            <div>
              @if($a->nickname)
                <a href="{{ route('profile.show', $a->nickname) }}" class="l69-ann-author-name">
                  {{ $a->nickname }}
                  @if($a->verified_profile)<span class="l69-verified">✓</span>@endif
                </a>
              @else
                <span class="l69-ann-author-name">{{ $a->display_name }}</span>
              @endif
              <div class="l69-ann-author-meta">
                {{ $a->profile_type ?? '' }}
                @if($a->city) · <i class="fas fa-map-marker-alt" style="font-size:.65rem;"></i> {{ $a->city }}@endif
              </div>
            </div>
          </div>
          <div class="l69-ann-author-actions">
            @if($a->nickname)
              <a href="{{ route('profile.show', $a->nickname) }}" class="l69-btn-ghost btn-sm">
                <i class="fas fa-user"></i> Ver perfil
              </a>
            @endif
            <button class="l69-btn-accent btn-sm btn-msg-ann"
                    data-partner="{{ $a->user_id ?? '' }}"
                    data-name="{{ $a->nickname ?? $a->display_name }}">
              <i class="fas fa-paper-plane"></i> Mensaje
            </button>
          </div>
        </div>

        {{-- Contenido del anuncio --}}
        <div class="l69-ann-content">
          <div class="l69-ann-header">
            <strong class="l69-ann-title">{{ $a->title }}</strong>
            @if($a->is_expired)<span class="l69-ann-badge expired">Expirado</span>@endif
          </div>
          @if(!empty($a->directed_to))
            <div class="l69-ann-tags">
              <span class="l69-ann-tag-label">Para:</span>
              @foreach($a->directed_to as $t)<span class="l69-tag-pink">{{ $t }}</span>@endforeach
            </div>
          @endif
          @if(!empty($a->what_looking))
            <div class="l69-ann-tags">
              @foreach($a->what_looking as $t)<span class="l69-tag-blue">{{ str_replace('_', ' ', $t) }}</span>@endforeach
            </div>
          @endif
          @if($a->proposal)
            <p class="l69-ann-body">{{ $a->proposal }}</p>
          @endif
          <div class="l69-ann-footer">
            <span class="l69-card-time">{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
            @if($a->event_date)
              <span class="l69-ann-date"><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($a->event_date)->format('d M Y') }}</span>
            @endif
          </div>
        </div>

      </div>
    @empty
      <p class="l69-msg-empty">No hay anuncios activos en este momento.</p>
    @endforelse
    @endif

  </div>{{-- /.l69-msg-body --}}
</div>{{-- /.l69-msg-wrap --}}

{{-- Modal conversación (reutilizable desde anuncios también) --}}
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
      <button type="submit" class="l69-btn-accent">Enviar</button>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
/* ══════════════════════════════════════════
   VARIABLES SEMÁNTICAS — respetan modo día/noche
   ══════════════════════════════════════════ */
.l69-msg-wrap {
  --_bg-card:    var(--bg-card,    #ffffff);
  --_bg-input:   var(--bg-input,   #f0eee8);
  --_text:       var(--text-primary,  #1a1523);
  --_text-sub:   var(--text-secondary, #5a5470);
  --_text-muted: var(--text-muted,  #9590a8);
  --_border:     var(--border-color, rgba(26,21,35,.10));
  --_accent:     #c0392b;
  --_accent-pink:#e056a0;
  --_radius:     10px;
}

/* ══ LAYOUT WRAPPER ══ */
.l69-msg-wrap { width: 100%; }

/* ══ TABS ══ */
.l69-msg-tabs {
  display: flex; gap: .35rem; flex-wrap: wrap;
  border-bottom: 2px solid var(--_border);
  margin-bottom: 1.25rem;
}
.l69-msg-tab {
  display: flex; align-items: center; gap: .4rem;
  padding: .5rem .9rem; border-radius: 8px 8px 0 0;
  text-decoration: none; font-size: .85rem; font-weight: 600;
  color: var(--_text-muted);
  transition: background .15s, color .15s;
}
.l69-msg-tab:hover  { background: rgba(192,57,43,.07); color: var(--_text); }
.l69-msg-tab.active { background: var(--_accent); color: #fff; }
.l69-tab-badge {
  background: #e74c3c; color: #fff;
  font-size: .65rem; font-weight: 700;
  border-radius: 99px; padding: 0 .4rem; line-height: 1.5;
}

/* ══ TÍTULOS Y VACÍOS ══ */
.l69-msg-title {
  font-size: .95rem; font-weight: 700;
  color: var(--_text); margin: 0 0 .75rem;
}
.l69-msg-empty { color: var(--_text-muted); font-size: .88rem; padding: .75rem 0; }

/* ══ TARJETA BASE ══ */
.l69-card {
  display: flex; align-items: flex-start; gap: .8rem;
  background: var(--_bg-card);
  border: 1px solid var(--_border);
  border-radius: var(--_radius);
  padding: .85rem 1rem;
  margin-bottom: .55rem;
  transition: border-color .15s, box-shadow .15s;
}
.l69-card:hover { border-color: rgba(192,57,43,.35); box-shadow: 0 2px 12px rgba(192,57,43,.08); }

/* ══ AVATARES ══ */
.l69-card-avatar { position: relative; flex-shrink: 0; }
.l69-card-avatar img {
  width: 44px; height: 44px; border-radius: 50%;
  object-fit: cover; border: 2px solid var(--_border);
  display: block;
}
.l69-avatar-ph {
  width: 44px; height: 44px; border-radius: 50%;
  background: var(--_accent); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; font-weight: 700; flex-shrink: 0;
}
.l69-avatar-ph.sm { width: 30px; height: 30px; font-size: .8rem; }
.l69-unread-dot {
  position: absolute; top: -3px; right: -3px;
  background: #e74c3c; color: #fff;
  font-size: .62rem; font-weight: 700;
  border-radius: 99px; padding: 1px 5px;
  border: 2px solid var(--_bg-card);
}

/* ══ INFO ══ */
.l69-card-info    { flex: 1; min-width: 0; }
.l69-card-name    { font-weight: 600; font-size: .9rem; color: var(--_text); margin-bottom: .18rem; }
.l69-card-preview { font-size: .82rem; color: var(--_text-sub); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.l69-card-preview.is-unread { color: var(--_text); font-weight: 700; }
.l69-card-sub     { font-size: .77rem; color: var(--_text-muted); margin-top: .12rem; }
.l69-verified     { color: #27ae60; font-size: .72rem; margin-left: .2rem; }

/* ══ META ══ */
.l69-card-meta { display: flex; flex-direction: column; align-items: flex-end; gap: .3rem; flex-shrink: 0; }
.l69-card-time { font-size: .73rem; color: var(--_text-muted); white-space: nowrap; }

/* ══ BOTONES ══ */
.l69-btn-accent {
  background: var(--_accent); color: #fff;
  border: none; border-radius: 7px;
  padding: .35rem .8rem; font-size: .8rem; font-weight: 700;
  cursor: pointer; transition: opacity .15s; white-space: nowrap;
  text-decoration: none; display: inline-flex; align-items: center; gap: .35rem;
}
.l69-btn-accent:hover { opacity: .85; }
.l69-btn-full   { width: 100%; justify-content: center; padding: .65rem; font-size: .9rem; margin-top: .25rem; }
.l69-btn-ghost  {
  background: transparent;
  border: 1px solid var(--_border);
  color: var(--_text-sub); border-radius: 7px;
  padding: .3rem .7rem; font-size: .78rem; font-weight: 600;
  cursor: pointer; transition: border-color .15s, color .15s;
  text-decoration: none; display: inline-flex; align-items: center; gap: .3rem;
}
.l69-btn-ghost:hover { border-color: var(--_accent); color: var(--_accent); }
.l69-btn-accept {
  background: #27ae60; color: #fff;
  border: none; border-radius: 7px;
  padding: .3rem .7rem; font-size: .78rem; font-weight: 700;
  cursor: pointer; transition: opacity .15s;
}
.l69-btn-accept:hover { opacity: .85; }
.l69-btn-muted  {
  background: transparent; border: 1px solid var(--_border);
  color: var(--_text-muted); border-radius: 7px;
  padding: .3rem .7rem; font-size: .78rem; font-weight: 600;
  cursor: pointer; transition: border-color .15s, color .15s;
}
.l69-btn-muted:hover { border-color: #e74c3c; color: #e74c3c; }
.btn-sm { padding: .25rem .6rem !important; font-size: .75rem !important; }

/* ══ AMISTADES ══ */
.l69-friend-actions { display: flex; flex-direction: column; gap: .35rem; flex-shrink: 0; }
.l69-friends-grid   { display: flex; flex-wrap: wrap; gap: .45rem; }
.l69-friend-chip {
  display: flex; align-items: center; gap: .35rem;
  background: var(--_bg-card);
  border: 1px solid var(--_border);
  border-radius: 99px; padding: .25rem .7rem .25rem .25rem;
  font-size: .8rem; color: var(--_text);
}
.l69-friend-chip img { width: 26px; height: 26px; border-radius: 50%; object-fit: cover; }

/* ══ RECOMENDACIONES ══ */
.l69-review-card.positive { border-left: 3px solid #27ae60; }
.l69-review-card.negative { border-left: 3px solid #e74c3c; }
.l69-review-badge {
  font-size: .72rem; font-weight: 700;
  border-radius: 5px; padding: .12rem .4rem; white-space: nowrap;
}
.l69-review-badge.positive { background: rgba(39,174,96,.12); color: #27ae60; }
.l69-review-badge.negative { background: rgba(231,76,60,.12);  color: #e74c3c; }

/* ══ FORMULARIO GENÉRICO ══ */
.l69-field    { display: flex; flex-direction: column; gap: .3rem; }
.l69-label    { font-size: .82rem; font-weight: 600; color: var(--_text-sub); }
.l69-hint     { font-weight: 400; font-size: .73rem; color: var(--_text-muted); }
.l69-req      { color: #e74c3c; }
.l69-input,
.l69-textarea,
.l69-select {
  width: 100%; box-sizing: border-box;
  background: var(--_bg-input);
  border: 1px solid var(--_border);
  color: var(--_text);
  border-radius: 8px; padding: .5rem .75rem;
  font-size: .87rem; font-family: inherit;
  transition: border-color .15s;
}
.l69-input:focus,
.l69-textarea:focus,
.l69-select:focus {
  outline: none;
  border-color: rgba(192,57,43,.5);
  box-shadow: 0 0 0 3px rgba(192,57,43,.08);
}
.l69-textarea { resize: vertical; }
.l69-checkgroup { display: flex; flex-wrap: wrap; gap: .45rem .9rem; }
.l69-checkgroup.two-cols { column-gap: 1.25rem; }
.l69-check {
  display: flex; align-items: center; gap: .3rem;
  font-size: .82rem; color: var(--_text); cursor: pointer;
}
.l69-check input[type="checkbox"] { accent-color: var(--_accent); width: 14px; height: 14px; }

/* ══ FORMULARIO ANUNCIO ══ */
.l69-ann-form-wrap {
  background: var(--_bg-card);
  border: 1px solid var(--_border);
  border-radius: 12px; padding: 1.25rem;
  margin-bottom: 1.25rem;
}
.l69-ann-form { display: flex; flex-direction: column; gap: .85rem; }

/* ══ TARJETA ANUNCIO ══ */
.l69-ann-card {
  background: var(--_bg-card);
  border: 1px solid var(--_border);
  border-radius: 12px; padding: 1rem 1.1rem;
  margin-bottom: .7rem;
  transition: border-color .15s, box-shadow .15s;
}
.l69-ann-card:hover:not(.is-expired) { border-color: rgba(192,57,43,.3); box-shadow: 0 2px 12px rgba(192,57,43,.07); }
.l69-ann-card.is-expired { opacity: .5; filter: grayscale(.35); }

/* Autor del anuncio */
.l69-ann-author {
  display: flex; align-items: center; justify-content: space-between;
  gap: .75rem; flex-wrap: wrap;
  padding-bottom: .75rem; margin-bottom: .75rem;
  border-bottom: 1px solid var(--_border);
}
.l69-ann-author-left    { display: flex; align-items: center; gap: .65rem; }
.l69-ann-author-name    { font-weight: 700; font-size: .88rem; color: var(--_text); text-decoration: none; }
.l69-ann-author-name:hover { color: var(--_accent); }
.l69-ann-author-meta    { font-size: .75rem; color: var(--_text-muted); margin-top: .1rem; }
.l69-ann-author-actions { display: flex; gap: .4rem; flex-wrap: wrap; }

/* Contenido */
.l69-ann-content  { }
.l69-ann-header   { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; margin-bottom: .5rem; }
.l69-ann-title    { font-size: .9rem; color: var(--_text); }
.l69-ann-badges   { display: flex; gap: .3rem; margin-left: auto; }
.l69-ann-badge    { font-size: .68rem; font-weight: 700; padding: .12rem .45rem; border-radius: 99px; }
.l69-ann-badge.active  { background: rgba(39,174,96,.12);  color: #27ae60; }
.l69-ann-badge.expired { background: rgba(149,165,166,.15); color: #95a5a6; }
.l69-ann-badge.closed  { background: rgba(127,140,141,.15); color: #7f8c8d; }

.l69-ann-tags     { display: flex; flex-wrap: wrap; gap: .3rem; margin-bottom: .4rem; align-items: center; }
.l69-ann-tag-label{ font-size: .72rem; color: var(--_text-muted); }
.l69-tag-pink     { background: rgba(224,86,160,.12); color: #c0266e; font-size: .71rem; border-radius: 99px; padding: .1rem .5rem; }
.l69-tag-blue     { background: rgba(52,152,219,.12);  color: #2471a3; font-size: .71rem; border-radius: 99px; padding: .1rem .5rem; }

/* modo noche — invertir colores de tags */
[data-theme="dark"] .l69-tag-pink { color: #e87b6e; background: rgba(192,57,43,.2); }
[data-theme="dark"] .l69-tag-blue { color: #7ec8e3; background: rgba(52,152,219,.18); }

.l69-ann-body { font-size: .84rem; color: var(--_text-sub); margin: .35rem 0; line-height: 1.55; }
.l69-ann-footer {
  display: flex; align-items: center; gap: .65rem;
  flex-wrap: wrap; margin-top: .45rem;
}
.l69-ann-date { font-size: .76rem; color: var(--_text-muted); }

/* ══ MODAL ══ */
.l69-modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.55);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
}
.l69-modal-overlay.hidden { display: none; }
.l69-modal-box {
  background: var(--_bg-card);
  border: 1px solid var(--_border);
  border-radius: 14px;
  width: min(520px, 96vw);
  display: flex; flex-direction: column;
  max-height: 85vh; overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
.l69-modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: .75rem 1rem;
  border-bottom: 1px solid var(--_border);
  font-weight: 700; font-size: .92rem; color: var(--_text);
}
.l69-modal-close {
  background: transparent; border: none; cursor: pointer;
  color: var(--_text-muted); font-size: 1rem; padding: .15rem .4rem;
  border-radius: 5px; transition: background .15s, color .15s;
}
.l69-modal-close:hover { background: rgba(0,0,0,.07); color: var(--_text); }
.l69-modal-messages {
  flex: 1; overflow-y: auto; padding: 1rem;
  display: flex; flex-direction: column; gap: .45rem;
}
.l69-msg-bubble {
  max-width: 72%; padding: .45rem .8rem;
  border-radius: 10px; font-size: .85rem; line-height: 1.45;
  word-break: break-word;
}
.l69-msg-bubble.mine   { background: var(--_accent); color: #fff; align-self: flex-end; border-bottom-right-radius: 2px; }
.l69-msg-bubble.theirs { background: var(--_bg-input); color: var(--_text); align-self: flex-start; border-bottom-left-radius: 2px; }
.l69-msg-time { font-size: .69rem; opacity: .65; display: block; margin-top: .12rem; }
.l69-modal-send {
  display: flex; gap: .5rem; padding: .75rem 1rem;
  border-top: 1px solid var(--_border);
}
.l69-modal-send textarea {
  flex: 1; resize: none;
  background: var(--_bg-input);
  border: 1px solid var(--_border);
  color: var(--_text);
  border-radius: 8px; padding: .45rem .65rem;
  font-size: .85rem; font-family: inherit;
}
.l69-modal-send textarea:focus { outline: none; border-color: rgba(192,57,43,.4); }
</style>
@endpush

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const ME   = '{{ Auth::id() }}';

async function postJson(url, data) {
  const r = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body: JSON.stringify(data),
  });
  return r.json();
}

/* ══ MODAL ══ */
const modal     = document.getElementById('conv-modal');
const modalName = document.getElementById('conv-modal-name');
const modalMsgs = document.getElementById('conv-modal-messages');
const sendForm  = document.getElementById('conv-send-form');
const sendBody  = document.getElementById('conv-body');
const recvId    = document.getElementById('conv-receiver-id');

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtTime(iso) {
  if (!iso) return '';
  return new Date(iso).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
}

function renderMessages(msgs) {
  modalMsgs.innerHTML = '';
  if (!msgs.length) {
    modalMsgs.innerHTML = '<p style="text-align:center;font-size:.8rem;color:var(--text-muted,#999)">Sin mensajes aún. ¡Escribe el primero!</p>';
    return;
  }
  msgs.forEach(m => {
    const mine = String(m.sender_id) === ME;
    const d = document.createElement('div');
    d.className = `l69-msg-bubble ${mine ? 'mine' : 'theirs'}`;
    d.innerHTML = `${escHtml(m.body)}<span class="l69-msg-time">${fmtTime(m.created_at)}</span>`;
    modalMsgs.appendChild(d);
  });
  modalMsgs.scrollTop = modalMsgs.scrollHeight;
}

async function openConversation(partnerId, name) {
  if (!partnerId) { alert('No se puede abrir la conversación.'); return; }
  modalName.textContent = name;
  recvId.value = partnerId;
  modal.classList.remove('hidden');
  modalMsgs.innerHTML = '<p style="text-align:center;font-size:.8rem;color:var(--text-muted,#999)">Cargando…</p>';
  try {
    const data = await fetch(`/mensajes/conversacion/${partnerId}`, { headers: { Accept: 'application/json' } }).then(r => r.json());
    renderMessages(data.messages ?? []);
  } catch(e) {
    modalMsgs.innerHTML = '<p style="text-align:center;color:#e74c3c;font-size:.8rem">Error al cargar mensajes.</p>';
  }
}

/* Abrir desde bandeja */
document.querySelectorAll('.btn-open-conv').forEach(btn =>
  btn.addEventListener('click', () => openConversation(btn.dataset.partner, btn.dataset.name))
);
/* Abrir desde anuncio */
document.querySelectorAll('.btn-msg-ann').forEach(btn =>
  btn.addEventListener('click', () => openConversation(btn.dataset.partner, btn.dataset.name))
);

document.getElementById('conv-modal-close')?.addEventListener('click', () => modal.classList.add('hidden'));
modal?.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

sendForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const body = sendBody.value.trim();
  if (!body) return;
  const btn = sendForm.querySelector('button[type=submit]');
  btn.disabled = true;
  const data = await postJson('/mensajes/enviar', { receiver_id: recvId.value, body });
  btn.disabled = false;
  if (data.ok) { sendBody.value = ''; await openConversation(recvId.value, modalName.textContent); }
});

/* ══ AMISTADES ══ */
document.querySelectorAll('.btn-friend-action').forEach(btn =>
  btn.addEventListener('click', async () => {
    btn.disabled = true;
    const data = await postJson(`/mensajes/amistad/${btn.dataset.id}`, { action: btn.dataset.action });
    if (data.ok) btn.closest('.l69-card').remove();
    else btn.disabled = false;
  })
);

/* ══ RECOMENDACIONES ══ */
document.querySelectorAll('.review-send-form').forEach(form =>
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const type = form.querySelector('[name=type]').value;
    const body = form.querySelector('[name=body]').value;
    if (!type) { alert('Selecciona el tipo de recomendación'); return; }
    const data = await postJson('/mensajes/recomendar', { reviewed_id: form.dataset.reviewed, type, body });
    if (data.ok) form.closest('.l69-card').remove();
    else alert(data.error ?? 'Error al enviar');
  })
);

/* ══ NUEVO ANUNCIO ══ */
document.getElementById('announcement-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Publicando…';
  const data = await postJson('/mensajes/anuncio', {
    title:        fd.get('title'),
    directed_to:  fd.getAll('directed_to[]'),
    what_looking: fd.getAll('what_looking[]'),
    event_date:   fd.get('event_date') || null,
    proposal:     fd.get('proposal')   || null,
  });
  btn.disabled = false; btn.textContent = '📢 Publicar anuncio';
  if (data.ok) { e.target.reset(); window.location.reload(); }
  else {
    const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.error ?? 'Error');
    alert(msg);
  }
});

/* ══ CERRAR ANUNCIO ══ */
document.querySelectorAll('.btn-close-ann').forEach(btn =>
  btn.addEventListener('click', async () => {
    if (!confirm('¿Cerrar este anuncio?')) return;
    btn.disabled = true;
    const r = await fetch(`/mensajes/anuncio/${btn.dataset.id}/cerrar`, {
      method: 'PATCH', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
    });
    const data = await r.json();
    if (data.ok) { btn.closest('.l69-ann-card').classList.add('is-expired'); btn.remove(); }
  })
);
</script>
@endpush
