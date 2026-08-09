@extends('layouts.app')

@section('title', 'Disponibles ahora - LOBBY69')

@section('content')
<div class="avail-page">

    {{-- Header --}}
    <div class="avail-page__header">
        <div class="avail-page__header-left">
            <span class="avail-page__pulse"></span>
            <h1 class="avail-page__title">Disponibles ahora</h1>
            <span class="avail-page__total">{{ $total }} {{ $total === 1 ? 'persona' : 'personas' }}</span>
        </div>
        <a href="{{ route('dashboard') }}" class="avail-page__back">&larr; Volver</a>
    </div>

    <div class="avail-page__layout">

        {{-- Columna izquierda: filtros --}}
        <aside class="avail-page__sidebar-left">
            <div class="avail-sidebar-box">
                <h3 class="avail-sidebar-box__title">Filtrar</h3>
                <form method="GET" action="{{ url('/disponibles') }}">
                    @if($search)
                        <input type="hidden" name="q" value="{{ $search }}">
                    @endif
                    <div class="avail-sidebar-slots">
                        <a href="{{ url('/disponibles') }}{{ $search ? '?q='.$search : '' }}"
                           class="avail-filter-btn {{ !$slotFilter ? 'is-active' : '' }}">
                            Todos
                        </a>
                        @foreach($slotLabels as $key => $meta)
                        <a href="{{ url('/disponibles') }}?slot={{ $key }}{{ $search ? '&q='.$search : '' }}"
                           class="avail-filter-btn {{ $slotFilter === $key ? 'is-active' : '' }}">
                            {{ $meta['icon'] }} {{ $meta['label'] }}
                        </a>
                        @endforeach
                    </div>
                </form>
            </div>

            <div class="avail-sidebar-box" style="margin-top:1rem">
                <h3 class="avail-sidebar-box__title">Buscar</h3>
                <form method="GET" action="{{ url('/disponibles') }}">
                    @if($slotFilter)
                        <input type="hidden" name="slot" value="{{ $slotFilter }}">
                    @endif
                    <input type="text"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Nick o ciudad..."
                           class="avail-page__search">
                    <button type="submit" class="avail-filter-btn is-active" style="width:100%;margin-top:.5rem">
                        Buscar
                    </button>
                </form>
            </div>
        </aside>

        {{-- Columna central: grid --}}
        <main class="avail-page__main">
            @if($available->isEmpty())
                <div class="avail-page__empty">
                    <span style="font-size:2.5rem">&#128564;</span>
                    <p>Nadie disponible en este momento.</p>
                    <p style="font-size:.85rem;opacity:.6">Vuelve mas tarde o activa tu propia disponibilidad.</p>
                </div>
            @else
            <div class="avail-page__grid">
                @foreach($available as $u)
                @php
                    $avatarUrl  = supabase_photo_url($u->avatar_path ?? null) ?? asset('img/default-avatar.svg');
                    $profileUrl = $u->nickname ? route('profile.show', $u->nickname) : '#';
                    $mins       = max(0, (int) now()->diffInMinutes(\Carbon\Carbon::parse($u->expires_at), false));
                    $hrs        = floor($mins / 60);
                    $minRest    = $mins % 60;
                    $timeLabel  = $hrs > 0 ? "{$hrs}h {$minRest}m" : "{$mins}m";
                    $slotMeta   = $slotLabels[$u->slot] ?? ['icon' => '&#128197;', 'label' => $u->slot];
                @endphp
                <a href="{{ $profileUrl }}" class="avail-ucard">
                    <div class="avail-ucard__img-wrap">
                        <img src="{{ $avatarUrl }}"
                             alt="{{ $u->nickname ?? $u->name }}"
                             class="avail-ucard__img"
                             loading="lazy"
                             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                        <span class="avail-ucard__online-dot"></span>
                        @if($u->verified_profile)
                            <span class="avail-ucard__verified" title="Verificado">&#10003;</span>
                        @endif
                    </div>
                    <div class="avail-ucard__body">
                        <p class="avail-ucard__name">{{ Str::limit($u->nickname ?? $u->name, 18) }}</p>
                        @if($u->nickname)
                            <p class="avail-ucard__nick">&#64;{{ $u->nickname }}</p>
                        @endif
                        @if($u->city)
                            <p class="avail-ucard__city">&#128205; {{ $u->city }}</p>
                        @endif
                        <div class="avail-ucard__meta">
                            <span class="avail-ucard__slot">{{ $slotMeta['icon'] }} {{ $slotMeta['label'] }}</span>
                            <span class="avail-ucard__time">{{ $timeLabel }}</span>
                        </div>
                        @if($u->message)
                            <p class="avail-ucard__msg">"{{ Str::limit($u->message, 60) }}"</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            <div class="avail-page__pagination">
                {{ $available->appends(['slot' => $slotFilter, 'q' => $search])->links() }}
            </div>
            @endif
        </main>

        {{-- Columna derecha: CTA + info --}}
        <aside class="avail-page__sidebar-right">
            @auth
            <div class="avail-sidebar-box avail-sidebar-box--cta">
                <h3 class="avail-sidebar-box__title">&#128293; Tu disponibilidad</h3>
                @if(auth()->user()->activeAvailability ?? false)
                    <p class="avail-sidebar-cta__desc">Ya estas disponible. Otros pueden verte aqui.</p>
                    <form method="POST" action="{{ route('availability.deactivate') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="avail-sidebar-cta__btn avail-sidebar-cta__btn--off">
                            Desactivar
                        </button>
                    </form>
                @else
                    <p class="avail-sidebar-cta__desc">Activa tu disponibilidad para que otros te encuentren aqui.</p>
                    <a href="{{ route('dashboard') }}" class="avail-sidebar-cta__btn avail-sidebar-cta__btn--on">
                        Activar ahora
                    </a>
                @endif
            </div>
            @endauth

            <div class="avail-sidebar-box" style="margin-top:1rem">
                <h3 class="avail-sidebar-box__title">&#128276; Como funciona</h3>
                <ul class="avail-sidebar-how">
                    <li>Activa tu disponibilidad desde el panel lateral</li>
                    <li>Elige el slot de tiempo que mejor te va</li>
                    <li>Otros miembros podran verte en esta pagina</li>
                    <li>Se desactiva automaticamente al expirar</li>
                </ul>
            </div>
        </aside>

    </div>{{-- /.avail-page__layout --}}
