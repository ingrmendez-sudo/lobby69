@php
    $rUser    = auth()->user();
    $rIsVerified         = false;
    $rVerificationStatus = 'none';
    $rRoute   = request()->route()?->getName() ?? '';
    $rProfile = null;

    if ($rUser) {
        try {
            $rProfile = \Illuminate\Support\Facades\DB::table('profiles')
                ->whereRaw('user_id::text = ?', [(string)$rUser->id])
                ->first();
        } catch(\Exception $e) {}

        try {
            $rVerif = \Illuminate\Support\Facades\DB::table('verifications')
                ->whereRaw('user_id::text = ?', [(string)$rUser->id])
                ->orderByDesc('created_at')
                ->first();
            $rVerificationStatus = $rVerif?->status ?? 'none';
            $rIsVerified         = ($rVerificationStatus === 'approved');
        } catch(\Exception $e) {}

        try {
            $rPendingPhotos  = \Illuminate\Support\Facades\DB::table('photos')
                ->whereRaw('user_id::text = ?', [(string)$rUser->id])
                ->where('status', 'pending')->count();
            $rApprovedPhotos = \Illuminate\Support\Facades\DB::table('photos')
                ->whereRaw('user_id::text = ?', [(string)$rUser->id])
                ->where('status', 'approved')->count();
        } catch(\Exception $e) {
            $rPendingPhotos  = 0;
            $rApprovedPhotos = 0;
        }

        try {
            $rPendingVideos = \Illuminate\Support\Facades\DB::table('videos')
                ->whereRaw('user_id::text = ?', [(string)$rUser->id])
                ->where('status', 'pending')->count();
            $rVStats = \Illuminate\Support\Facades\DB::table('videos')
                ->whereRaw('user_id::text = ?', [(string)$rUser->id])
                ->where('status', 'approved')
                ->selectRaw('COUNT(*) as total, COALESCE(SUM(views_count),0) as total_views')
                ->first();
            $rApprovedVideos = $rVStats?->total ?? 0;
            $rTotalViews     = $rVStats?->total_views ?? 0;
        } catch(\Exception $e) {
            $rPendingVideos  = 0;
            $rApprovedVideos = 0;
            $rTotalViews     = 0;
        }
    } else {
        $rPendingPhotos  = 0;
        $rApprovedPhotos = 0;
        $rPendingVideos  = 0;
        $rApprovedVideos = 0;
        $rTotalViews     = 0;
    }
@endphp

{{-- ── Disponibles ahora (sidebar derecho, solo dashboard) ── --}}
@if($rUser && $rRoute === 'dashboard')
@php
    $sideAvailUsers = \Illuminate\Support\Facades\DB::table('availability as av')
        ->join('users as u', \Illuminate\Support\Facades\DB::raw('u.id::text'), '=', \Illuminate\Support\Facades\DB::raw('av.user_id::text'))
        ->leftJoin('profiles as p', \Illuminate\Support\Facades\DB::raw('p.user_id::text'), '=', \Illuminate\Support\Facades\DB::raw('u.id::text'))
        ->leftJoin(\Illuminate\Support\Facades\DB::raw(
            "(SELECT DISTINCT ON (user_id) user_id::text AS sa_uid, file_path AS sa_avatar
              FROM photos WHERE is_profile_photo = true AND status = 'approved'
              ORDER BY user_id) as sa"
        ), 'sa.sa_uid', '=', \Illuminate\Support\Facades\DB::raw('u.id::text'))
        ->where('av.expires_at', '>', now())
        ->whereRaw('av.user_id::text != ?', [(string)$rUser->id])
        ->select([
            'u.id as user_id',
            \Illuminate\Support\Facades\DB::raw('COALESCE(p.nickname, u.name) as nickname'),
            'av.slot',
            'av.message',
            'av.expires_at',
            'sa.sa_avatar as avatar_path',
        ])
        ->orderBy('av.expires_at', 'asc')
        ->limit(8)
        ->get();

    $sideSlotIcons = [
        'hoy' => '📅', 'entre_semana' => '💼', 'viernes' => '🍹',
        'finde' => '🎉', 'sabado' => '🌙', 'domingo' => '☀️',
    ];
    $supabaseBase = config('filesystems.supabase_public_url', '');
