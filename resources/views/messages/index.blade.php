@extends('layouts.app')
@section('title', 'Mensajes · LOBBY69')

@section('content')
<div class="l69-messages-wrap">

  {{-- ══ TABS ══ --}}
  <nav class="l69-msg-tabs">
    @php
      $tabs = [
        'inbox'         => ['icon'=>'💬', 'label'=>'Bandeja'],
        'comments'      => ['icon'=>'🗨️',  'label'=>'Comentarios'],
        'friends'       => ['icon'=>'🤝', 'label'=>'Amistades'],
        'reviews'       => ['icon'=>'⭐', 'label'=>'Recomendaciones'],
        'announcements' => ['icon'=>'📢', 'label'=>'Anuncios'],
      ];
    @endphp
    @foreach($tabs as $key => $meta)
      <a href="{{ route('messages.index', ['tab' => $key]) }}"
         class="l69-msg-tab {{ $tab === $key ? 'active' : '' }}">
        <span class="tab-icon">{{ $meta['icon'] }}</span>
        <span class="tab-label">{{ $meta['label'] }}</span>
        @if($key === 'inbox' && ($unreadTotal ?? 0) > 0)
          <span class="tab-badge">{{ $unreadTotal }}</span>
        @endif
      </a>
    @endforeach
  </nav>

  {{-- ══ CONTENIDO POR TAB ══ --}}
  <div class="l69-msg-body">

    {{-- ─── BANDEJA ─── --}}
    @if($tab === 'inbox')
      <div class="l69-msg-section">
        <h2 class="l69-msg-title">💬 Conversaciones</h2>
        @forelse($conversations as $c)
          @php $isMe = (string)$c->sender_id === (string)Auth::id(); @endphp
          <div class="l69-conv-card" data-partner="{{ $c->partner_id }}">
            <div class="l69-conv-avatar">
              @if($c->avatar_photo_id)
                <img src="{{ route('photos.serve', $c->avatar_photo_id) }}" alt="">
              @else
                <div class="l69-avatar-placeholder">{{ mb_substr($c->display_name ?? '?', 0, 1) }}</div>
              @endif
              @if(($c->unread_count ?? 0) > 0)
                <span class="conv-unread-dot">{{ $c->unread_count }}</span>
              @endif
            </div>
            <div class="l69-conv-info">
              <div class="conv-name">
                {{ $c->nickname ?? $c->display_name }}
                @if($c->verified_profile)<span class="badge-verified" title="Verificado">✓</span>@endif
              </div>
              <div class="conv-preview {{ ($c->unread_count ?? 0) > 0 ? 'unread' : '' }}">
                {{ $isMe ? 'Tú: ' : '' }}{{ Str::limit($c->last_message, 55) }}
              </div>
            </div>
            <div class="conv-meta">
              <span class="conv-time">{{ \Carbon\Carbon::parse($c->last_at)->diffForHumans() }}</span>
              <button class="btn-open-conv"
                      data-partner="{{ $c->partner_id }}"
                      data-name="{{ $c->nickname ?? $c->display_name }}">
                Abrir
              </button>
            </div>
          </div>
        @empty
          <p class="l69-msg-empty">No tienes conversaciones todavía.</p>
        @endforelse
      </div>

      {{-- Modal de conversación --}}
      <div id="conv-modal" class="l69-conv-modal hidden">
        <div class="l69-conv-modal-inner">
          <div class="l69-conv-modal-header">
            <span id="conv-modal-name"></span>
            <button id="conv-modal-close">✕</button>
          </div>
          <div id="conv-modal-messages" class="l69-conv-messages"></div>
          <form id="conv-send-form" class="l69-conv-send">
            <input type="hidden" id="conv-receiver-id">
            <textarea id="conv-body" placeholder="Escribe un mensaje…" rows="2" maxlength="1000"></textarea>
            <button type="submit" class="btn-send">Enviar</button>
          </form>
        </div>
      </div>
    @endif

    {{-- ─── COMENTARIOS ─── --}}
    @if($tab === 'comments')
      <div class="l69-msg-section">
        <h2 class="l69-msg-title">🗨️ Comentarios en fotos</h2>
        @forelse($photoComments as $pc)
          <div class="l69-comment-card">
            <div class="l69-conv-avatar">
              @if($pc->commenter_avatar_id)
                <img src="{{ route('photos.serve', $pc->commenter_avatar_id) }}" alt="">
              @else
                <div class="l69-avatar-placeholder">{{ mb_substr($pc->commenter_name ?? '?', 0, 1) }}</div>
              @endif
            </div>
            <div class="l69-conv-info">
              <div class="conv-name">{{ $pc->commenter_nick ?? $pc->commenter_name }}</div>
              <div class="conv-preview">{{ $pc->body }}</div>
              <div class="conv-sub">En: <em>{{ $pc->caption ?? 'Sin título' }}</em></div>
            </div>
            <div class="conv-meta">
              <span class="conv-time">{{ \Carbon\Carbon::parse($pc->created_at)->diffForHumans() }}</span>
            </div>
          </div>
        @empty
          <p class="l69-msg-empty">Sin comentarios en tus fotos aún.</p>
        @endforelse

        @if($videoComments->isNotEmpty())
          <h2 class="l69-msg-title mt-6">🎬 Comentarios en videos</h2>
          @foreach($videoComments as $vc)
            <div class="l69-comment-card">
              <div class="l69-conv-info">
                <div class="conv-name">{{ $vc->commenter_nick ?? $vc->commenter_name }}</div>
                <div class="conv-preview">{{ $vc->body }}</div>
                <div class="conv-sub">En: <em>{{ $vc->title ?? 'Video' }}</em></div>
              </div>
              <div class="conv-meta">
                <span class="conv-time">{{ \Carbon\Carbon::parse($vc->created_at)->diffForHumans() }}</span>
              </div>
            </div>
          @endforeach
        @endif
      </div>
    @endif

    {{-- ─── AMISTADES ─── --}}
    @if($tab === 'friends')
      <div class="l69-msg-section">

        @if($friendsPending->isNotEmpty())
          <h2 class="l69-msg-title">🔔 Solicitudes recibidas ({{ $friendsPending->count() }})</h2>
          @foreach($friendsPending as $f)
            <div class="l69-friend-card">
              <div class="l69-conv-avatar">
                @if($f->avatar_photo_id)
                  <img src="{{ route('photos.serve', $f->avatar_photo_id) }}" alt="">
                @else
                  <div class="l69-avatar-placeholder">{{ mb_substr($f->display_name ?? '?', 0, 1) }}</div>
                @endif
              </div>
              <div class="l69-conv-info">
                <div class="conv-name">{{ $f->nickname ?? $f->display_name }}
                  @if($f->verified_profile)<span class="badge-verified">✓</span>@endif
                </div>
                <div class="conv-sub">{{ $f->profile_type ?? '' }} · {{ $f->city ?? '' }}</div>
              </div>
              <div class="friend-actions">
                <button class="btn-friend-action btn-accept"
                        data-id="{{ $f->friendship_id }}" data-action="accept">
                  ✓ Aceptar
                </button>
                <button class="btn-friend-action btn-reject"
                        data-id="{{ $f->friendship_id }}" data-action="reject">
                  ✕ Rechazar
                </button>
              </div>
            </div>
          @endforeach
        @endif

        @if($friendsSent->isNotEmpty())
          <h2 class="l69-msg-title mt-6">📤 Solicitudes enviadas ({{ $friendsSent->count() }})</h2>
          @foreach($friendsSent as $f)
            <div class="l69-friend-card">
              <div class="l69-conv-avatar">
                @if($f->avatar_photo_id)
                  <img src="{{ route('photos.serve', $f->avatar_photo_id) }}" alt="">
                @else
                  <div class="l69-avatar-placeholder">{{ mb_substr($f->display_name ?? '?', 0, 1) }}</div>
                @endif
              </div>
              <div class="l69-conv-info">
                <div class="conv-name">{{ $f->nickname ?? $f->display_name }}</div>
                <div class="conv-sub">{{ $f->profile_type ?? '' }} · {{ $f->city ?? '' }}</div>
              </div>
              <div class="conv-meta">
                <span class="conv-time">Enviada {{ \Carbon\Carbon::parse($f->created_at)->diffForHumans() }}</span>
              </div>
            </div>
          @endforeach
        @endif

        @if($friendsAccepted->isNotEmpty())
          <h2 class="l69-msg-title mt-6">✅ Amigos ({{ $friendsAccepted->count() }})</h2>
          <div class="l69-friends-grid">
            @foreach($friendsAccepted as $f)
              <div class="l69-friend-chip">
                @if($f->avatar_photo_id)
                  <img src="{{ route('photos.serve', $f->avatar_photo_id) }}" alt="">
                @else
                  <div class="l69-avatar-placeholder sm">{{ mb_substr($f->display_name ?? '?', 0, 1) }}</div>
                @endif
                <span>{{ $f->nickname ?? $f->display_name }}</span>
                @if($f->verified_profile)<span class="badge-verified">✓</span>@endif
              </div>
            @endforeach
          </div>
        @endif

        @if($friendsPending->isEmpty() && $friendsSent->isEmpty() && $friendsAccepted->isEmpty())
          <p class="l69-msg-empty">No tienes solicitudes ni amigos todavía.</p>
        @endif

      </div>
    @endif

    {{-- ─── RECOMENDACIONES ─── --}}
    @if($tab === 'reviews')
      <div class="l69-msg-section">

        @if($reviewsReceived->isNotEmpty())
          <h2 class="l69-msg-title">⭐ Recomendaciones recibidas</h2>
          @foreach($reviewsReceived as $r)
            <div class="l69-review-card {{ $r->type === 'positive' ? 'positive' : 'negative' }}">
              <div class="l69-conv-avatar">
                @if($r->avatar_photo_id)
                  <img src="{{ route('photos.serve', $r->avatar_photo_id) }}" alt="">
                @else
                  <div class="l69-avatar-placeholder">{{ mb_substr($r->reviewer_name ?? '?', 0, 1) }}</div>
                @endif
              </div>
              <div class="l69-conv-info">
                <div class="conv-name">{{ $r->reviewer_nick ?? $r->reviewer_name }}</div>
                @if($r->body)<div class="conv-preview">{{ $r->body }}</div>@endif
              </div>
              <div class="conv-meta">
                <span class="review-type-badge {{ $r->type }}">
                  {{ $r->type === 'positive' ? '👍 Positiva' : '👎 Negativa' }}
                </span>
                <span class="conv-time">{{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</span>
              </div>
            </div>
          @endforeach
        @endif

        @if($reviewsGiven->isNotEmpty())
          <h2 class="l69-msg-title mt-6">📝 Recomendaciones que dejé</h2>
          @foreach($reviewsGiven as $r)
            <div class="l69-review-card {{ $r->type === 'positive' ? 'positive' : 'negative' }}">
              <div class="l69-conv-info">
                <div class="conv-name">Para: {{ $r->reviewed_nick ?? $r->reviewed_name }}</div>
                @if($r->body)<div class="conv-preview">{{ $r->body }}</div>@endif
              </div>
              <div class="conv-meta">
                <span class="review-type-badge {{ $r->type }}">
                  {{ $r->type === 'positive' ? '👍 Positiva' : '👎 Negativa' }}
                </span>
                <span class="conv-time">{{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}</span>
              </div>
            </div>
          @endforeach
        @endif

        @if($canReview->isNotEmpty())
          <h2 class="l69-msg-title mt-6">✍️ Puedes recomendar</h2>
          @foreach($canReview as $fr)
            <div class="l69-review-form-card">
              <div class="l69-conv-avatar">
                @if($fr->avatar_photo_id)
                  <img src="{{ route('photos.serve', $fr->avatar_photo_id) }}" alt="">
                @else
                  <div class="l69-avatar-placeholder">{{ mb_substr($fr->display_name ?? '?', 0, 1) }}</div>
                @endif
              </div>
              <div class="l69-conv-info">
                <div class="conv-name">{{ $fr->nickname ?? $fr->display_name }}</div>
              </div>
              <form class="review-send-form" data-reviewed="{{ $fr->user_id }}">
                <select name="type" class="review-type-select" required>
                  <option value="">Tipo…</option>
                  <option value="positive">👍 Positiva</option>
                  <option value="negative">👎 Negativa</option>
                </select>
                <textarea name="body" placeholder="Comentario opcional…" maxlength="500" rows="2"></textarea>
                <button type="submit" class="btn-send-review">Enviar</button>
              </form>
            </div>
          @endforeach
        @endif

        @if($reviewsReceived->isEmpty() && $reviewsGiven->isEmpty() && $canReview->isEmpty())
          <p class="l69-msg-empty">Sin recomendaciones aún. Agrega amigos primero.</p>
        @endif

      </div>
    @endif

    {{-- ─── ANUNCIOS ─── --}}
    @if($tab === 'announcements')
      <div class="l69-msg-section">

        {{-- Formulario nuevo anuncio --}}
        <div class="l69-announcement-form-wrap">
          <h2 class="l69-msg-title">📢 Publicar un anuncio</h2>
          <form id="announcement-form" class="l69-ann-form">
            @csrf

            <div class="l69-ann-field">
              <label>Título <span class="req">*</span></label>
              <input type="text" name="title" maxlength="120" placeholder="Ej: Buscamos pareja para intercambio…" required>
            </div>

            <div class="l69-ann-field">
              <label>Dirigido a</label>
              <div class="l69-checkgroup">
                <label><input type="checkbox" name="directed_to[]" value="singles"> Singles</label>
                <label><input type="checkbox" name="directed_to[]" value="parejas"> Parejas</label>
                <label><input type="checkbox" name="directed_to[]" value="unicornio"> Chicas unicornio</label>
              </div>
            </div>

            <div class="l69-ann-field">
              <label>¿Qué busco?</label>
              <div class="l69-checkgroup two-cols">
                <label><input type="checkbox" name="what_looking[]" value="intercambios"> Intercambios</label>
                <label><input type="checkbox" name="what_looking[]" value="cuckold"> Cuckold</label>
                <label><input type="checkbox" name="what_looking[]" value="fiesta"> Fiesta / Antro</label>
                <label><input type="checkbox" name="what_looking[]" value="trio_mhm"> Trío MHM</label>
                <label><input type="checkbox" name="what_looking[]" value="trio_hmh"> Trío HMH</label>
                <label><input type="checkbox" name="what_looking[]" value="gangbang"> Gang bang</label>
                <label><input type="checkbox" name="what_looking[]" value="cita_soft"> Cita Soft</label>
                <label><input type="checkbox" name="what_looking[]" value="reunion_swinger"> Reunión Swinger</label>
                <label><input type="checkbox" name="what_looking[]" value="encuentro_casual"> Encuentro casual</label>
                <label><input type="checkbox" name="what_looking[]" value="voyeurismo"> Voyeurismo</label>
                <label><input type="checkbox" name="what_looking[]" value="jugar"> Jugar / Divertirnos</label>
                <label><input type="checkbox" name="what_looking[]" value="conocernos"> Conocernos y después ver</label>
              </div>
            </div>

            <div class="l69-ann-field">
              <label>Descripción</label>
              <textarea name="proposal" maxlength="600" rows="3"
                        placeholder="Cuéntanos más sobre lo que buscas…"></textarea>
            </div>

            <div class="l69-ann-field">
              <label>Fecha del encuentro <span class="ann-hint">(máx. 4 días a partir de hoy)</span></label>
              <input type="date" name="event_date"
                     min="{{ now()->addDay()->toDateString() }}"
                     max="{{ now()->addDays(4)->toDateString() }}">
            </div>

            <button type="submit" class="btn-publish-ann">📢 Publicar anuncio</button>
          </form>
        </div>

        {{-- Mis anuncios --}}
        @if($myAnnouncements->isNotEmpty())
          <h2 class="l69-msg-title mt-8">📋 Mis anuncios</h2>
          @foreach($myAnnouncements as $a)
            <div class="l69-ann-card {{ $a->is_expired || $a->status === 'closed' ? 'expired' : '' }}">
              <div class="ann-card-header">
                <strong>{{ $a->title }}</strong>
                <div class="ann-badges">
                  @if($a->status === 'closed')
                    <span class="ann-badge closed">Cerrado</span>
                  @elseif($a->is_expired)
                    <span class="ann-badge expired">Expirado</span>
                  @else
                    <span class="ann-badge active">Activo</span>
                  @endif
                </div>
              </div>
              @if(!empty($a->directed_to))
                <div class="ann-tags">
                  <span class="ann-tag-label">Para:</span>
                  @foreach($a->directed_to as $t)<span class="ann-tag">{{ $t }}</span>@endforeach
                </div>
              @endif
              @if(!empty($a->what_looking))
                <div class="ann-tags">
                  @foreach($a->what_looking as $t)<span class="ann-tag alt">{{ str_replace('_', ' ', $t) }}</span>@endforeach
                </div>
              @endif
              @if($a->proposal)
                <p class="ann-proposal">{{ $a->proposal }}</p>
              @endif
              <div class="ann-footer">
                <span class="conv-time">{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
                @if($a->status === 'active' && !$a->is_expired)
                  <button class="btn-close-ann" data-id="{{ $a->id }}">Cerrar anuncio</button>
                @endif
              </div>
            </div>
          @endforeach
        @endif

        {{-- Anuncios de otros --}}
        <h2 class="l69-msg-title mt-8">🔍 Anuncios recientes</h2>
        @forelse($announcements as $a)
          <div class="l69-ann-card {{ $a->is_expired ? 'expired' : '' }}">
            <div class="ann-card-header">
              <div class="l69-conv-avatar sm">
                @if($a->avatar_photo_id)
                  <img src="{{ route('photos.serve', $a->avatar_photo_id) }}" alt="">
                @else
                  <div class="l69-avatar-placeholder sm">{{ mb_substr($a->display_name ?? '?', 0, 1) }}</div>
                @endif
              </div>
              <div>
                <div class="conv-name">
                  {{ $a->nickname ?? $a->display_name }}
                  @if($a->verified_profile)<span class="badge-verified">✓</span>@endif
                  <span class="ann-profile-type">· {{ $a->profile_type ?? '' }}</span>
                </div>
                <strong>{{ $a->title }}</strong>
              </div>
              @if($a->is_expired)
                <span class="ann-badge expired ml-auto">Expirado</span>
              @endif
            </div>
            @if(!empty($a->directed_to))
              <div class="ann-tags">
                <span class="ann-tag-label">Para:</span>
                @foreach($a->directed_to as $t)<span class="ann-tag">{{ $t }}</span>@endforeach
              </div>
            @endif
            @if(!empty($a->what_looking))
              <div class="ann-tags">
                @foreach($a->what_looking as $t)<span class="ann-tag alt">{{ str_replace('_', ' ', $t) }}</span>@endforeach
              </div>
            @endif
            @if($a->proposal)
              <p class="ann-proposal">{{ $a->proposal }}</p>
            @endif
            <div class="ann-footer">
              <span class="conv-time">{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
              @if($a->city)<span class="ann-city">📍 {{ $a->city }}</span>@endif
              @if($a->event_date)
                <span class="ann-date">📅 {{ \Carbon\Carbon::parse($a->event_date)->format('d M Y') }}</span>
              @endif
            </div>
          </div>
        @empty
          <p class="l69-msg-empty">No hay anuncios activos en este momento.</p>
        @endforelse

      </div>
    @endif

  </div>{{-- /.l69-msg-body --}}
</div>{{-- /.l69-messages-wrap --}}
@endsection

@push('styles')
<style>
/* ══ LAYOUT ══ */
.l69-messages-wrap { max-width: 820px; margin: 0 auto; padding: 1.5rem 1rem 4rem; }

/* ══ TABS ══ */
.l69-msg-tabs {
  display: flex; gap: .4rem; flex-wrap: wrap;
  border-bottom: 2px solid var(--border-color, #2a2a2a);
  margin-bottom: 1.5rem;
}
.l69-msg-tab {
  display: flex; align-items: center; gap: .4rem;
  padding: .55rem 1rem; border-radius: 8px 8px 0 0;
  text-decoration: none; font-size: .88rem; font-weight: 500;
  color: var(--text-muted, #aaa);
  transition: background .18s, color .18s;
  position: relative;
}
.l69-msg-tab:hover  { background: rgba(255,255,255,.06); color: #fff; }
.l69-msg-tab.active { background: var(--accent, #c0392b); color: #fff; }
.tab-badge {
  background: #e74c3c; color: #fff; font-size: .7rem;
  border-radius: 99px; padding: 0 .4rem; line-height: 1.4;
}

/* ══ BODY ══ */
.l69-msg-body { animation: fadeIn .2s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: none; } }

.l69-msg-title { font-size: 1rem; font-weight: 700; margin: 0 0 .8rem; color: var(--text-main, #eee); }
.l69-msg-empty { color: var(--text-muted, #888); font-size: .9rem; padding: 1rem 0; }
.mt-6 { margin-top: 1.5rem; }
.mt-8 { margin-top: 2rem; }

/* ══ TARJETA BASE ══ */
.l69-conv-card,
.l69-comment-card,
.l69-friend-card,
.l69-review-card,
.l69-review-form-card {
  display: flex; align-items: flex-start; gap: .85rem;
  background: var(--card-bg, #1a1a1a);
  border: 1px solid var(--border-color, #2a2a2a);
  border-radius: 10px; padding: .85rem 1rem;
  margin-bottom: .6rem;
  transition: border-color .18s;
}
.l69-conv-card:hover { border-color: var(--accent, #c0392b); }

/* ══ AVATARES ══ */
.l69-conv-avatar { position: relative; flex-shrink: 0; }
.l69-conv-avatar img,
.l69-conv-avatar.sm img {
  width: 46px; height: 46px; border-radius: 50%;
  object-fit: cover; border: 2px solid var(--border-color, #333);
}
.l69-conv-avatar.sm img { width: 36px; height: 36px; }
.l69-avatar-placeholder {
  width: 46px; height: 46px; border-radius: 50%;
  background: var(--accent, #c0392b); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem; font-weight: 700; flex-shrink: 0;
}
.l69-avatar-placeholder.sm { width: 36px; height: 36px; font-size: .9rem; }
.conv-unread-dot {
  position: absolute; top: -3px; right: -3px;
  background: #e74c3c; color: #fff;
  font-size: .65rem; font-weight: 700;
  border-radius: 99px; padding: 1px 5px;
  border: 2px solid var(--card-bg, #1a1a1a);
}

/* ══ INFO ══ */
.l69-conv-info  { flex: 1; min-width: 0; }
.conv-name      { font-weight: 600; font-size: .92rem; color: var(--text-main, #eee); margin-bottom: .2rem; }
.conv-preview   { font-size: .83rem; color: var(--text-muted, #999); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.conv-preview.unread { color: #fff; font-weight: 600; }
.conv-sub       { font-size: .78rem; color: var(--text-muted, #777); margin-top: .15rem; }
.badge-verified { color: #27ae60; font-size: .75rem; margin-left: .25rem; }

/* ══ META ══ */
.conv-meta  { display: flex; flex-direction: column; align-items: flex-end; gap: .35rem; flex-shrink: 0; }
.conv-time  { font-size: .75rem; color: var(--text-muted, #777); }

/* ══ BOTONES ══ */
.btn-open-conv, .btn-send, .btn-send-review, .btn-publish-ann {
  background: var(--accent, #c0392b); color: #fff;
  border: none; border-radius: 6px; padding: .35rem .75rem;
  font-size: .8rem; font-weight: 600; cursor: pointer;
  transition: opacity .18s;
}
.btn-open-conv:hover, .btn-send:hover, .btn-send-review:hover, .btn-publish-ann:hover { opacity: .85; }
.btn-publish-ann { width: 100%; padding: .65rem; font-size: .92rem; margin-top: .5rem; }

/* ══ AMISTADES ══ */
.friend-actions { display: flex; gap: .4rem; flex-direction: column; }
.btn-friend-action {
  border: none; border-radius: 6px; padding: .3rem .7rem;
  font-size: .78rem; font-weight: 600; cursor: pointer; transition: opacity .18s;
}
.btn-accept { background: #27ae60; color: #fff; }
.btn-reject { background: #555; color: #eee; }
.btn-accept:hover, .btn-reject:hover { opacity: .8; }
.l69-friends-grid { display: flex; flex-wrap: wrap; gap: .5rem; }
.l69-friend-chip {
  display: flex; align-items: center; gap: .4rem;
  background: var(--card-bg, #1a1a1a);
  border: 1px solid var(--border-color, #2a2a2a);
  border-radius: 99px; padding: .3rem .75rem .3rem .3rem;
  font-size: .82rem; color: var(--text-main, #eee);
}
.l69-friend-chip img { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; }

/* ══ RESEÑAS ══ */
.l69-review-card.positive { border-left: 3px solid #27ae60; }
.l69-review-card.negative { border-left: 3px solid #e74c3c; }
.review-type-badge {
  font-size: .75rem; font-weight: 700; border-radius: 5px; padding: .15rem .45rem;
}
.review-type-badge.positive { background: rgba(39,174,96,.2);  color: #2ecc71; }
.review-type-badge.negative { background: rgba(231,76,60,.2);  color: #e74c3c; }
.review-send-form  { display: flex; flex-direction: column; gap: .4rem; flex: 1; }
.review-type-select {
  background: var(--input-bg, #111); border: 1px solid var(--border-color, #333);
  color: var(--text-main, #eee); border-radius: 6px; padding: .35rem .5rem; font-size: .82rem;
}
.review-send-form textarea {
  background: var(--input-bg, #111); border: 1px solid var(--border-color, #333);
  color: var(--text-main, #eee); border-radius: 6px;
  padding: .4rem .6rem; font-size: .82rem; resize: vertical;
}

/* ══ ANUNCIOS ══ */
.l69-announcement-form-wrap {
  background: var(--card-bg, #1a1a1a);
  border: 1px solid var(--border-color, #2a2a2a);
  border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;
}
.l69-ann-form { display: flex; flex-direction: column; gap: .9rem; }
.l69-ann-field label { display: block; font-size: .82rem; font-weight: 600; color: var(--text-muted, #aaa); margin-bottom: .3rem; }
.l69-ann-field input[type="text"],
.l69-ann-field input[type="date"],
.l69-ann-field textarea {
  width: 100%; background: var(--input-bg, #111);
  border: 1px solid var(--border-color, #333);
  color: var(--text-main, #eee); border-radius: 8px;
  padding: .5rem .75rem; font-size: .88rem;
  box-sizing: border-box;
}
.l69-checkgroup { display: flex; flex-wrap: wrap; gap: .5rem .9rem; }
.l69-checkgroup.two-cols { column-gap: 1.5rem; }
.l69-checkgroup label {
  display: flex; align-items: center; gap: .35rem;
  font-size: .83rem; color: var(--text-main, #ddd); cursor: pointer;
}
.l69-checkgroup input[type="checkbox"] { accent-color: var(--accent, #c0392b); width: 15px; height: 15px; }
.ann-hint { font-weight: 400; font-size: .75rem; color: var(--text-muted, #888); }
.req { color: #e74c3c; }

.l69-ann-card {
  background: var(--card-bg, #1a1a1a);
  border: 1px solid var(--border-color, #2a2a2a);
  border-radius: 10px; padding: .9rem 1rem; margin-bottom: .7rem;
  transition: border-color .18s;
}
.l69-ann-card:hover:not(.expired) { border-color: var(--accent, #c0392b); }
.l69-ann-card.expired { opacity: .55; filter: grayscale(.4); }
.ann-card-header {
  display: flex; align-items: center; gap: .65rem;
  margin-bottom: .5rem; flex-wrap: wrap;
}
.ann-card-header strong { font-size: .92rem; color: var(--text-main, #eee); }
.ann-badges { display: flex; gap: .35rem; margin-left: auto; }
.ann-badge {
  font-size: .7rem; font-weight: 700; padding: .15rem .5rem;
  border-radius: 99px;
}
.ann-badge.active  { background: rgba(39,174,96,.2);  color: #2ecc71; }
.ann-badge.expired { background: rgba(149,165,166,.15); color: #95a5a6; }
.ann-badge.closed  { background: rgba(127,140,141,.15); color: #7f8c8d; }
.ann-tags { display: flex; flex-wrap: wrap; gap: .3rem; margin-bottom: .4rem; align-items: center; }
.ann-tag-label { font-size: .73rem; color: var(--text-muted, #888); margin-right: .1rem; }
.ann-tag {
  background: rgba(192,57,43,.18); color: #e87b6e;
  font-size: .72rem; border-radius: 99px; padding: .1rem .5rem;
}
.ann-tag.alt {
  background: rgba(52,152,219,.18); color: #7ec8e3;
}
.ann-proposal { font-size: .84rem; color: var(--text-muted, #bbb); margin: .35rem 0; }
.ann-footer {
  display: flex; align-items: center; gap: .75rem;
  flex-wrap: wrap; margin-top: .4rem; font-size: .77rem;
}
.ann-city, .ann-date { color: var(--text-muted, #888); }
.ann-profile-type { color: var(--text-muted, #888); font-size: .8rem; }
.btn-close-ann {
  background: transparent; border: 1px solid #555;
  color: #999; border-radius: 6px; padding: .2rem .6rem;
  font-size: .75rem; cursor: pointer; margin-left: auto;
  transition: border-color .18s, color .18s;
}
.btn-close-ann:hover { border-color: #e74c3c; color: #e74c3c; }
.ml-auto { margin-left: auto; }

/* ══ MODAL CONVERSACIÓN ══ */
.l69-conv-modal {
  position: fixed; inset: 0; background: rgba(0,0,0,.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
}
.l69-conv-modal.hidden { display: none; }
.l69-conv-modal-inner {
  background: var(--card-bg, #1a1a1a);
  border: 1px solid var(--border-color, #333);
  border-radius: 14px; width: min(540px, 96vw);
  display: flex; flex-direction: column; overflow: hidden;
  max-height: 85vh;
}
.l69-conv-modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: .75rem 1rem; border-bottom: 1px solid var(--border-color, #2a2a2a);
  font-weight: 700; color: var(--text-main, #eee);
}
#conv-modal-close {
  background: transparent; border: none; color: #888;
  font-size: 1.1rem; cursor: pointer; padding: .15rem .4rem; border-radius: 5px;
}
#conv-modal-close:hover { color: #fff; background: rgba(255,255,255,.1); }
.l69-conv-messages {
  flex: 1; overflow-y: auto; padding: 1rem;
  display: flex; flex-direction: column; gap: .5rem;
}
.msg-bubble {
  max-width: 72%; padding: .45rem .8rem;
  border-radius: 10px; font-size: .86rem; line-height: 1.45;
  word-break: break-word;
}
.msg-bubble.mine    { background: var(--accent, #c0392b); color: #fff; align-self: flex-end; border-bottom-right-radius: 2px; }
.msg-bubble.theirs  { background: var(--border-color, #2a2a2a); color: var(--text-main, #eee); align-self: flex-start; border-bottom-left-radius: 2px; }
.msg-time { font-size: .7rem; opacity: .6; display: block; margin-top: .15rem; }
.l69-conv-send {
  display: flex; gap: .5rem; padding: .75rem 1rem;
  border-top: 1px solid var(--border-color, #2a2a2a);
}
.l69-conv-send textarea {
  flex: 1; resize: none;
  background: var(--input-bg, #111); border: 1px solid var(--border-color, #333);
  color: var(--text-main, #eee); border-radius: 8px;
  padding: .45rem .65rem; font-size: .86rem;
}
</style>
@endpush

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const ME   = '{{ Auth::id() }}';

/* ── Helpers ── */
async function postJson(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body: JSON.stringify(data),
  });
  return res.json();
}

/* ══ MODAL CONVERSACIÓN ══ */
const modal      = document.getElementById('conv-modal');
const modalName  = document.getElementById('conv-modal-name');
const modalMsgs  = document.getElementById('conv-modal-messages');
const modalClose = document.getElementById('conv-modal-close');
const sendForm   = document.getElementById('conv-send-form');
const sendBody   = document.getElementById('conv-body');
const receiverId = document.getElementById('conv-receiver-id');

function renderMessages(messages) {
  modalMsgs.innerHTML = '';
  messages.forEach(m => {
    const mine = String(m.sender_id) === ME;
    const div  = document.createElement('div');
    div.className = `msg-bubble ${mine ? 'mine' : 'theirs'}`;
    div.innerHTML = `${escHtml(m.body)}<span class="msg-time">${formatTime(m.created_at)}</span>`;
    modalMsgs.appendChild(div);
  });
  modalMsgs.scrollTop = modalMsgs.scrollHeight;
}

async function openConversation(partnerId, name) {
  modalName.textContent = name;
  receiverId.value      = partnerId;
  modal.classList.remove('hidden');
  modalMsgs.innerHTML   = '<p style="color:#888;font-size:.8rem;text-align:center">Cargando…</p>';
  const data = await fetch(`/mensajes/conversacion/${partnerId}`, { headers: { 'Accept': 'application/json' } }).then(r => r.json());
  renderMessages(data.messages ?? []);
}

document.querySelectorAll('.btn-open-conv').forEach(btn => {
  btn.addEventListener('click', () => openConversation(btn.dataset.partner, btn.dataset.name));
});
modalClose?.addEventListener('click', () => modal.classList.add('hidden'));
modal?.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

sendForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const body = sendBody.value.trim();
  if (!body) return;
  const data = await postJson('/mensajes/enviar', { receiver_id: receiverId.value, body });
  if (data.ok) {
    sendBody.value = '';
    await openConversation(receiverId.value, modalName.textContent);
  }
});

/* ══ AMISTADES ══ */
document.querySelectorAll('.btn-friend-action').forEach(btn => {
  btn.addEventListener('click', async () => {
    btn.disabled = true;
    const data = await postJson(`/mensajes/amistad/${btn.dataset.id}`, { action: btn.dataset.action });
    if (data.ok) btn.closest('.l69-friend-card').remove();
    else btn.disabled = false;
  });
});

/* ══ RECOMENDACIONES ══ */
document.querySelectorAll('.review-send-form').forEach(form => {
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const type = form.querySelector('[name=type]').value;
    const body = form.querySelector('[name=body]').value;
    if (!type) return alert('Selecciona el tipo de recomendación');
    const data = await postJson('/mensajes/recomendar', {
      reviewed_id: form.dataset.reviewed, type, body
    });
    if (data.ok) form.closest('.l69-review-form-card').remove();
    else alert(data.error ?? 'Error al enviar');
  });
});

/* ══ ANUNCIO ══ */
document.getElementById('announcement-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const fd   = new FormData(e.target);
  const body = {
    title:        fd.get('title'),
    directed_to:  fd.getAll('directed_to[]'),
    what_looking: fd.getAll('what_looking[]'),
    event_date:   fd.get('event_date') || null,
    proposal:     fd.get('proposal') || null,
  };
  const data = await postJson('/mensajes/anuncio', body);
  if (data.ok) {
    e.target.reset();
    window.location.reload();
  } else {
    const msg = data.errors ? Object.values(data.errors).flat().join('\n') : (data.error ?? 'Error');
    alert(msg);
  }
});

/* ══ CERRAR ANUNCIO ══ */
document.querySelectorAll('.btn-close-ann').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('¿Cerrar este anuncio?')) return;
    btn.disabled = true;
    const res = await fetch(`/mensajes/anuncio/${btn.dataset.id}/cerrar`, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    const data = await res.json();
    if (data.ok) btn.closest('.l69-ann-card').classList.add('expired');
  });
});

/* ── Utils ── */
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatTime(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
}
</script>
@endpush
