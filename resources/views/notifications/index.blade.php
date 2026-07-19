@extends('layouts.app')
@section('title', 'Notificaciones — LOBBY69')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="font-size:1.6rem;font-weight:800;color:var(--theme-text);margin:0 0 .25rem;">
        🔔 Notificaciones
    </h1>
    <p style="color:var(--theme-muted);margin:0;">Tus últimas actividades</p>
</div>

<div id="notif-list"
     style="background:var(--theme-card);border:1px solid var(--theme-border);
            border-radius:16px;overflow:hidden;">
    @if($notifs->isEmpty())
    <div style="text-align:center;padding:4rem 2rem;">
        <i class="fas fa-bell-slash"
           style="font-size:3rem;color:rgba(180,60,120,.3);margin-bottom:1rem;display:block;"></i>
        <p style="color:var(--theme-muted);margin:0;">No tienes notificaciones aún.</p>
    </div>
    @else
    @foreach($notifs as $notif)
    @php
        $isUnread = is_null($notif->read_at);
        $d = is_array($notif->data)
            ? $notif->data
            : (array) json_decode($notif->data ?? '{}', true);

        $icon        = 'fa-bell';
        $color       = '#e056a0';
        $message     = 'Nueva notificación';
        $targetType  = 'url';
        $targetValue = '#';

        switch($notif->type) {
            case 'like':
                $icon    = 'fa-heart';
                $color   = '#e056a0';
                $message = ($d['from_nick'] ?? 'Alguien') . ' le dio like a tu foto';
                // Ir al perfil propio con el modal de esa foto abierto
                if (!empty($d['photo_id']) && $myNick) {
                    $targetType  = 'url';
                    $targetValue = '/u/' . $myNick . '?photo=' . $d['photo_id'];
                }
                break;

            case 'comment':
                $icon    = 'fa-comment';
                $color   = '#8b5cf6';
                $message = ($d['from_nick'] ?? 'Alguien') . ' comentó en tu foto';
                if (!empty($d['photo_id']) && $myNick) {
                    $targetType  = 'url';
                    $targetValue = '/u/' . $myNick . '?photo=' . $d['photo_id'];
                }
                break;

            case 'follow':
                $icon        = 'fa-user-plus';
                $color       = '#22c55e';
                $message     = ($d['from_nick'] ?? 'Alguien') . ' empezó a seguirte';
                $targetValue = !empty($d['from_nick']) ? '/u/' . $d['from_nick'] : '#';
                break;

            case 'new_message':
                $icon    = 'fa-envelope';
                $color   = '#3b82f6';
                $preview = !empty($d['preview'])
                    ? ': "' . \Illuminate\Support\Str::limit($d['preview'], 40) . '"'
                    : '';
                $senderNick = $d['from_nick'] ?? '';
                if (!$senderNick && !empty($d['sender_id'])) {
                    $senderNick = DB::table('profiles')
                        ->whereRaw('user_id::text = ?', [$d['sender_id']])
                        ->value('nickname') ?? '';
                }
                $message     = ($senderNick ?: 'Alguien') . ' te envió un mensaje' . $preview;
                $targetValue = route('messages.index', ['tab' => 'inbox']);
                break;

            case 'friend_request':
                $icon        = 'fa-user-friends';
                $color       = '#f59e0b';
                $message     = ($d['from_nick'] ?? 'Alguien') . ' te envió una solicitud de amistad';
                $targetValue = route('messages.index', ['tab' => 'friends']);
                break;

            case 'friend_accepted':
                $icon        = 'fa-handshake';
                $color       = '#22c55e';
                $message     = ($d['from_nick'] ?? 'Alguien') . ' aceptó tu solicitud de amistad';
                $targetValue = !empty($d['from_nick']) ? '/u/' . $d['from_nick'] : '#';
                break;

            case 'article_like':
                $icon        = 'fa-newspaper';
                $color       = '#f59e0b';
                $message     = ($d['from_nick'] ?? 'Alguien') . ' le dio like a un artículo';
                $targetValue = !empty($d['article_id'])
                    ? route('articles.public.show', $d['article_id'])
                    : '#';
                break;
        }
    @endphp
    <div class="notif-row {{ $isUnread ? 'notif-unread' : '' }}"
         data-notif-id="{{ $notif->id }}"
         data-unread="{{ $isUnread ? '1' : '0' }}"
         data-target="{{ $targetValue }}"
         style="display:flex;align-items:flex-start;gap:1rem;padding:1rem 1.25rem;
                border-bottom:1px solid var(--theme-border);cursor:pointer;
                transition:background .15s;
                {{ $isUnread ? 'background:rgba(180,60,120,.08);' : '' }}">

        <div style="width:38px;height:38px;border-radius:50%;flex-shrink:0;
                    background:rgba(180,60,120,.1);display:flex;align-items:center;
                    justify-content:center;pointer-events:none;">
            <i class="fas {{ $icon }}" style="color:{{ $color }};font-size:.9rem;"></i>
        </div>

        <div style="flex:1;min-width:0;pointer-events:none;">
            <span style="font-size:.88rem;color:var(--theme-text);
                         font-weight:{{ $isUnread ? '600' : '400' }};">
                {{ $message }}
            </span>
            <div style="font-size:.75rem;color:var(--theme-muted);margin-top:.2rem;">
                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
            </div>
        </div>

        <div class="notif-dot"
             style="width:8px;height:8px;border-radius:50%;background:#e056a0;
                    flex-shrink:0;margin-top:.35rem;
                    display:{{ $isUnread ? 'block' : 'none' }};">
        </div>
    </div>
    @endforeach
    @endif
</div>

@push('scripts')
<script>
(function() {
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    document.querySelectorAll('.notif-row').forEach(function(row) {
        row.addEventListener('click', function() {
            var notifId  = row.dataset.notifId;
            var isUnread = row.dataset.unread === '1';
            var target   = row.dataset.target;

            // Marcar visualmente como leída al instante
            if (isUnread) {
                row.style.background = 'transparent';
                row.dataset.unread   = '0';
                row.classList.remove('notif-unread');
                var dot  = row.querySelector('.notif-dot');
                var span = row.querySelector('span');
                if (dot)  dot.style.display    = 'none';
                if (span) span.style.fontWeight = '400';

                // Persistir en BD de forma asíncrona
                fetch('/notificaciones/' + notifId + '/leer', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept':       'application/json'
                    }
                }).catch(function(e) {
                    console.warn('markOne failed', e);
                });
            }

            // Navegar al destino
            if (target && target !== '#') {
                window.location.href = target;
            }
        });
    });
})();
</script>
@endpush
@endsection