@endphp
@if($sideAvailUsers->count() > 0)
<div class="l69-sidebar-card" style="padding:.85rem;">
    <div class="l69-sidebar-card__title" style="margin-bottom:.65rem;">
        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;
                     background:#22c55e;box-shadow:0 0 6px #22c55e;margin-right:.4rem;
                     vertical-align:middle;flex-shrink:0;"></span>
        Disponibles ahora
        <a href="{{ route('availability.public') }}"
           style="margin-left:auto;font-size:.68rem;color:#a78bfa;font-weight:500;
                  text-decoration:none;white-space:nowrap;">
            Ver todos →
        </a>
    </div>

    <div style="display:flex;flex-direction:column;gap:.5rem;">
    @foreach($sideAvailUsers as $su)
    @php
        $suPhoto = $su->avatar_path
            ? $supabaseBase . '/' . ltrim($su->avatar_path, '/')
            : null;
        $suIcon  = $sideSlotIcons[$su->slot ?? 'hoy'] ?? '📅';
        $suNick  = $su->nickname ?? 'Usuario';
        $suMsg   = trim($su->message ?? '');
        $suUid   = (string)$su->user_id;
    @endphp
    <div style="display:flex;align-items:center;gap:.55rem;padding:.35rem 0;
                border-bottom:1px solid rgba(255,255,255,.05);cursor:pointer;"
         onclick="document.getElementById('dpSideModal').dataset.partner='{{ $suUid }}';
                  document.getElementById('dpSideModal').dataset.nick='{{ e($suNick) }}';
                  document.getElementById('dpSideModalNick').textContent='{{ e($suNick) }}';
                  document.getElementById('dpSideModalBody').value='';
                  document.getElementById('dpSideModalFeedback').textContent='';
                  document.getElementById('dpSideModal').classList.add('is-open');">

        {{-- Avatar --}}
        <div style="width:34px;height:34px;border-radius:50%;overflow:hidden;
                    background:rgba(108,63,197,.25);flex-shrink:0;border:1px solid rgba(108,63,197,.3);">
            @if($suPhoto)
            <img src="{{ $suPhoto }}" alt="{{ $suNick }}"
                 style="width:100%;height:100%;object-fit:cover;"
                 onerror="this.style.display='none'">
            @else
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-user" style="font-size:.75rem;color:#a78bfa;"></i>
            </div>
            @endif
        </div>

        {{-- Info --}}
        <div style="min-width:0;flex:1;">
            <div style="font-size:.78rem;font-weight:700;color:var(--theme-text,#e2d9f3);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $suNick }}
                <span style="font-size:.68rem;font-weight:400;opacity:.7;">{{ $suIcon }}</span>
            </div>
            @if($suMsg)
            <div style="font-size:.7rem;color:rgba(226,217,243,.5);
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                        font-style:italic;">
                "{{ $suMsg }}"
            </div>
            @else
            <div style="font-size:.7rem;color:rgba(226,217,243,.3);">
                Hasta {{ \Carbon\Carbon::parse($su->expires_at)->format('H:i') }}
            </div>
            @endif
        </div>

        {{-- Botón mensaje --}}
        <button style="flex-shrink:0;background:rgba(108,63,197,.25);border:1px solid rgba(108,63,197,.4);
                       border-radius:6px;padding:.22rem .45rem;color:#a78bfa;cursor:pointer;
                       font-size:.68rem;transition:background .2s;"
                onclick="event.stopPropagation();">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
    @endforeach
    </div>
</div>
@endif
@endif

