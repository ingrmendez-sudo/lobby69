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

{{-- ── Panel Disponible HOY ── --}}
@if($rUser)
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

@elseif(str_starts_with($rRoute, 'explore'))
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-filter"></i> Filtros Rápidos
    </div>
    <div style="display:flex;flex-direction:column;gap:.4rem;">
        <a href="{{ route('explore') }}?type=single"    class="l69-quick-btn"><i class="fas fa-user"></i> Singles</a>
        <a href="{{ route('explore') }}?type=pareja"    class="l69-quick-btn"><i class="fas fa-heart"></i> Parejas</a>
        <a href="{{ route('explore') }}?type=unicornio" class="l69-quick-btn"><i class="fas fa-star"></i> Unicornios</a>
    </div>
</div>

@else
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-bolt"></i> Accesos Rápidos
    </div>
    <div style="display:flex;flex-direction:column;gap:.4rem;">
        @if(!$rIsVerified)
        <a href="{{ route('verification.show') }}" class="l69-quick-btn"
           style="border-color:rgba(245,158,11,.35);color:#fbbf24;">
            <i class="fas fa-id-card"></i> Verificar identidad
        </a>
        @endif
        <a href="{{ route('photos.index') }}" class="l69-quick-btn">
            <i class="fas fa-camera"></i> Subir fotos
        </a>
        <a href="{{ route('videos.index') }}" class="l69-quick-btn">
            <i class="fas fa-video"></i> Subir videos
        </a>
        <a href="{{ route('explore') }}" class="l69-quick-btn">
            <i class="fas fa-compass"></i> Explorar perfiles
        </a>
        @if($rProfile?->nickname)
        <a href="{{ route('profile.show', $rProfile->nickname) }}" class="l69-quick-btn">
            <i class="fas fa-eye"></i> Ver mi perfil público
        </a>
        @endif
    </div>
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




