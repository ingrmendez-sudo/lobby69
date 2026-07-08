@php
    $sUser    = auth()->user();
    $sProfile = $sUser->profile ?? null;
    $sNick    = $sProfile?->nickname ?? $sUser->name ?? 'Usuario';
    $sMembership = $sUser->membership_type ?? 'trial';
    $sVerified   = $sUser->identity_verified ?? false;
    $sRoute      = request()->route()?->getName() ?? '';
    $sActive     = fn(string $r) => str_starts_with($sRoute, $r) ? 'is-active' : '';

    $sProgress = 0;
    if ($sProfile) {
        $sFields = ['nickname','bio','profile_type','age','gender','city'];
        $sFilled = collect($sFields)->filter(fn($f) => !empty($sProfile->$f))->count();
        $sProgress = (int)(($sFilled / count($sFields)) * 100);
    }

    $sAvatar = null;
    try {
        $sAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
            ->whereRaw('user_id::text = ?', [$sUser->id])
            ->where('is_profile_photo', true)
            ->where('status', 'approved')
            ->first();
        if (!$sAvatarPhoto) {
            $sAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
                ->whereRaw('user_id::text = ?', [$sUser->id])
                ->where('album_type', 'public')
                ->where('status', 'approved')
                ->orderBy('sort_order')
                ->first();
        }
        $sAvatar = $sAvatarPhoto
            ? route('photos.serve', $sAvatarPhoto->id)
            : asset('img/default-avatar.svg');
    } catch(\Exception $e) {
        $sAvatar = asset('img/default-avatar.svg');
    }

    $sMembershipLabels = [
        'trial'      => ['label' => 'Trial',      'icon' => 'fa-clock'],
        'explorer'   => ['label' => 'Explorer',   'icon' => 'fa-compass'],
        'connectors' => ['label' => 'Connectors', 'icon' => 'fa-link'],
        'influencer' => ['label' => 'Influencer', 'icon' => 'fa-star'],
        'vip_elite'  => ['label' => 'VIP Elite',  'icon' => 'fa-gem'],
        'vitalicio'  => ['label' => 'Vitalicio',  'icon' => 'fa-crown'],
    ];
    $sMembershipInfo = $sMembershipLabels[$sMembership] ?? $sMembershipLabels['trial'];
@endphp

{{-- ══ BLOQUE FIJO: Mini Perfil ══ --}}
<div class="l69-sidebar-card" style="padding-top:1.5rem;">
    <div class="l69-mini-profile">
        <div class="l69-mini-profile__avatar-wrap">
            <img src="{{ $sAvatar }}"
                 alt="{{ $sNick }}"
                 class="l69-mini-profile__avatar"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            @if($sVerified)
            <div class="l69-mini-profile__verified" title="Verificado">
                <i class="fas fa-check"></i>
            </div>
            @endif
        </div>
        <div class="l69-mini-profile__nick">{{ $sNick }}</div>
        <div class="l69-mini-profile__type">
            @if($sProfile?->profile_type === 'pareja')
                <i class="fas fa-heart"></i> Pareja
            @elseif($sProfile?->profile_type === 'unicornio')
                <i class="fas fa-star"></i> Unicornio
            @else
                <i class="fas fa-user"></i> Single
            @endif
        </div>
        @php
            $iconFile = match($sMembership) {
                'trial_verified' => 'trial.png',
                'explorer'       => 'explorer.png',
                'connectors'     => 'connectors.png',
                'influencer'     => 'influencer.png',
                'vip_elite'      => 'vip-elite.png',
                'vitalicio'      => 'vitalicio.png',
                default          => 'trial.png',
            };
        @endphp
        <span class="l69-membership-badge l69-membership-badge--{{ $sMembership }}">
            <img src="{{ asset('img/membership/' . $iconFile) }}"
                 style="width:14px;height:14px;object-fit:contain;"
                 onerror="this.style.display='none'">
            {{ $sMembershipInfo['label'] }}
        </span>

        @if($sProgress < 100)
        <div class="l69-profile-progress" style="width:100%;margin-top:.75rem;">
            <div class="l69-profile-progress__label">
                <span>Perfil completado</span>
                <span>{{ $sProgress }}%</span>
            </div>
            <div class="l69-profile-progress__bar">
                <div class="l69-profile-progress__fill" style="width:{{ $sProgress }}%"></div>
            </div>
        </div>
        @endif
    </div>

    @if(str_starts_with($sRoute, 'dashboard'))

{{-- ── Últimas visitas a mi perfil ── --}}
@php
    $sWhoViewed = $whoViewedMe ?? collect();
    $sWhoViewedCount = $whoViewedMeCount ?? 0;