{{-- ── Modal mensaje desde sidebar ── --}}
<div id="dpSideModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);
            backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;"
     data-partner="" data-nick=""
     class=""
     onclick="if(event.target===this)this.classList.remove('is-open')">
    <div style="background:var(--theme-bg,#1a1025);border:1px solid rgba(108,63,197,.35);
                border-radius:16px;padding:1.5rem;width:90%;max-width:400px;position:relative;">
        <button onclick="document.getElementById('dpSideModal').classList.remove('is-open')"
                style="position:absolute;top:.75rem;right:.85rem;background:none;border:none;
                       color:rgba(226,217,243,.5);font-size:1.1rem;cursor:pointer;">&times;</button>
        <p style="font-size:.95rem;font-weight:700;color:var(--theme-text);margin:0 0 .75rem;">
            <i class="fas fa-paper-plane"></i> Enviar mensaje
        </p>
        <p style="font-size:.8rem;color:rgba(226,217,243,.55);margin:0 0 .75rem;">
            Para: <strong id="dpSideModalNick" style="color:rgba(226,217,243,.85);">—</strong>
        </p>
        <textarea id="dpSideModalBody"
                  style="width:100%;background:rgba(255,255,255,.05);border:1px solid rgba(108,63,197,.3);
                         border-radius:8px;color:var(--theme-text);padding:.6rem .75rem;font-size:.85rem;
                         resize:vertical;min-height:90px;box-sizing:border-box;"
                  placeholder="Escribe tu mensaje…" maxlength="500"></textarea>
        <button onclick="dpSideModalSend()"
                style="width:100%;margin-top:.75rem;padding:.55rem;
                       background:linear-gradient(135deg,#6c3fc5,#e056a0);border:none;
                       border-radius:8px;color:#fff;font-size:.88rem;font-weight:700;cursor:pointer;">
            Enviar
        </button>
        <div id="dpSideModalFeedback"
             style="margin-top:.5rem;font-size:.8rem;text-align:center;min-height:1.2em;color:#22c55e;"></div>
    </div>
</div>

<style>
#dpSideModal.is-open { display:flex !important; }
</style>

<script>
if (!window._dpSideModalInit) {
    window._dpSideModalInit = true;
    async function dpSideModalSend() {
        const modal    = document.getElementById('dpSideModal');
        const receiver = modal.dataset.partner;
        const body     = document.getElementById('dpSideModalBody').value.trim();
        const fb       = document.getElementById('dpSideModalFeedback');
        const btn      = modal.querySelector('button[onclick="dpSideModalSend()"]');

        if (!body) { fb.style.color='#f87171'; fb.textContent='Escribe un mensaje primero.'; return; }
        if (!receiver) { fb.style.color='#f87171'; fb.textContent='Error: destinatario no encontrado.'; return; }

        btn.disabled = true;
        fb.style.color = 'rgba(226,217,243,.5)';
        fb.textContent = 'Enviando…';

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const res  = await fetch('/messages/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ receiver_id: receiver, body: body })
            });
            const data = await res.json();
            if (res.ok && (data.success || data.message)) {
                fb.style.color = '#22c55e';
                fb.textContent = '¡Mensaje enviado!';
                setTimeout(() => modal.classList.remove('is-open'), 1400);
            } else {
                fb.style.color = '#f87171';
                fb.textContent = data.error || data.message || 'Error al enviar.';
                btn.disabled = false;
            }
        } catch(err) {
            fb.style.color = '#f87171';
            fb.textContent = 'Error de red. Intenta de nuevo.';
            btn.disabled = false;
        }
    }
}
</script>
{{-- ── Panel Disponible HOY ── --}}
@if($rUser && $rRoute !== 'explore' && !str_starts_with($rRoute, 'events.') && !str_starts_with($rRoute, 'articles.'))
@php
    $rAvail = \Illuminate\Support\Facades\DB::table('availability')
        ->whereRaw('user_id::text = ?', [(string)$rUser->id])
        ->where('expires_at', '>', now())
        ->first();
    $rAvailActive = (bool) $rAvail;
    $slotLabels = [
        'hoy'          => ['label' => 'Hoy',               'icon' => '📅'],
        'entre_semana' => ['label' => 'Entre semana (L–J)', 'icon' => '💼'],
        'viernes'      => ['label' => 'Viernes',            'icon' => '🍹'],
        'finde'        => ['label' => 'Fin de semana',      'icon' => '🎉'],
        'sabado'       => ['label' => 'Sábado',             'icon' => '🌙'],
        'domingo'      => ['label' => 'Domingo',            'icon' => '☀️'],
    ];
    $currentSlotLabel = $slotLabels[$rAvail->slot ?? 'hoy'] ?? ['label' => 'Hoy', 'icon' => '📅'];
