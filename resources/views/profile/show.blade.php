@extends('layouts.app')

@section('title', ($profile->nickname ?? 'Perfil') . ' — LOBBY69')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@section('content')

@push('styles')
<style>
/* ══ PERFIL PÚBLICO ══ */
.prf-header {
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 16px;
    padding: 1.75rem;
    margin-bottom: 1.25rem;
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}
.prf-avatar-wrap {
    position: relative;
    flex-shrink: 0;
}
.prf-avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(180,60,120,.5);
    display: block;
}
.prf-verified-badge {
    position: absolute;
    bottom: 4px; right: 4px;
    background: #3b82f6;
    color: #fff;
    border-radius: 50%;
    width: 24px; height: 24px;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem;
    border: 2px solid var(--bg-body, #1a1028);
}
.prf-info { flex: 1; min-width: 0; }
.prf-nick {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--theme-text);
    margin: 0 0 .35rem;
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
}
.prf-badge {
    font-size: .75rem;
    font-weight: 600;
    padding: .2rem .65rem;
    border-radius: 20px;
    white-space: nowrap;
}
.prf-badge--verified  { background: rgba(59,130,246,.15); color: #60a5fa; }
.prf-badge--type      { background: rgba(180,60,120,.15); color: #e056a0; }
.prf-badge--member    { background: rgba(120,60,180,.15); color: #a78bfa; }
.prf-location {
    font-size: .88rem;
    color: var(--theme-text-secondary, #9ca3af);
    margin: 0 0 .6rem;
}
.prf-bio {
    font-size: .92rem;
    color: var(--theme-text, #e2d9f3);
    line-height: 1.65;
    margin: 0;
}

/* Cards internas */
.prf-card {
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 14px;
    padding: 1.4rem;
    margin-bottom: 1.25rem;
}
.prf-card__title {
    font-size: .92rem;
    font-weight: 700;
    color: var(--theme-text);
    margin: 0 0 1rem;
    padding-bottom: .65rem;
    border-bottom: 1px solid rgba(180,60,120,.15);
    display: flex;
    align-items: center;
    gap: .4rem;
}

/* Grid de datos físicos */
.prf-data-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}
.prf-data-col-title {
    font-size: .85rem;
    font-weight: 700;
    margin: 0 0 .6rem;
}
.prf-data-col-title--main   { color: #a78bfa; }
.prf-data-col-title--partner { color: #e056a0; }

/* Tabla de datos */
.prf-table {
    width: 100%;
    font-size: .83rem;
    border-collapse: collapse;
}
.prf-table td {
    padding: .3rem 0;
    border-bottom: 1px solid rgba(128,128,128,.1);
    vertical-align: top;
}
.prf-table td:first-child {
    color: var(--theme-text-secondary, #9ca3af);
    width: 45%;
    padding-right: .5rem;
}
.prf-table td:last-child {
    color: var(--theme-text, #e2d9f3);
    font-weight: 500;
}

/* Tags de intereses */
.prf-tags {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
}
.prf-tag {
    font-size: .78rem;
    font-weight: 600;
    padding: .25rem .6rem;
    border-radius: 20px;
    white-space: nowrap;
}
.prf-tag--active   { background: rgba(180,60,120,.2); color: #e056a0; border: 1px solid rgba(180,60,120,.3); }
.prf-tag--inactive { background: rgba(128,128,128,.08); color: var(--theme-text-secondary, #6b7280); border: 1px solid rgba(128,128,128,.1); text-decoration: line-through; opacity: .5; }

/* Grid de fotos */
.prf-photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: .75rem;
}
.prf-photo-item {
    aspect-ratio: 1;
    border-radius: 10px;
    overflow: hidden;
    background: #0f0a1a;
    cursor: pointer;
    transition: transform .15s;
}
.prf-photo-item:hover { transform: scale(1.03); }
.prf-photo-item img {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
}

/* Layout dos columnas */
.prf-body-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}
@media (max-width: 700px) {
    .prf-body-grid      { grid-template-columns: 1fr; }
    .prf-header         { flex-direction: column; align-items: center; text-align: center; }
    .prf-data-grid      { grid-template-columns: 1fr; }
    .prf-photos-grid    { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
}

/* Modo claro */
[data-theme="light"] .prf-table td:last-child { color: #1a1028; }
[data-theme="light"] .prf-bio                 { color: #1a1028; }
[data-theme="light"] .prf-nick                { color: #1a1028; }

/* ── Follow row ── */
.prf-follow-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: .85rem;
    flex-wrap: wrap;
}
.prf-follow-stats {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .88rem;
    color: var(--theme-muted, #9ca3af);
}
.prf-follow-stats strong {
    color: var(--theme-text);
    font-weight: 700;
}
.prf-follow-stat-sep {
    color: var(--theme-muted, #9ca3af);
}

/* Botón Seguir */
.prf-btn-follow {
    padding: .45rem 1.25rem;
    background: linear-gradient(135deg, #8b5cf6, #ec4899);
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .2s;
}
.prf-btn-follow:hover { opacity: .85; }

/* Botón Siguiendo */
.prf-btn-unfollow {
    padding: .45rem 1.25rem;
    background: transparent;
    color: var(--theme-text);
    border: 1.5px solid var(--theme-border);
    border-radius: 999px;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
}
.prf-btn-unfollow:hover {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
}

/* Botón Editar perfil */
.prf-btn-edit {
    padding: .45rem 1.25rem;
    background: transparent;
    color: var(--theme-muted, #9ca3af);
    border: 1.5px solid var(--theme-border);
    border-radius: 999px;
    font-size: .88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s;
}
.prf-btn-edit:hover {
    color: var(--theme-text);
    border-color: var(--theme-text);
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

    // Avatar — usa file_path con la ruta /foto/
    $profilePhoto = DB::table('photos')
        ->whereRaw('user_id::text = ?', [$profile->user_id])
        ->where('is_profile_photo', true)
        ->where('status', 'approved')
        ->first();

    if (!$profilePhoto) {
        $profilePhoto = DB::table('photos')
            ->whereRaw('user_id::text = ?', [$profile->user_id])
            ->where('album_type', 'public')
            ->where('status', 'approved')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->first();
    }

$avatarUrl = $profilePhoto
    ? route('photos.serve', $profilePhoto->id)
    : asset('img/default-avatar.svg');


    // Fotos públicas aprobadas
    $photos = DB::table('photos')
        ->whereRaw('user_id::text = ?', [$profile->user_id])
        ->where('album_type', 'public')
        ->where('status', 'approved')
        ->orderBy('sort_order')
        ->get();

    $verificationStatus = $user->verification_status ?? null;

    $typeLabel = match($profile->profile_type ?? '') {
        'pareja'    => 'Pareja',
        'unicornio' => 'Unicornio',
        default     => 'Single',
    };
    $memberLabel = ucfirst($user->membership_type ?? 'trial');
@endphp

{{-- ── HEADER ── --}}
<div class="prf-header">
    <div class="prf-avatar-wrap">
        <img class="prf-avatar"
             src="{{ $avatarUrl }}"
             alt="{{ $profile->nickname }}"
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
    @php
        $memberIcon = match($user->membership_type ?? 'trial') {
            'explorer'   => asset('img/membership/explorer.png'),
            'connectors' => asset('img/membership/connectors.png'),
            'influencer' => asset('img/membership/influencer.png'),
            'vip_elite'  => asset('img/membership/vip-elite.png'),
            'vitalicio'  => asset('img/membership/vitalicio.png'),
            default      => asset('img/membership/trial.png'),
        };
    @endphp
    <img src="{{ $memberIcon }}"
         alt="{{ $memberLabel }}"
         style="width:18px;height:18px;object-fit:contain;vertical-align:middle;">
    {{ $memberLabel }}
</span>

        </h1>

        @php
            $location = implode(', ', array_filter([
                $profile->city ?? null,
                $profile->state ?? null,
            ]));
        @endphp
        @if($location)
            <p class="prf-location">📍 {{ $location }}</p>
        @endif

                @if($profile->bio)
            <p class="prf-bio">{{ $profile->bio }}</p>
        @endif

        {{-- ── Contadores y botón Follow ── --}}
        <div class="prf-follow-row">

            <div class="prf-follow-stats">
                <span class="prf-follow-stat">
                    <strong>{{ $followersCount }}</strong> seguidores
                </span>
                <span class="prf-follow-stat-sep">·</span>
                <span class="prf-follow-stat">
                    <strong>{{ $followingCount }}</strong> siguiendo
                </span>
            </div>

            @auth
                @if(!$isOwnProfile)
                    @if($isFollowing)
                        <form method="POST"
                              action="{{ route('unfollow', $profile->nickname) }}"
                              style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="prf-btn-unfollow">
                                ✓ Siguiendo
                            </button>
                        </form>
                    @else
                        <form method="POST"
                              action="{{ route('follow', $profile->nickname) }}"
                              style="margin:0;">
                            @csrf
                            <button type="submit" class="prf-btn-follow">
                                + Seguir
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('profile.edit') }}" class="prf-btn-edit">
                        ✏️ Editar perfil
                    </a>
                @endif
            @endauth

        </div>

    </div>
</div>


{{-- ── CUERPO ── --}}
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

{{-- ── FOTOS ── --}}
@if($photos->isNotEmpty())
<div class="prf-card">
    <h2 class="prf-card__title">📸 Fotos públicas</h2>
    <div class="prf-photos-grid">
        @foreach($photos as $photo)
        <div class="prf-photo-item">
            <img src="{{ route('photos.serve', $photo->id) }}"
                 alt="{{ $photo->caption ?? '' }}"
                 loading="lazy"
                 onerror="this.parentElement.style.display='none'">
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection



