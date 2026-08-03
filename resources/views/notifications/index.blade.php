@extends('layouts.app')
@section('title', 'Notificaciones — LOBBY69')

@push('sidebar-left')
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title"><i class="fas fa-bell"></i> Actividad</div>
    <ul class="l69-sidebar-nav">
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('notifications.index') }}"
               class="{{ request('cat', 'all') === 'all' ? 'is-active' : '' }}">
                <i class="fas fa-layer-group"></i> Todas
                @if(($totalUnread ?? 0) > 0)
                    <span class="l69-nav-badge">{{ $totalUnread }}</span>
                @endif
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('notifications.index', ['cat' => 'messages']) }}"
               class="{{ request('cat') === 'messages' ? 'is-active' : '' }}">
                <i class="fas fa-envelope"></i> Mensajes
                @if(($unreadByType['messages'] ?? 0) > 0)
                    <span class="l69-nav-badge">{{ $unreadByType['messages'] }}</span>
                @endif
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('notifications.index', ['cat' => 'social']) }}"
               class="{{ request('cat') === 'social' ? 'is-active' : '' }}">
                <i class="fas fa-users"></i> Amigos
                @if(($unreadByType['social'] ?? 0) > 0)
                    <span class="l69-nav-badge">{{ $unreadByType['social'] }}</span>
                @endif
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('notifications.index', ['cat' => 'activity']) }}"
               class="{{ request('cat') === 'activity' ? 'is-active' : '' }}">
                <i class="fas fa-heart"></i> En tus fotos
                @if(($unreadByType['activity'] ?? 0) > 0)
                    <span class="l69-nav-badge">{{ $unreadByType['activity'] }}</span>
                @endif
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('notifications.index', ['cat' => 'comments']) }}"
               class="{{ request('cat') === 'comments' ? 'is-active' : '' }}">
                <i class="fas fa-comments"></i> Comentarios
                @if(($unreadByType['comments'] ?? 0) > 0)
                    <span class="l69-nav-badge">{{ $unreadByType['comments'] }}</span>
                @endif
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('notifications.index', ['cat' => 'reviews']) }}"
               class="{{ request('cat') === 'reviews' ? 'is-active' : '' }}">
                <i class="fas fa-star"></i> Recomendaciones
            </a>
        </li>
    </ul>
</div>
@endpush