@endphp
<div class="l69-sidebar-card avail-panel" id="availPanel">
    <div class="l69-sidebar-card__title">
        <span class="avail-dot-indicator {{ $rAvailActive ? 'is-active' : '' }}"></span>
        Disponible HOY
    </div>

    @if($rAvailActive)
    {{-- Estado activo --}}
    <div class="avail-active-state">
        <div class="avail-active-slot">
            <span class="avail-slot-icon">{{ $currentSlotLabel['icon'] }}</span>
            <span class="avail-slot-text">{{ $currentSlotLabel['label'] }}</span>
        </div>
        <p class="avail-expires-label">
            Expira {{ \Carbon\Carbon::parse($rAvail->expires_at)->translatedFormat('l d/m \a \l\a\s H:i') }}
        </p>
        @if($rAvail->message)
        <p class="avail-message-display">"{{ $rAvail->message }}"</p>
        @endif
        <form method="POST" action="{{ route('availability.deactivate') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="avail-btn avail-btn--off">
                <i class="fas fa-times-circle"></i> Desactivar
            </button>
        </form>
    </div>

    @else
    {{-- Formulario activar --}}
    <form method="POST" action="{{ route('availability.activate') }}" id="availForm">
        @csrf
        <p class="avail-form-label">¿Cuándo estarás disponible?</p>

        <div class="avail-slots-grid">
            @foreach($slotLabels as $slotKey => $slotMeta)
            <label class="avail-slot-pill">
                <input type="radio" name="slot" value="{{ $slotKey }}"
                       {{ $slotKey === 'hoy' ? 'checked' : '' }}>
                <span class="avail-slot-pill__inner">
                    <span class="avail-slot-pill__icon">{{ $slotMeta['icon'] }}</span>
                    <span class="avail-slot-pill__text">{{ $slotMeta['label'] }}</span>
                </span>
            </label>
            @endforeach
        </div>

        <input type="text"
               name="message"
               placeholder="Mensaje opcional (ej: En casa esta tarde)…"
               maxlength="200"
               class="avail-msg-input">

        <label class="avail-notify-row">
            <input type="checkbox" name="notify_followers" value="1" checked>
            <span>Notificar a mis seguidores</span>
        </label>

        <button type="submit" class="avail-btn avail-btn--on">
            <span class="avail-btn__dot"></span>
            Activar disponibilidad
        </button>
    </form>
    @endif
</div>
@endif

{{-- Contexto por página --}}
@if(str_starts_with($rRoute, 'videos'))
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-lightbulb"></i> Consejos
    </div>
    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.6rem;">
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Graba en horizontal para mejor visualización
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Duración mínima 30 seg, máxima 5 min
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Formatos: MP4, MOV, AVI, WEBM
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Los videos se revisan antes de publicarse
        </li>
    </ul>
</div>

@elseif(str_starts_with($rRoute, 'photos'))
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-lightbulb"></i> Consejos
    </div>
    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.6rem;">
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Sube fotos nítidas con buena iluminación.
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Te recomendamos contar con una foto en Blanco y negro en tu galeria.
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            El álbum público es visible para todos los verificados.
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Las fotos se revisan antes de publicarse.
        </li>
    </ul>
</div>

@elseif(str_starts_with($rRoute, 'profile.edit') || str_starts_with($rRoute, 'profile.setup'))
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-tasks"></i> Checklist de perfil
    </div>
    @php
        $checks = [
            ['label' => 'Nick definido',        'done' => !empty($rProfile?->nickname)],
            ['label' => 'Foto de perfil',        'done' => $rApprovedPhotos > 0],
            ['label' => 'Descripción (50+ car)', 'done' => strlen($rProfile?->bio ?? '') >= 50],
            ['label' => 'Ciudad',                'done' => !empty($rProfile?->city)],
            ['label' => 'Qué buscas',            'done' => !empty($rProfile?->looking_for)],
        ];
    @endphp
    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.5rem;">
        @foreach($checks as $check)
        <li style="display:flex;align-items:center;gap:.55rem;font-size:.82rem;">
            @if($check['done'])
                <i class="fas fa-check-circle" style="color:#27ae60;flex-shrink:0;"></i>
                <span style="color:rgba(226,217,243,.5);text-decoration:line-through;">{{ $check['label'] }}</span>
            @else
                <i class="far fa-circle" style="color:rgba(226,217,243,.3);flex-shrink:0;"></i>
                <span style="color:rgba(226,217,243,.85);">{{ $check['label'] }}</span>
            @endif
        </li>
        @endforeach
    </ul>
</div>

@endif

@if(!$rIsVerified)
<div class="l69-sidebar-card"
     style="border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.05);">
    <div style="display:flex;align-items:flex-start;gap:.6rem;">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-top:.1rem;flex-shrink:0;"></i>
        <div>
            <p style="font-size:.8rem;font-weight:600;color:#fbbf24;margin:0 0 .3rem;">
                Verificación pendiente
            </p>
            <p style="font-size:.75rem;color:rgba(97, 94, 102, 0.6);margin:0 0 .65rem;">
                Verifica tu identidad para acceder a todos los perfiles.
            </p>
            <a href="{{ route('verification.show') }}"
               style="font-size:.78rem;color:#f59e0b;font-weight:600;text-decoration:none;">
                Verificar ahora →
            </a>
        </div>
    </div>
</div>
@endif