</div>{{-- /.avail-page --}}

@push('styles')
<style>
.avail-page { max-width: 1400px; margin: 0 auto; padding: 2rem 1.5rem; }

.avail-page__header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.5rem;
}
.avail-page__header-left { display: flex; align-items: center; gap: .75rem; }
.avail-page__pulse {
    width: 10px; height: 10px; border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34,197,94,.25);
    animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{box-shadow:0 0 0 3px rgba(34,197,94,.25)} 50%{box-shadow:0 0 0 6px rgba(34,197,94,.1)} }
.avail-page__title { font-size: 1.4rem; font-weight: 700; margin: 0; }
.avail-page__total {
    background: #22c55e; color: #fff;
    font-size: .8rem; font-weight: 600;
    padding: .2rem .6rem; border-radius: 999px;
}
.avail-page__back { font-size: .9rem; opacity: .7; text-decoration: none; }
.avail-page__back:hover { opacity: 1; }

/* Layout 3 columnas */
.avail-page__layout {
    display: grid;
    grid-template-columns: 180px 1fr 200px;
    gap: 1.5rem;
    align-items: start;
}
@media(max-width: 700px) {
    .avail-page__layout { grid-template-columns: 1fr; }
    .avail-page__sidebar-left, .avail-page__sidebar-right { display: none; }
}

/* Sidebar boxes */
.avail-sidebar-box {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: .75rem;
    padding: 1rem;
}
.avail-sidebar-box__title {
    font-size: .8rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .05em;
    opacity: .5; margin: 0 0 .75rem;
}
.avail-sidebar-slots { display: flex; flex-direction: column; gap: .35rem; }