@endphp
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-eye"></i> Quién me visitó
        @if($sWhoViewedCount > 5)
            <span style="margin-left:auto;font-size:.7rem;color:rgba(180,60,120,.7);">
                +{{ $sWhoViewedCount - 5 }} más
            </span>
        @endif
    </div>
    @if($sWhoViewed->isEmpty())
        <p style="font-size:.8rem;color:var(--theme-muted,#9ca3af);text-align:center;padding:.5rem 0;">
            Aún no tienes visitas
        </p>
    @else
        <div style="display:flex;flex-direction:column;gap:.5rem;">
            @foreach($sWhoViewed->take(5) as $visitor)
            @php
                $vProfile = $visitor->profile;
                $vNick    = $vProfile?->nickname ?? 'Usuario';
                $vAvatar  = null;
                try {
                    $vAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
                        ->whereRaw('user_id::text = ?', [$visitor->id])
                        ->where('is_profile_photo', true)
                        ->where('status', 'approved')
                        ->first();
                    if (!$vAvatarPhoto) {
                        $vAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
                            ->whereRaw('user_id::text = ?', [$visitor->id])
                            ->where('album_type', 'public')
                            ->where('status', 'approved')
                            ->orderBy('sort_order')
                            ->first();
                    }
                    $vAvatar = $vAvatarPhoto ? route('photos.serve', $vAvatarPhoto->id) : null;
                } catch(\Exception $e) {}
                $vTime = $visitor->last_seen_at
                    ? \Carbon\Carbon::parse($visitor->last_seen_at)->diffForHumans()
                    : '';
            @endphp
            <div style="display:flex;align-items:center;gap:.6rem;">
                <img src="{{ $vAvatar ?? asset('img/default-avatar.svg') }}"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                <div style="min-width:0;flex:1;">
                    @if($vProfile?->nickname)
                    <a href="{{ route('profile.show', $vProfile->nickname) }}"
                       style="font-size:.82rem;font-weight:600;color:var(--theme-text);
                              text-decoration:none;display:block;
                              white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $vNick }}
                    </a>
                    @else
                    <span style="font-size:.82rem;font-weight:600;color:var(--theme-text);">
                        {{ $vNick }}
                    </span>
                    @endif
                    @if($vTime)
                    <span style="font-size:.72rem;color:var(--theme-muted,#9ca3af);">
                        {{ $vTime }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if($sWhoViewedCount > 5)
        <a href="#" style="display:block;text-align:center;font-size:.78rem;
                           color:rgba(180,60,120,.8);margin-top:.75rem;
                           text-decoration:none;font-weight:600;">
            Ver historial completo →
        </a>
        @endif
    @endif
</div>

{{-- ── Últimos perfiles visitados ── --}}
@php
    $sIViewed      = $iViewed      ?? collect();
    $sIViewedCount = $iViewedCount ?? 0;
@endphp
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-user-clock"></i> Últimos visitados
        @if($sIViewedCount > 5)
            <span style="margin-left:auto;font-size:.7rem;color:rgba(180,60,120,.7);">
                +{{ $sIViewedCount - 5 }} más
            </span>
        @endif
    </div>
    @if($sIViewed->isEmpty())
        <p style="font-size:.8rem;color:var(--theme-muted,#9ca3af);text-align:center;padding:.5rem 0;">
            No has visitado perfiles aún
        </p>
    @else
        <div style="display:flex;flex-direction:column;gap:.5rem;">
            @foreach($sIViewed->take(5) as $visited)
            @php
                $vdProfile = $visited->profile;
                $vdNick    = $vdProfile?->nickname ?? 'Usuario';
                $vdAvatar  = null;
                try {
                    $vdAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
                        ->whereRaw('user_id::text = ?', [$visited->id])
                        ->where('is_profile_photo', true)
                        ->where('status', 'approved')
                        ->first();
                    if (!$vdAvatarPhoto) {
                        $vdAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
                            ->whereRaw('user_id::text = ?', [$visited->id])
                            ->where('album_type', 'public')
                            ->where('status', 'approved')
                            ->orderBy('sort_order')
                            ->first();
                    }
                    $vdAvatar = $vdAvatarPhoto ? route('photos.serve', $vdAvatarPhoto->id) : null;
                } catch(\Exception $e) {}
            @endphp
            <div style="display:flex;align-items:center;gap:.6rem;">
                <img src="{{ $vdAvatar ?? asset('img/default-avatar.svg') }}"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                <div style="min-width:0;flex:1;">
                    @if($vdProfile?->nickname)
                    <a href="{{ route('profile.show', $vdProfile->nickname) }}"
                       style="font-size:.82rem;font-weight:600;color:var(--theme-text);
                              text-decoration:none;display:block;
                              white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $vdNick }}
                    </a>
                    @else
                    <span style="font-size:.82rem;font-weight:600;color:var(--theme-text);">
                        {{ $vdNick }}
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if($sIViewedCount > 5)
        <a href="#" style="display:block;text-align:center;font-size:.78rem;
                           color:rgba(180,60,120,.8);margin-top:.75rem;
                           text-decoration:none;font-weight:600;">
            Ver historial completo →
        </a>
        @endif
    @endif
</div>