@push('sidebar-right')
{{-- Resumen de actividad contextual — va PRIMERO --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-chart-pie"></i> Resumen
    </div>
    <div style="display:flex;flex-direction:column;gap:.3rem;">

        <a href="{{ route('notifications.index', ['cat' => 'messages']) }}"
           class="l69-notif-summary-row">
            <div class="l69-notif-summary-icon" style="background:rgba(59,130,246,.12);">
                <i class="fas fa-envelope" style="color:#3b82f6;"></i>
            </div>
            <span class="l69-notif-summary-label">Mensajes</span>
            @if(($unreadByType['messages'] ?? 0) > 0)
                <span class="l69-notif-summary-badge" style="background:#3b82f6;">{{ $unreadByType['messages'] }}</span>
            @else
                <span class="l69-notif-summary-zero">al día</span>
            @endif
        </a>

        <a href="{{ route('notifications.index', ['cat' => 'social']) }}"
           class="l69-notif-summary-row">
            <div class="l69-notif-summary-icon" style="background:rgba(245,158,11,.12);">
                <i class="fas fa-user-friends" style="color:#f59e0b;"></i>
            </div>
            <span class="l69-notif-summary-label">Solicitudes</span>
            @if(($unreadByType['social'] ?? 0) > 0)
                <span class="l69-notif-summary-badge" style="background:#f59e0b;">{{ $unreadByType['social'] }}</span>
            @else
                <span class="l69-notif-summary-zero">al día</span>
            @endif
        </a>

        <a href="{{ route('notifications.index', ['cat' => 'activity']) }}"
           class="l69-notif-summary-row">
            <div class="l69-notif-summary-icon" style="background:rgba(224,86,160,.12);">
                <i class="fas fa-heart" style="color:#e056a0;"></i>
            </div>
            <span class="l69-notif-summary-label">Likes</span>
            @if(($unreadByType['activity'] ?? 0) > 0)
                <span class="l69-notif-summary-badge" style="background:#e056a0;">{{ $unreadByType['activity'] }}</span>
            @else
                <span class="l69-notif-summary-zero">al día</span>
            @endif
        </a>

        <a href="{{ route('notifications.index', ['cat' => 'comments']) }}"
           class="l69-notif-summary-row">
            <div class="l69-notif-summary-icon" style="background:rgba(139,92,246,.12);">
                <i class="fas fa-comments" style="color:#8b5cf6;"></i>
            </div>
            <span class="l69-notif-summary-label">Comentarios</span>
            @if(($unreadByType['comments'] ?? 0) > 0)
                <span class="l69-notif-summary-badge" style="background:#8b5cf6;">{{ $unreadByType['comments'] }}</span>
            @else
                <span class="l69-notif-summary-zero">al día</span>
            @endif
        </a>

        <a href="{{ route('notifications.index', ['cat' => 'reviews']) }}"
           class="l69-notif-summary-row">
            <div class="l69-notif-summary-icon" style="background:rgba(34,197,94,.12);">
                <i class="fas fa-star" style="color:#22c55e;"></i>
            </div>
            <span class="l69-notif-summary-label">Recomendaciones</span>
            <span class="l69-notif-summary-zero">ver todas</span>
        </a>

    </div>
</div>

{{-- Verificación pendiente si aplica --}}
@php $rUser = auth()->user(); @endphp
@if(!(($rUser->verification_status ?? '') === 'approved'))
<div class="l69-sidebar-card" style="margin-top:.75rem;border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.05);">
    <div style="display:flex;align-items:flex-start;gap:.6rem;">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-top:.1rem;flex-shrink:0;"></i>
        <div>
            <p style="font-size:.8rem;font-weight:600;color:#fbbf24;margin:0 0 .3rem;">Verificación pendiente</p>
            <p style="font-size:.75rem;color:var(--theme-muted);margin:0 0 .65rem;">Verifica tu identidad para acceder a todos los perfiles.</p>
            <a href="{{ route('verification.show') }}" style="font-size:.78rem;color:#f59e0b;font-weight:600;text-decoration:none;">
                Verificar ahora →
            </a>
        </div>
    </div>
</div>
@endif
@endpush



@push('styles')
<style>
/* ══ NOTIFICACIONES ══ */
.l69-notif-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 1rem;
    margin-bottom: 1.25rem; flex-wrap: wrap;
}
.l69-notif-title {
    font-size: 1.4rem; font-weight: 800;
    color: var(--theme-text, #1a1523);
    margin: 0 0 .2rem; display: flex;
    align-items: center; gap: .5rem;
}
.l69-notif-sub { color: var(--theme-muted); margin: 0; font-size: .85rem; }

.l69-notif-section {
    background: var(--theme-card, #fff);
    border: 1px solid var(--theme-border, rgba(0,0,0,.08));
    border-radius: 14px; overflow: hidden;
    margin-bottom: 1rem;
}
.l69-notif-section__header {
    display: flex; align-items: center; gap: .5rem;
    padding: .65rem 1rem;
    background: rgba(180,60,120,.04);
    border-bottom: 1px solid var(--theme-border, rgba(0,0,0,.08));
    font-size: .8rem; font-weight: 700;
    color: var(--theme-text);
}
.l69-section-count {
    margin-left: auto;
    background: rgba(180,60,120,.12);
    color: #b43c78; font-size: .7rem;
    font-weight: 700; border-radius: 99px;
    padding: .1rem .5rem;
}

.l69-notif-row {
    display: flex; align-items: flex-start; gap: .85rem;
    padding: .85rem 1rem;
    border-bottom: 1px solid var(--theme-border, rgba(0,0,0,.06));
    transition: background .15s;
}
.l69-notif-row:last-child { border-bottom: none; }
.l69-notif-row:hover { background: rgba(180,60,120,.04); }
.l69-notif-row.is-unread { background: rgba(180,60,120,.07); }

.l69-notif-icon {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .88rem;
}
.l69-notif-body { flex: 1; min-width: 0; }
.l69-notif-text { font-size: .87rem; color: var(--theme-text); line-height: 1.4; }
.l69-notif-preview {
    font-size: .8rem; color: var(--theme-muted);
    margin-top: .2rem; font-style: italic;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.l69-notif-time {
    font-size: .73rem; color: var(--theme-muted);
    margin-top: .3rem; display: flex;
    align-items: center; gap: .35rem; flex-wrap: wrap;
}
.l69-notif-ref {
    font-size: .75rem; background: rgba(180,60,120,.1);
    color: #b43c78; border-radius: 5px;
    padding: .1rem .4rem; margin-left: .25rem;
}
.l69-notif-link {
    color: #b43c78; text-decoration: none; font-weight: 600;
}
.l69-notif-link:hover { text-decoration: underline; }
.l69-badge-positive { background: rgba(34,197,94,.1); color: #16a34a; }
.l69-badge-negative { background: rgba(239,68,68,.1);  color: #dc2626; }

.l69-unread-dot {
    width: 9px; height: 9px; border-radius: 50%;
    background: #e056a0; flex-shrink: 0; margin-top: .45rem;
}
.l69-notif-empty {
    text-align: center; padding: 3rem 2rem;
    color: var(--theme-muted);
}
.l69-notif-empty i { font-size: 2.5rem; opacity: .3; margin-bottom: .75rem; display: block; }
.l69-notif-empty p { margin: 0; font-size: .88rem; }
.l69-nav-badge {
    margin-left: auto; background: #e74c3c;
    color: #fff; font-size: .65rem; font-weight: 700;
    border-radius: 99px; padding: 1px 6px;
}

/* ══ SIDEBAR RESUMEN ══ */
.l69-notif-summary-row {
    display: flex; align-items: center; gap: .6rem;
    padding: .45rem .5rem; border-radius: 8px;
    text-decoration: none;
    transition: background .15s;
}
.l69-notif-summary-row:hover { background: rgba(180,60,120,.06); }
.l69-notif-summary-icon {
    width: 30px; height: 30px; border-radius: 8px;
    display: flex; align-items: center;
    justify-content: center; flex-shrink: 0;
    font-size: .78rem;
}
.l69-notif-summary-label {
    flex: 1; font-size: .8rem;
    color: var(--theme-text); font-weight: 500;
}
.l69-notif-summary-badge {
    color: #fff; font-size: .68rem; font-weight: 700;
    border-radius: 99px; padding: .1rem .5rem;
    min-width: 20px; text-align: center;
}
.l69-notif-summary-zero {
    font-size: .7rem; color: var(--theme-muted);
}

</style>
@endpush

@section('content')
@php
    $cat        = request('cat', 'all');
    $catLabels  = [
        'all'      => 'Toda la actividad',
        'messages' => 'Mensajes recibidos',
        'social'   => 'Solicitudes y amigos',
        'activity' => 'Likes en tus fotos',
        'comments' => 'Comentarios recibidos',
        'reviews'  => 'Recomendaciones',
    ];
    $catIcons   = [
        'all'      => 'fa-layer-group',
        'messages' => 'fa-envelope',
        'social'   => 'fa-users',
        'activity' => 'fa-heart',
        'comments' => 'fa-comments',
        'reviews'  => 'fa-star',
    ];
@endphp

<div class="l69-notif-header">
    <div>
        <h1 class="l69-notif-title">
            <i class="fas {{ $catIcons[$cat] ?? 'fa-bell' }}"></i>
            {{ $catLabels[$cat] ?? 'Notificaciones' }}
        </h1>
        <p class="l69-notif-sub">
            @if($notifs->isEmpty())
                Sin actividad nueva
            @else
                {{ $notifs->count() }} elemento(s)
                @if(($totalUnread ?? 0) > 0)
                    · <span style="color:#e056a0;font-weight:600;">{{ $totalUnread }} sin leer</span>
                @endif
            @endif
        </p>
    </div>
    @if(($totalUnread ?? 0) > 0)
    <form method="POST" action="{{ route('notifications.markRead') }}">
        @csrf
        <button type="submit" class="l69-btn-ghost" style="font-size:.78rem;">
            <i class="fas fa-check-double"></i> Marcar todo como leído
        </button>
    </form>
    @endif
</div>

{{-- ── Sección: Comentarios recibidos en fotos ── --}}
@if(in_array($cat, ['all', 'comments']) && $photoComments->isNotEmpty())
<div class="l69-notif-section">
    <div class="l69-notif-section__header">
        <i class="fas fa-camera" style="color:#8b5cf6;"></i>
        <span>Comentarios en tus fotos</span>
        <span class="l69-section-count">{{ $photoComments->count() }}</span>
    </div>
    @foreach($photoComments as $c)
    <div class="l69-notif-row {{ is_null($c->read_at) ? 'is-unread' : '' }}">
        <div class="l69-notif-icon" style="background:rgba(139,92,246,.12);">
            <i class="fas fa-comment" style="color:#8b5cf6;"></i>
        </div>
        <div class="l69-notif-body">
            <div class="l69-notif-text">
                <strong>{{ $c->commenter_nick ?? $c->commenter_name }}</strong>
                comentó en tu foto
                @if($c->caption)
                    <span class="l69-notif-ref">"{{ \Illuminate\Support\Str::limit($c->caption, 30) }}"</span>
                @endif
            </div>
            @if($c->body)
            <div class="l69-notif-preview">"{{ \Illuminate\Support\Str::limit($c->body, 80) }}"</div>
            @endif
            <div class="l69-notif-time">
                <i class="fas fa-clock"></i>
                {{ \Carbon\Carbon::parse($c->created_at)->diffForHumans() }}
                @if($c->commenter_nick)
                · <a href="{{ route('profile.show', $c->commenter_nick) }}" class="l69-notif-link">Ver perfil</a>
                @endif
                @if($c->photo_uuid)
                · <a href="/u/{{ $myNick }}?photo={{ $c->photo_uuid }}" class="l69-notif-link">Ver foto</a>
                @endif
            </div>
        </div>
        @if(is_null($c->read_at))
            <div class="l69-unread-dot"></div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- ── Sección: Recomendaciones recibidas ── --}}
@if(in_array($cat, ['all', 'reviews']) && $reviewsReceived->isNotEmpty())
<div class="l69-notif-section">
    <div class="l69-notif-section__header">
        <i class="fas fa-star" style="color:#f59e0b;"></i>
        <span>Recomendaciones recibidas</span>
        <span class="l69-section-count">{{ $reviewsReceived->count() }}</span>
    </div>
    @foreach($reviewsReceived as $r)
    <div class="l69-notif-row">
        <div class="l69-notif-icon"
             style="background:{{ $r->type === 'positive' ? 'rgba(34,197,94,.12)' : 'rgba(239,68,68,.12)' }};">
            <i class="fas {{ $r->type === 'positive' ? 'fa-thumbs-up' : 'fa-thumbs-down' }}"
               style="color:{{ $r->type === 'positive' ? '#22c55e' : '#ef4444' }};"></i>
        </div>
        <div class="l69-notif-body">
            <div class="l69-notif-text">
                <strong>{{ $r->reviewer_nick ?? $r->reviewer_name }}</strong>
                te dejó una recomendación
                <span class="l69-notif-ref l69-badge-{{ $r->type }}">
                    {{ $r->type === 'positive' ? '👍 Positiva' : '👎 Negativa' }}
                </span>
            </div>
            @if($r->body)
            <div class="l69-notif-preview">"{{ \Illuminate\Support\Str::limit($r->body, 80) }}"</div>
            @endif
            <div class="l69-notif-time">
                <i class="fas fa-clock"></i>
                {{ \Carbon\Carbon::parse($r->created_at)->diffForHumans() }}
                @if($r->reviewer_nick)
                · <a href="{{ route('profile.show', $r->reviewer_nick) }}" class="l69-notif-link">Ver perfil</a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── Sección: Notificaciones del sistema ── --}}
@if(in_array($cat, ['all', 'messages', 'social', 'activity']))
<div class="l69-notif-section">
    @if(!in_array($cat, ['messages', 'social', 'activity']))
    <div class="l69-notif-section__header">
        <i class="fas fa-bell" style="color:#e056a0;"></i>
        <span>Sistema</span>
        <span class="l69-section-count">{{ $notifs->count() }}</span>
    </div>
    @endif

    @forelse($notifs as $notif)
    @php
        $isUnread = is_null($notif->read_at);
        $d = is_array($notif->data)
            ? $notif->data
            : (array) json_decode($notif->data ?? '{}', true);

        $icon = 'fa-bell'; $color = '#e056a0';
        $message = 'Nueva notificación';
        $targetValue = '#';
        $subtext = null;

        switch($notif->type) {
            case 'like':
                $icon    = 'fa-heart'; $color = '#e056a0';
                $message = ($d['from_nick'] ?? 'Alguien') . ' le dio like a tu foto';
                if (!empty($d['photo_id']) && $myNick)
                    $targetValue = '/u/' . $myNick . '?photo=' . $d['photo_id'];
                break;
            case 'comment':
                $icon    = 'fa-comment'; $color = '#8b5cf6';
                $message = ($d['from_nick'] ?? 'Alguien') . ' comentó en tu foto';
                if (!empty($d['photo_id']) && $myNick)
                    $targetValue = '/u/' . $myNick . '?photo=' . $d['photo_id'];
                break;
            case 'follow':
                $icon    = 'fa-user-plus'; $color = '#22c55e';
                $message = ($d['from_nick'] ?? 'Alguien') . ' empezó a seguirte';
                $targetValue = !empty($d['from_nick']) ? '/u/' . $d['from_nick'] : '#';
                break;
            case 'new_message':
                $icon    = 'fa-envelope'; $color = '#3b82f6';
                $preview = !empty($d['preview']) ? ': "' . \Illuminate\Support\Str::limit($d['preview'], 40) . '"' : '';
                $senderNick = $d['from_nick'] ?? '';
                if (!$senderNick && !empty($d['sender_id']))
                    $senderNick = \Illuminate\Support\Facades\DB::table('profiles')
                        ->whereRaw('user_id::text = ?', [$d['sender_id']])
                        ->value('nickname') ?? '';
                $message = ($senderNick ?: 'Alguien') . ' te envió un mensaje' . $preview;
                $targetValue = route('messages.index', ['tab' => 'inbox']);
                break;
            case 'friend_request':
                $icon    = 'fa-user-friends'; $color = '#f59e0b';
                $message = ($d['from_nick'] ?? 'Alguien') . ' te envió una solicitud de amistad';
                $targetValue = route('messages.index', ['tab' => 'friends']);
                break;
            case 'friend_accepted':
                $icon    = 'fa-handshake'; $color = '#22c55e';
                $message = ($d['from_nick'] ?? 'Alguien') . ' aceptó tu solicitud de amistad';
                $targetValue = !empty($d['from_nick']) ? '/u/' . $d['from_nick'] : '#';
                break;
            case 'membership_approved':
                $icon    = 'fa-crown'; $color = '#f59e0b';
                $message = 'Tu membresía ' . ($d['plan'] ?? '') . ' fue aprobada';
                $targetValue = route('memberships.status');
                break;
        }
    @endphp
    <div class="l69-notif-row {{ $isUnread ? 'is-unread' : '' }}"
         data-notif-id="{{ $notif->id }}"
         data-unread="{{ $isUnread ? '1' : '0' }}"
         data-target="{{ $targetValue }}"
         style="cursor:pointer;">
        <div class="l69-notif-icon" style="background:rgba(180,60,120,.08);">
            <i class="fas {{ $icon }}" style="color:{{ $color }};"></i>
        </div>
        <div class="l69-notif-body">
            <div class="l69-notif-text" style="font-weight:{{ $isUnread ? '600' : '400' }};">
                {{ $message }}
            </div>
            <div class="l69-notif-time">
                <i class="fas fa-clock"></i>
                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
            </div>
        </div>
        @if($isUnread)
            <div class="l69-unread-dot"></div>
        @endif
    </div>
    @empty
    <div class="l69-notif-empty">
        <i class="fas fa-bell-slash"></i>
        <p>Sin notificaciones en esta categoría.</p>
    </div>
    @endforelse
</div>
@endif

@if($notifs->isEmpty() && $photoComments->isEmpty() && $reviewsReceived->isEmpty())
<div class="l69-notif-empty" style="margin-top:1rem;">
    <i class="fas fa-bell-slash"></i>
    <p>No tienes notificaciones aún.</p>
</div>
@endif

@push('scripts')
<script>
(function() {
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    document.querySelectorAll('.l69-notif-row[data-notif-id]').forEach(function(row) {
        row.addEventListener('click', function() {
            var notifId  = row.dataset.notifId;
            var isUnread = row.dataset.unread === '1';
            var target   = row.dataset.target;
            if (isUnread) {
                row.classList.remove('is-unread');
                row.dataset.unread = '0';
                var dot  = row.querySelector('.l69-unread-dot');
                var text = row.querySelector('.l69-notif-text');
                if (dot)  dot.style.display = 'none';
                if (text) text.style.fontWeight = '400';
                fetch('/notificaciones/' + notifId + '/leer', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                }).catch(function(){});
            }
            if (target && target !== '#') window.location.href = target;
        });
    });
})();
</script>
@endpush
@endsection

