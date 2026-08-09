@extends('layouts.app')

@section('title', 'Disponibles ahora ÔÇö LOBBY69')

@section('content')
<div class="avail-page">

    {{-- ÔöÇÔöÇ Header ÔöÇÔöÇ --}}
    <div class="avail-page__header">
        <div class="avail-page__header-left">
            <span class="avail-page__pulse"></span>
            <h1 class="avail-page__title">Disponibles ahora</h1>
            <span class="avail-page__total">{{ $total }} {{ $total === 1 ? 'persona' : 'personas' }}</span>
        </div>
        <a href="{{ route('dashboard') }}" class="avail-page__back">
            ÔåÉ Volver
        </a>
    </div>

    {{-- ÔöÇÔöÇ Filtros ÔöÇÔöÇ --}}
    <form method="GET" action="{{ route('availability.public') }}" class="avail-page__filters" id="filterForm">
        <div class="avail-page__search-wrap">
            <input type="text" name="q" value="{{ $search }}"
                   placeholder="Buscar por nick o ciudadÔÇª"
                   class="avail-page__search">
        </div>
        <div class="avail-page__slots">
            <button type="submit" name="slot" value=""
                    class="avail-filter-btn {{ !$slotFilter ? 'is-active' : '' }}">
                Todos
            </button>
            @foreach($slotLabels as $key => $meta)
            <button type="submit" name="slot" value="{{ $key }}"
                    class="avail-filter-btn {{ $slotFilter === $key ? 'is-active' : '' }}">
                {{ $meta['icon'] }} {{ $meta['label'] }}
            </button>
            @endforeach
        </div>
    </form>

    {{-- ÔöÇÔöÇ Grid de usuarios ÔöÇÔöÇ --}}
    @if($available->isEmpty())
        <div class="avail-page__empty">
            <span style="font-size:2.5rem">­ƒÿ┤</span>
            <p>Nadie disponible en este momento.</p>
            <p style="font-size:.85rem;opacity:.6">Vuelve m├ís tarde o activa tu propia disponibilidad.</p>
        </div>
    @else
    <div class="avail-page__grid">
        @foreach($available as $u)
        @php
            $avatarUrl  = supabase_photo_url($u->avatar_path ?? null) ?? asset('img/default-avatar.svg');
            $profileUrl = $u->nickname ? route('profile.show', $u->nickname) : '#';
            $mins       = max(0, (int) now()->diffInMinutes(\Carbon\Carbon::parse($u->expires_at), false));
            $hrs        = floor($mins / 60);
            $rem        = $mins % 60;
            $timeLabel  = $mins < 60 ? "{$mins}min" : ($rem > 0 ? "{$hrs}h {$rem}m" : "{$hrs}h");
            $slotMeta   = $slotLabels[$u->slot] ?? ['icon' => '­ƒôà', 'label' => $u->slot];
        @endphp
        <a href="{{ $profileUrl }}" class="avail-ucard">
            <div class="avail-ucard__img-wrap">
                <img src="{{ $avatarUrl }}"
                     alt="{{ ($u->nickname ?? $u->name) }}"
                     class="avail-ucard__img"
                     loading="lazy"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                <span class="avail-ucard__online-dot"></span>
                @if($u->verified_profile)
                    <span class="avail-ucard__verified" title="Verificado">Ô£ô</span>
                @endif
            </div>
            <div class="avail-ucard__body">
                <p class="avail-ucard__name">{{ Str::limit(($u->nickname ?? $u->name), 18) }}</p>
                @if($u->nickname)
                    <p class="avail-ucard__nick">@{{ $u->nickname }}</p>
                @endif
                @if($u->city)
                    <p class="avail-ucard__city">­ƒôì {{ $u->city }}</p>
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

    {{-- ÔöÇÔöÇ Paginaci├│n ÔöÇÔöÇ --}}
    @if($available->hasPages())
    <div class="avail-page__pagination">
        {{ $available->links() }}
    </div>
    @endif
    @endif

</div>

