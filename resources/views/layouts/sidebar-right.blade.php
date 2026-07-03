@php
    $rUser    = auth()->user();
    $rProfile = $rUser->profile ?? null;
    $rRoute   = request()->route()?->getName() ?? '';

    $rPendingPhotos = 0;
    $rApprovedPhotos = 0;
    try {
        $rPendingPhotos  = \Illuminate\Support\Facades\DB::table('photos')
            ->whereRaw('user_id::text = ?', [$rUser->id])
            ->where('status', 'pending')->count();
        $rApprovedPhotos = \Illuminate\Support\Facades\DB::table('photos')
            ->whereRaw('user_id::text = ?', [$rUser->id])
            ->where('status', 'approved')->count();
    } catch(\Exception $e) {}

    $rPendingVideos  = 0;
    $rApprovedVideos = 0;
    $rTotalViews     = 0;
    try {
        $rPendingVideos  = \Illuminate\Support\Facades\DB::table('videos')
            ->whereRaw('user_id::text = ?', [$rUser->id])
            ->where('status', 'pending')->count();
        $rVStats = \Illuminate\Support\Facades\DB::table('videos')
            ->whereRaw('user_id::text = ?', [$rUser->id])
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(views_count),0) as total_views')
            ->first();
        $rApprovedVideos = $rVStats?->total ?? 0;
        $rTotalViews     = $rVStats?->total_views ?? 0;
    } catch(\Exception $e) {}
@endphp

{{-- Stats generales --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-chart-bar"></i> Mi Actividad
    </div>
    <div class="l69-stat-grid">
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $rApprovedPhotos }}</div>
            <div class="l69-stat__label">Fotos</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $rApprovedVideos }}</div>
            <div class="l69-stat__label">Videos</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value" style="font-size:1rem;">
                @if($rUser->identity_verified ?? false)
                    <i class="fas fa-check-circle" style="color:#27ae60;"></i>
                @else
                    <i class="fas fa-clock" style="color:#f59e0b;"></i>
                @endif
            </div>
            <div class="l69-stat__label">Verificación</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value" style="font-size:.85rem;color:#a78bfa;">
                {{ ucfirst($rUser->membership_type ?? 'trial') }}
            </div>
            <div class="l69-stat__label">Plan</div>
        </div>
    </div>

    @if($rPendingPhotos > 0 || $rPendingVideos > 0)
    <div style="padding:.65rem;background:rgba(245,158,11,.1);
                border:1px solid rgba(245,158,11,.25);border-radius:9px;">
        @if($rPendingPhotos > 0)
        <p style="font-size:.78rem;color:#fbbf24;margin:0 0 .2rem;">
            <i class="fas fa-image"></i> {{ $rPendingPhotos }} foto(s) en revisión
        </p>
        @endif
        @if($rPendingVideos > 0)
        <p style="font-size:.78rem;color:#fbbf24;margin:0;">
            <i class="fas fa-video"></i> {{ $rPendingVideos }} video(s) en revisión
        </p>
        @endif
    </div>
    @endif
</div>

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
            Sube fotos nítidas con buena iluminación
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            El álbum público es visible para todos los verificados
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Las fotos se revisan antes de publicarse
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
        @if(!($rUser->identity_verified ?? false))
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

@if(!($rUser->identity_verified ?? false))
<div class="l69-sidebar-card"
     style="border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.05);">
    <div style="display:flex;align-items:flex-start;gap:.6rem;">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-top:.1rem;flex-shrink:0;"></i>
        <div>
            <p style="font-size:.8rem;font-weight:600;color:#fbbf24;margin:0 0 .3rem;">
                Verificación pendiente
            </p>
            <p style="font-size:.75rem;color:rgba(226,217,243,.6);margin:0 0 .65rem;">
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