@endif {{-- fin if dashboard --}}


    {{-- ══ NAVEGACIÓN CONTEXTUAL ══ --}}
    <ul class="l69-sidebar-nav">

        @if(str_starts_with($sRoute, 'videos'))
        {{-- Contexto: Mis Videos --}}
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('videos.index') }}" class="is-active">
                <i class="fas fa-video"></i> Mis Videos
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('photos.index') }}">
                <i class="fas fa-images"></i> Mis Fotos
            </a>
        </li>
        <li class="l69-sidebar-nav__sep"></li>
        <li style="padding:.4rem .75rem;">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                      letter-spacing:.08em;color:rgba(180,60,120,.8);margin:0 0 .5rem;">
                <i class="fas fa-info-circle"></i> Límites
            </p>
            <div style="display:flex;flex-direction:column;gap:.3rem;">
                <div style="font-size:.78rem;color:var(--theme-muted,#9ca3af);
                            display:flex;justify-content:space-between;">
                    <span>Duración mín.</span><strong>30 seg</strong>
                </div>
                <div style="font-size:.78rem;color:var(--theme-muted,#9ca3af);
                            display:flex;justify-content:space-between;">
                    <span>Duración máx.</span><strong>5 min</strong>
                </div>
                <div style="font-size:.78rem;color:var(--theme-muted,#9ca3af);
                            display:flex;justify-content:space-between;">
                    <span>Peso máx.</span><strong>100 MB</strong>
                </div>
                <div style="font-size:.78rem;color:var(--theme-muted,#9ca3af);
                            display:flex;justify-content:space-between;">
                    <span>Formatos</span><strong>MP4 MOV WEBM</strong>
                </div>
            </div>
        </li>

        @elseif(str_starts_with($sRoute, 'photos'))
        {{-- Contexto: Mis Fotos --}}
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('photos.index') }}" class="is-active">
                <i class="fas fa-images"></i> Mis Fotos
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('videos.index') }}">
                <i class="fas fa-video"></i> Mis Videos
            </a>
        </li>
        <li class="l69-sidebar-nav__sep"></li>
        <li style="padding:.4rem .75rem;">
            <p style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                      letter-spacing:.08em;color:rgba(180,60,120,.8);margin:0 0 .5rem;">
                <i class="fas fa-info-circle"></i> Álbumes
            </p>
            <div style="display:flex;flex-direction:column;gap:.3rem;">
                <div style="font-size:.78rem;color:var(--theme-muted,#9ca3af);
                            display:flex;justify-content:space-between;">
                    <span>🌐 Público</span><span>Todos los verificados</span>
                </div>
                <div style="font-size:.78rem;color:var(--theme-muted,#9ca3af);
                            display:flex;justify-content:space-between;">
                    <span>🔒 Privado</span><span>Connectors+</span>
                </div>
                <div style="font-size:.78rem;color:var(--theme-muted,#9ca3af);
                            display:flex;justify-content:space-between;">
                    <span>👑 VIP</span><span>VIP Elite+</span>
                </div>
            </div>
        </li>

        @elseif(str_starts_with($sRoute, 'explore'))
        {{-- Contexto: Explorar --}}
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('explore') }}" class="is-active">
                <i class="fas fa-compass"></i> Explorar
            </a>
        </li>
        <li class="l69-sidebar-nav__sep"></li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('explore') }}?type=single">
                <i class="fas fa-user"></i> Singles
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('explore') }}?type=pareja">
                <i class="fas fa-heart"></i> Parejas
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('explore') }}?type=unicornio">
                <i class="fas fa-star"></i> Unicornios
            </a>
        </li>

        @elseif(str_starts_with($sRoute, 'profile'))
        {{-- Contexto: Perfil --}}
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('profile.edit') }}" class="{{ $sActive('profile.edit') }}">
                <i class="fas fa-user-edit"></i> Editar Perfil
            </a>
        </li>
        @if($sProfile?->nickname)
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('profile.show', $sProfile->nickname) }}">
                <i class="fas fa-eye"></i> Ver mi perfil
            </a>
        </li>
        @endif

        @else
        {{-- Contexto: Dashboard y resto --}}

        <li class="l69-sidebar-nav__item">
            <a href="{{ route('events.public.index') }}">
                <i class="fas fa-calendar-alt"></i> Eventos
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('articles.public.index') }}">
                <i class="fas fa-newspaper"></i> Noticias
            </a>
        </li>
        @endif

        @if($sUser->isAdmin())
        <li class="l69-sidebar-nav__sep"></li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('admin.photos.index') }}" style="color:#fbbf24;">
                <i class="fas fa-shield-alt"></i> Panel Admin
            </a>
        </li>
        @endif

        <li class="l69-sidebar-nav__sep"></li>
        <li class="l69-sidebar-nav__item l69-sidebar-nav__item--danger">
            <form method="POST" action="{{ route('logout') }}" style="margin:0;width:100%;">
                @csrf
                <button type="submit">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </button>
            </form>
        </li>
    </ul>
</div>