<style>
/* ÔöÇÔöÇ P├ígina Disponibles ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
.avail-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.5rem 1rem 3rem;
}
.avail-page__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: 1rem;
}
.avail-page__header-left {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.avail-page__pulse {
    width: 12px; height: 12px;
    background: #16a34a;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(22,163,74,.6);
    animation: avail-pulse 1.6s ease-in-out infinite;
    flex-shrink: 0;
}
@keyframes avail-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(22,163,74,.6); }
    70%  { box-shadow: 0 0 0 8px rgba(22,163,74,0); }
    100% { box-shadow: 0 0 0 0 rgba(22,163,74,0); }
}
.avail-page__title {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--_text);
    margin: 0;
}
.avail-page__total {
    font-size: .8rem;
    background: rgba(22,163,74,.12);
    color: #16a34a;
    border: 1px solid rgba(22,163,74,.25);
    padding: .2rem .6rem;
    border-radius: 99px;
    font-weight: 600;
}
.avail-page__back {
    font-size: .85rem;
    color: var(--_muted);
    text-decoration: none;
    padding: .4rem .8rem;
    border: 1px solid var(--_border);
    border-radius: 8px;
    transition: background .15s;
    white-space: nowrap;
}
.avail-page__back:hover { background: var(--_hover); }

/* ÔöÇÔöÇ Filtros ÔöÇÔöÇ */
.avail-page__filters {
    display: flex;
    flex-direction: column;
    gap: .75rem;
    margin-bottom: 1.75rem;
}
.avail-page__search-wrap { width: 100%; max-width: 360px; }
.avail-page__search {
    width: 100%;
    padding: .55rem .9rem;
    border: 1px solid var(--_border);
    border-radius: 10px;
    background: var(--_input-bg, var(--_bg));
    color: var(--_text);
    font-size: .9rem;
    outline: none;
    transition: border-color .15s;
}
.avail-page__search:focus { border-color: #16a34a; }
.avail-page__slots {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.avail-filter-btn {
    padding: .35rem .8rem;
    border: 1px solid var(--_border);
    border-radius: 99px;
    background: var(--_card-bg, var(--_bg));
    color: var(--_text);
    font-size: .8rem;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.avail-filter-btn:hover  { border-color: #16a34a; color: #16a34a; }
.avail-filter-btn.is-active {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
    font-weight: 600;
}

/* ÔöÇÔöÇ Grid ÔöÇÔöÇ */
.avail-page__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 1rem;
}
.avail-ucard {
    display: flex;
    flex-direction: column;
    background: var(--_card-bg, var(--_bg));
    border: 1px solid var(--_border);
    border-radius: 14px;
    overflow: hidden;
    text-decoration: none;
    color: var(--_text);
    transition: transform .18s, box-shadow .18s;
}
.avail-ucard:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    border-color: rgba(22,163,74,.4);
}
.avail-ucard__img-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 1;
    background: var(--_border);
    overflow: hidden;
}
.avail-ucard__img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.avail-ucard__online-dot {
    position: absolute;
    bottom: 8px; right: 8px;
    width: 12px; height: 12px;
    background: #16a34a;
    border-radius: 50%;
    border: 2px solid var(--_card-bg, #fff);
}
.avail-ucard__verified {
    position: absolute;
    top: 8px; right: 8px;
    background: #3b82f6;
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    width: 18px; height: 18px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.avail-ucard__body {
    padding: .75rem .8rem;
    display: flex;
    flex-direction: column;
    gap: .2rem;
    flex: 1;
}
.avail-ucard__name {
    font-weight: 700;
    font-size: .92rem;
    margin: 0;
    color: var(--_text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.avail-ucard__nick {
    font-size: .75rem;
    color: var(--_muted);
    margin: 0;
}
.avail-ucard__city {
    font-size: .75rem;
    color: var(--_muted);
    margin: 0;
}
.avail-ucard__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: .35rem;
    gap: .4rem;
}
.avail-ucard__slot {
    font-size: .72rem;
    background: rgba(22,163,74,.1);
    color: #16a34a;
    border-radius: 99px;
    padding: .15rem .5rem;
    font-weight: 600;
    white-space: nowrap;
}
.avail-ucard__time {
    font-size: .7rem;
    color: var(--_muted);
    white-space: nowrap;
}
.avail-ucard__msg {
    font-size: .75rem;
    color: var(--_muted);
    margin: .25rem 0 0;
    font-style: italic;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* ÔöÇÔöÇ Empty state ÔöÇÔöÇ */
.avail-page__empty {
    text-align: center;
    padding: 4rem 1rem;
    color: var(--_muted);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
}

/* ÔöÇÔöÇ Paginaci├│n ÔöÇÔöÇ */
.avail-page__pagination {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

/* ÔöÇÔöÇ Responsive ÔöÇÔöÇ */
@media (max-width: 640px) {
    .avail-page__grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
    .avail-page__header { flex-direction: column; align-items: flex-start; }
}
</style>

{{-- Auto-refresh cada 60 segundos --}}
<script>
    setTimeout(function() { window.location.reload(); }, 60000);
</script>
@endsection
