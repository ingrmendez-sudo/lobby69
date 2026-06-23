@php
    $sUser    = auth()->user();
    $sProfile = $sUser->profile ?? null;
    $sAvatar  = $sProfile?->avatar_url ?? asset('img/default-avatar.svg');
    $sNick    = $sProfile?->nickname ?? $sUser->name ?? 'Usuario';
    $sMembership = $sUser->membership_type ?? 'trial';
    $sVerified   = $sUser->identity_verified ?? false;
    $sRoute      = request()->route()?->getName() ?? '';
    $sActive     = fn(string $r) => str_starts_with($sRoute, $r) ? 'is-active' : '';

    // Calcular progreso del perfil
    $sProgress = 0;
    if ($sProfile) {
        $sFields = ['nickname','bio','profile_type','age','gender','location_country'];
        $sFilled = collect($sFields)->filter(fn($f) => !empty($sProfile->$f))->count();
        $sProgress = (int)(($sFilled / count($sFields)) * 100);
        if ($sProfile->avatar_url) $sProgress = min(100, $sProgress + 10);
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

{{-- ── Mini Perfil ── --}}
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
        <span class="l69-membership-badge l69-membership-badge--{{ $sMembership }}">
            <i class="fas {{ $sMembershipInfo['icon'] }}"></i>
            {{ $sMembershipInfo['label'] }}
        </span>

        {{-- Progreso del perfil --}}
        @if($sProgress < 100)
        <div class="l69-profile-progress" style="width:100%;">
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

    {{-- ── Navegación principal ── --}}
    <ul class="l69-sidebar-nav">
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('dashboard') }}" class="{{ $sActive('dashboard') }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('explore') }}" class="{{ $sActive('explore') }}">
                <i class="fas fa-compass"></i> Explorar
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('photos.index') }}" class="{{ $sActive('photos') }}">
                <i class="fas fa-images"></i> Mis Fotos
            </a>
        </li>

        <li class="l69-sidebar-nav__sep"></li>

        <li class="l69-sidebar-nav__item">
            <a href="{{ route('profile.edit') }}" class="{{ $sActive('profile.edit') }}">
                <i class="fas fa-user-edit"></i> Editar Perfil
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('profile.setup') }}" class="{{ $sActive('profile.setup') }}">
                <i class="fas fa-sliders-h"></i> Configuración
            </a>
        </li>

        {{-- Próximas fases --}}
        <li class="l69-sidebar-nav__item" style="opacity:.45;pointer-events:none;">
            <a href="#">
                <i class="fas fa-calendar-day"></i> Disponible HOY
                <span style="font-size:.65rem;background:rgba(180,60,120,.3);padding:.1rem .4rem;border-radius:10px;margin-left:auto;">Pronto</span>
            </a>
        </li>
        <li class="l69-sidebar-nav__item" style="opacity:.45;pointer-events:none;">
            <a href="#">
                <i class="fas fa-book-open"></i> Historias
                <span style="font-size:.65rem;background:rgba(180,60,120,.3);padding:.1rem .4rem;border-radius:10px;margin-left:auto;">Pronto</span>
            </a>
        </li>

        @if($sUser->isAdmin())
        <li class="l69-sidebar-nav__sep"></li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('admin.invitations.index') }}" style="color:#fbbf24;">
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