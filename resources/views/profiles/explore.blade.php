@extends('layouts.app')

@section('title', 'Explorar Perfiles — LOBBY69')

@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush

@push('styles')
<style>
/* ══ EXPLORAR ══ */
.exp-filters {
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
    display: flex;
    flex-wrap: wrap;
    gap: .75rem;
    align-items: flex-end;
}
.exp-filter-group {
    display: flex;
    flex-direction: column;
    gap: .3rem;
    min-width: 130px;
    flex: 1;
}
.exp-filter-group label {
    font-size: .75rem;
    font-weight: 600;
    color: var(--theme-text-secondary, #9ca3af);
    text-transform: uppercase;
    letter-spacing: .04em;
}
.exp-select, .exp-input {
    background: var(--bg-input, rgba(255,255,255,.06));
    border: 1px solid rgba(180,60,120,.2);
    border-radius: 8px;
    color: var(--theme-text, #f0e8ff);
    font-size: .85rem;
    padding: .45rem .75rem;
    transition: border-color .2s;
    width: 100%;
}
.exp-select:focus, .exp-input:focus {
    outline: none;
    border-color: #e056a0;
}
[data-theme="light"] .exp-select,
[data-theme="light"] .exp-input {
    color: #1a1028;
    background: rgba(0,0,0,.04);
}
.exp-btn-filter {
    background: #e056a0;
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: .85rem;
    font-weight: 600;
    padding: .5rem 1.25rem;
    cursor: pointer;
    transition: background .2s;
    white-space: nowrap;
    align-self: flex-end;
}
.exp-btn-filter:hover { background: #c43d8a; }
.exp-btn-reset {
    background: transparent;
    border: 1px solid rgba(180,60,120,.3);
    border-radius: 8px;
    color: var(--theme-text-secondary, #9ca3af);
    font-size: .85rem;
    padding: .5rem 1rem;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
    align-self: flex-end;
}
.exp-btn-reset:hover { border-color: #e056a0; color: #e056a0; }

/* Contador de resultados */
.exp-count {
    font-size: .85rem;
    color: var(--theme-text-secondary, #9ca3af);
    margin-bottom: .75rem;
}
.exp-count strong { color: var(--theme-text); }

/* Grid de perfiles */
.exp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

/* Tarjeta de perfil */
.exp-card {
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    transition: transform .15s, box-shadow .15s;
    text-decoration: none;
    display: block;
}
.exp-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
    border-color: rgba(180,60,120,.4);
}
.exp-card__avatar-wrap {
    position: relative;
    aspect-ratio: 1;
    background: #0f0a1a;
    overflow: hidden;
}
.exp-card__avatar {
    width: 100%; height: 100%;
    object-fit: cover; display: block;
    transition: transform .2s;
}
.exp-card:hover .exp-card__avatar { transform: scale(1.05); }

.exp-card__badge-type {
    position: absolute;
    top: .5rem; left: .5rem;
    font-size: .68rem;
    font-weight: 700;
    padding: .2rem .5rem;
    border-radius: 20px;
    backdrop-filter: blur(4px);
}
.exp-card__badge-type--single    { background: rgba(167,139,250,.25); color: #a78bfa; border: 1px solid rgba(167,139,250,.3); }
.exp-card__badge-type--pareja    { background: rgba(224,86,160,.25);  color: #e056a0; border: 1px solid rgba(224,86,160,.3); }
.exp-card__badge-type--unicornio { background: rgba(251,191,36,.25);  color: #fbbf24; border: 1px solid rgba(251,191,36,.3); }

.exp-card__verified {
    position: absolute;
    bottom: .5rem; right: .5rem;
    background: #3b82f6;
    color: #fff;
    border-radius: 50%;
    width: 22px; height: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem;
    border: 2px solid var(--bg-body, #1a1028);
}
.exp-card__body {
    padding: .75rem;
}
.exp-card__nick {
    font-size: .88rem;
    font-weight: 700;
    color: var(--theme-text, #f0e8ff);
    margin: 0 0 .2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.exp-card__meta {
    font-size: .75rem;
    color: var(--theme-text-secondary, #9ca3af);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.exp-card__active {
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #34d399;
    margin-right: .3rem;
    vertical-align: middle;
}

/* Paginación */
.exp-pagination {
    display: flex;
    justify-content: center;
    gap: .4rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}
.exp-page-btn {
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.2);
    border-radius: 8px;
    color: var(--theme-text, #f0e8ff);
    font-size: .82rem;
    padding: .4rem .75rem;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.exp-page-btn:hover,
.exp-page-btn--active {
    background: rgba(180,60,120,.25);
    border-color: rgba(180,60,120,.5);
    color: #e056a0;
}
.exp-page-btn--disabled {
    opacity: .35;
    cursor: not-allowed;
    pointer-events: none;
}

/* Vacío */
.exp-empty {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--theme-surface-2);
    border: 1px solid rgba(180,60,120,.15);
    border-radius: 16px;
}
.exp-empty i {
    font-size: 2.5rem;
    color: rgba(180,60,120,.35);
    display: block;
    margin-bottom: 1rem;
}
.exp-empty p { color: var(--theme-text-secondary, #9ca3af); margin: 0; }

@media (max-width: 640px) {
    .exp-filters { flex-direction: column; }
    .exp-grid    { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
}
/* Estrellas en tarjetas explore */
.exp-card__stars {
    margin-top: .35rem;
    font-size: .65rem;
    color: #f59e0b;
    letter-spacing: .05em;
}
.exp-card__stars .far.fa-star {
    color: rgba(245,158,11,.25);
}
</style>
@endpush

@section('content')


{{-- ── Filtros ── --}}
<form method="GET" action="{{ route('explore') }}">
<div class="exp-filters">

    <div class="exp-filter-group">
        <label>Tipo</label>
        <select name="tipo" class="exp-select">
            <option value="">Todos</option>
            <option value="single"    {{ request('tipo') === 'single'    ? 'selected' : '' }}>Single</option>
            <option value="pareja"    {{ request('tipo') === 'pareja'    ? 'selected' : '' }}>Pareja</option>
            <option value="unicornio" {{ request('tipo') === 'unicornio' ? 'selected' : '' }}>Unicornio</option>
        </select>
    </div>

    <div class="exp-filter-group">
        <label>Género</label>
        <select name="genero" class="exp-select">
            <option value="">Todos</option>
            <option value="masculino" {{ request('genero') === 'masculino' ? 'selected' : '' }}>Masculino</option>
            <option value="femenino"  {{ request('genero') === 'femenino'  ? 'selected' : '' }}>Femenino</option>
            <option value="otro"      {{ request('genero') === 'otro'      ? 'selected' : '' }}>Otro</option>
        </select>
    </div>

    <div class="exp-filter-group">
        <label>Ciudad / Estado</label>
        <input type="text"
               name="ciudad"
               class="exp-input"
               placeholder="Ej: Ciudad de México"
               value="{{ request('ciudad') }}">
    </div>

    <div class="exp-filter-group">
        <label>Orientación</label>
        <select name="orientacion" class="exp-select">
            <option value="">Todas</option>
            <option value="heterosexual" {{ request('orientacion') === 'heterosexual' ? 'selected' : '' }}>Heterosexual</option>
            <option value="bisexual"     {{ request('orientacion') === 'bisexual'     ? 'selected' : '' }}>Bisexual</option>
            <option value="homosexual"   {{ request('orientacion') === 'homosexual'   ? 'selected' : '' }}>Homosexual</option>
        </select>
    </div>

    <div class="exp-filter-group">
        <label>Ordenar por</label>
        <select name="orden" class="exp-select">
            <option value="destacados" {{ request('orden','destacados')==='destacados' ? 'selected' : '' }}>Destacados</option>
            <option value="score"      {{ request('orden')==='score'      ? 'selected' : '' }}>Mayor puntuación</option>
            <option value="activos"    {{ request('orden')==='activos'    ? 'selected' : '' }}>Más activos</option>
            <option value="recientes"  {{ request('orden')==='recientes'  ? 'selected' : '' }}>Más recientes</option>
        </select>
    </div>

    <button type="submit" class="exp-btn-filter">
        <i class="fas fa-search"></i> Buscar
    </button>


    @if(request()->hasAny(['tipo','genero','ciudad','orientacion']))
    <a href="{{ route('explore') }}" class="exp-btn-reset">
        <i class="fas fa-times"></i> Limpiar
    </a>
    @endif

</div>
</form>

{{-- ── Contador ── --}}
<p class="exp-count">
    <strong>{{ $profiles->total() }}</strong>
    {{ $profiles->total() === 1 ? 'perfil encontrado' : 'perfiles encontrados' }}
    @if(request()->hasAny(['tipo','genero','ciudad','orientacion']))
        con los filtros aplicados
    @endif
</p>

{{-- ── Grid ── --}}
@if($profiles->count() > 0)
<div class="exp-grid">
    @foreach($profiles as $profile)
    @php
        $avatarUrl = (!empty($avatars[$profile->user_id]) && isset($avatars[$profile->user_id]->id))
            ? 'https://kjhaquimghhejqznleyn.supabase.co/storage/v1/object/public/gallery/' . $avatars[$profile->user_id]->file_path
            : asset('img/default-avatar.svg');

        $typeClass = match($profile->profile_type ?? 'single') {
            'pareja'    => 'exp-card__badge-type--pareja',
            'unicornio' => 'exp-card__badge-type--unicornio',
            default     => 'exp-card__badge-type--single',
        };
        $typeLabel = match($profile->profile_type ?? 'single') {
            'pareja'    => 'Pareja',
            'unicornio' => 'Unicornio',
            default     => 'Single',
        };

        // Activo en las últimas 24h
        $isActive = $profile->last_active_at
            && \Carbon\Carbon::parse($profile->last_active_at)->diffInHours(now()) < 24;

        $meta = implode(' · ', array_filter([
            $profile->age ? $profile->age . ' años' : null,
            $profile->city ?: null,
        ]));
    @endphp

    <a href="{{ route('profile.show', ['nickname' => $profile->nickname]) }}" class="exp-card">
        <div class="exp-card__avatar-wrap">
            <img loading="lazy" class="exp-card__avatar"
                 src="{{ $avatarUrl }}"
                 alt="{{ $profile->nickname }}"
                 loading="lazy"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">

            <span class="exp-card__badge-type {{ $typeClass }}">{{ $typeLabel }}</span>

            @if($profile->verified_profile)
                <div class="exp-card__verified" title="Verificado">✓</div>
            @endif
        </div>

        <div class="exp-card__body">
            <p class="exp-card__nick">
                @if($isActive)
                    <span class="exp-card__active"></span>
                @endif
                {{ $profile->nickname }}
            </p>
            @php
                $expScore  = floatval($profile->recommendation_score ?? 0);
                $expFull   = (int) floor($expScore);
                $expHalf   = ($expScore - $expFull) >= 0.4 ? 1 : 0;
                $expEmpty  = max(0, 5 - $expFull - $expHalf);
            @endphp
            @if($expScore > 0)
            <div class="exp-card__stars" title="Puntuacion: {{ number_format($expScore,1) }}">
                @for($i = 0; $i < $expFull; $i++)<i class="fa fa-star"></i>@endfor
                @if($expHalf)<i class="fa fa-star-half-o"></i>@endif
                @for($i = 0; $i < $expEmpty; $i++)<i class="fa fa-star-o"></i>@endfor
            </div>
            @endif
            @if($meta)
                <p class="exp-card__meta">{{ $meta }}</p>
            @endif
        </div>
    </a>
    @endforeach
</div>

{{-- ── Paginación ── --}}
@if($profiles->hasPages())
<div class="exp-pagination">
    {{-- Anterior --}}
    @if($profiles->onFirstPage())
        <span class="exp-page-btn exp-page-btn--disabled">
            <i class="fas fa-chevron-left"></i>
        </span>
    @else
        <a href="{{ $profiles->previousPageUrl() }}" class="exp-page-btn">
            <i class="fas fa-chevron-left"></i>
        </a>
    @endif

    {{-- Números --}}
    @foreach($profiles->getUrlRange(1, $profiles->lastPage()) as $page => $url)
        @if($page == $profiles->currentPage())
            <span class="exp-page-btn exp-page-btn--active">{{ $page }}</span>
        @elseif(abs($page - $profiles->currentPage()) <= 2)
            <a href="{{ $url }}" class="exp-page-btn">{{ $page }}</a>
        @elseif($page == 1 || $page == $profiles->lastPage())
            <a href="{{ $url }}" class="exp-page-btn">{{ $page }}</a>
        @elseif(abs($page - $profiles->currentPage()) == 3)
            <span class="exp-page-btn exp-page-btn--disabled">…</span>
        @endif
    @endforeach

    {{-- Siguiente --}}
    @if($profiles->hasMorePages())
        <a href="{{ $profiles->nextPageUrl() }}" class="exp-page-btn">
            <i class="fas fa-chevron-right"></i>
        </a>
    @else
        <span class="exp-page-btn exp-page-btn--disabled">
            <i class="fas fa-chevron-right"></i>
        </span>
    @endif
</div>
@endif

@else
<div class="exp-empty">
    <i class="fas fa-user-friends"></i>
    <p>No se encontraron perfiles con los filtros seleccionados.</p>
</div>
@endif

@endsection