/* Filtros */
.avail-filter-btn {
    display: block; width: 100%;
    padding: .4rem .75rem; border-radius: .5rem;
    font-size: .85rem; font-weight: 500;
    border: 1px solid var(--border, #e5e7eb);
    background: transparent; cursor: pointer;
    text-decoration: none; color: inherit;
    transition: all .15s;
    text-align: left;
}
.avail-filter-btn:hover { background: var(--bg-hover, #f3f4f6); }
.avail-filter-btn.is-active {
    background: #7c3aed; color: #fff; border-color: #7c3aed;
}

/* Busqueda */
.avail-page__search {
    width: 100%; padding: .5rem .75rem;
    border: 1px solid var(--border, #e5e7eb);
    border-radius: .5rem; font-size: .9rem;
    background: var(--bg-input, #fff);
    color: inherit; box-sizing: border-box;
}

/* Grid central */
.avail-page__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1rem;
}

/* Tarjeta */
.avail-ucard {
    border-radius: .75rem; overflow: hidden;
    border: 1px solid var(--border, #e5e7eb);
    background: var(--bg-card, #fff);
    text-decoration: none; color: inherit;
    transition: transform .15s, box-shadow .15s;
    display: block;
}
.avail-ucard:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.1); }
.avail-ucard__img-wrap { position: relative; aspect-ratio: 3/4; overflow: hidden; }
.avail-ucard__img { width: 100%; height: 100%; object-fit: cover; }
.avail-ucard__online-dot {
    position: absolute; bottom: 6px; right: 6px;
    width: 10px; height: 10px; border-radius: 50%;
    background: #22c55e; border: 2px solid #fff;
}
.avail-ucard__verified {
    position: absolute; top: 6px; right: 6px;
    background: #3b82f6; color: #fff;
    font-size: .7rem; width: 18px; height: 18px;
    border-radius: 50%; display: grid; place-items: center;
}
.avail-ucard__body { padding: .6rem .75rem; }
.avail-ucard__name { font-weight: 700; font-size: .9rem; margin: 0 0 .15rem; }
.avail-ucard__nick { font-size: .78rem; opacity: .5; margin: 0 0 .25rem; }
.avail-ucard__city { font-size: .78rem; opacity: .65; margin: 0 0 .35rem; }
.avail-ucard__meta {
    display: flex; justify-content: space-between; align-items: center;
    font-size: .75rem; margin-bottom: .3rem;
}
.avail-ucard__slot {
    background: #f3e8ff; color: #7c3aed;
    padding: .15rem .45rem; border-radius: 999px; font-weight: 600;
}
.avail-ucard__time { opacity: .5; }
.avail-ucard__msg { font-size: .78rem; opacity: .7; font-style: italic; margin: .3rem 0 0; }

/* CTA sidebar */
.avail-sidebar-box--cta { border-color: #7c3aed33; background: #faf5ff; }
.avail-sidebar-cta__desc { font-size: .82rem; opacity: .75; margin: 0 0 .75rem; }
.avail-sidebar-cta__btn {
    display: block; width: 100%; padding: .5rem;
    border-radius: .5rem; font-size: .85rem; font-weight: 600;
    text-align: center; text-decoration: none; border: none; cursor: pointer;
}
.avail-sidebar-cta__btn--on { background: #7c3aed; color: #fff; }
.avail-sidebar-cta__btn--off { background: #fee2e2; color: #dc2626; }

/* Como funciona */
.avail-sidebar-how { font-size: .82rem; opacity: .75; padding-left: 1.1rem; margin: 0; }
.avail-sidebar-how li { margin-bottom: .4rem; }

/* Empty */
.avail-page__empty { text-align: center; padding: 3rem 1rem; opacity: .6; }

/* Paginacion */
.avail-page__pagination { margin-top: 1.5rem; }
</style>
@endpush
@endsection