@if(str_contains($rRoute, 'explore'))
<div class="l69-sidebar-card" style="margin-top:.75rem;">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-user-plus"></i> Nuevos perfiles
    </div>
    @php
    try {
        $rNewProfiles = \Illuminate\Support\Facades\DB::table('profiles')
            ->join('users', \Illuminate\Support\Facades\DB::raw('users.id::text'), '=', \Illuminate\Support\Facades\DB::raw('profiles.user_id::text'))
            ->where('profiles.profile_completed', true)
            ->where('profiles.public', true)
            ->where('users.active', true)
            ->where('users.role', '!=', 'admin')
            ->whereRaw('profiles.user_id::text != ?', [(string)$rUser->id])
            ->orderByDesc('users.created_at')
            ->limit(5)
            ->select(['profiles.nickname', 'profiles.display_name', 'profiles.profile_type', 'profiles.verified_profile', 'users.created_at'])
            ->get();
    } catch(\Exception $e) { $rNewProfiles = collect(); }
    @endphp
    @forelse($rNewProfiles as $np)
    <a href="{{ $np->nickname ? route('profile.show', $np->nickname) : '#' }}"
       style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.05);">
        <div style="width:28px;height:28px;border-radius:50%;background:rgba(108,63,197,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-user" style="font-size:.65rem;color:#a78bfa;"></i>
        </div>
        <div style="min-width:0;">
            <div style="font-size:.78rem;font-weight:600;color:var(--theme-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $np->display_name ?? $np->nickname ?? 'Usuario' }}
                @if($np->verified_profile)
                <i class="fas fa-check-circle" style="color:#22c55e;font-size:.6rem;"></i>
                @endif
            </div>
            <div style="font-size:.68rem;color:var(--theme-muted);">{{ $np->profile_type ?? '—' }} · {{ \Carbon\Carbon::parse($np->created_at)->diffForHumans() }}</div>
        </div>
    </a>
    @empty
    <p style="font-size:.78rem;color:var(--theme-muted);margin:0;">Sin nuevos perfiles.</p>
    @endforelse
</div>

<div class="l69-sidebar-card" style="margin-top:.75rem;">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-thumbs-up"></i> Recomendados
    </div>
    @php
    try {
        $rFollowingIds = \Illuminate\Support\Facades\DB::table('follows')
            ->whereRaw('follower_id::text = ?', [(string)$rUser->id])
            ->pluck(\Illuminate\Support\Facades\DB::raw('following_id::text'))
            ->toArray();
        $rFollowingIds[] = (string)$rUser->id;
        $rUserCity = $rProfile?->city ?? null;

        $rRecommended = \Illuminate\Support\Facades\DB::table('profiles')
            ->join('users', \Illuminate\Support\Facades\DB::raw('users.id::text'), '=', \Illuminate\Support\Facades\DB::raw('profiles.user_id::text'))
            ->where('profiles.profile_completed', true)
            ->where('profiles.public', true)
            ->where('users.active', true)
            ->where('users.role', '!=', 'admin')
            ->whereNotIn(\Illuminate\Support\Facades\DB::raw('profiles.user_id::text'), $rFollowingIds)
            ->when($rUserCity, fn($q) => $q->where('profiles.city', 'ilike', '%'.$rUserCity.'%'))
            ->orderByDesc('profiles.last_active_at')
            ->limit(5)
            ->select(['profiles.nickname', 'profiles.display_name', 'profiles.profile_type', 'profiles.city', 'profiles.verified_profile'])
            ->get();
    } catch(\Exception $e) { $rRecommended = collect(); }
    @endphp
    @forelse($rRecommended as $rp)
    <a href="{{ $rp->nickname ? route('profile.show', $rp->nickname) : '#' }}"
       style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.05);">
        <div style="width:28px;height:28px;border-radius:50%;background:rgba(224,86,160,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas fa-user" style="font-size:.65rem;color:#f472b6;"></i>
        </div>
        <div style="min-width:0;">
            <div style="font-size:.78rem;font-weight:600;color:var(--theme-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $rp->display_name ?? $rp->nickname ?? 'Usuario' }}
                @if($rp->verified_profile)
                <i class="fas fa-check-circle" style="color:#22c55e;font-size:.6rem;"></i>
                @endif
            </div>
            <div style="font-size:.68rem;color:var(--theme-muted);">{{ $rp->city ?? $rp->profile_type ?? '—' }}</div>
        </div>
    </a>
    @empty
    <p style="font-size:.78rem;color:var(--theme-muted);margin:0;">Sin recomendaciones aún.</p>
    @endforelse
</div>
@endif




