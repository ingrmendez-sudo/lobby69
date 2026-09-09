@extends('layouts.app')

@section('title', 'Mis historias')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stories.css') }}">
@endpush

@section('content')
<div class="l69-layout stories-layout">

    {{-- COLUMNA IZQUIERDA --}}
    <aside class="l69-sidebar--left stories-sidebar-left">
        <div class="stories-profile-card">
            <div class="stories-profile-avatar">
                <img src="{{ Auth::user()->profile_photo_url ?? asset('images/default-avatar.png') }}"
                     alt="{{ Auth::user()->name }}" loading="lazy">
            </div>
            <h3 class="stories-profile-name">{{ Auth::user()->name }}</h3>
            <p class="stories-profile-handle">@{{ Auth::user()->username ?? 'usuario' }}</p>
            <div class="stories-profile-stats">
                <div class="stories-stat">
                    <span class="stories-stat__number">
                        {{ \App\Models\Story::byUser(Auth::id())->published()->count() }}
                    </span>
                    <span class="stories-stat__label">Publicadas</span>
                </div>
                <div class="stories-stat">
                    <span class="stories-stat__number">
                        {{ \App\Models\Story::byUser(Auth::id())->where('status','draft')->count() }}
                    </span>
                    <span class="stories-stat__label">Borradores</span>
                </div>
                <div class="stories-stat">
                    <span class="stories-stat__number">
                        {{ number_format(\App\Models\Story::byUser(Auth::id())->sum('views')) }}
                    </span>
                    <span class="stories-stat__label">Lecturas</span>
                </div>
            </div>
            <a href="{{ route('stories.create') }}" class="stories-btn-write">
                ✍️ Nueva historia
            </a>
            <a href="{{ route('stories.index') }}" class="stories-btn-secondary">
                📚 Ver todas
            </a>
        </div>
    </aside>

    {{-- COLUMNA CENTRAL --}}
    <main class="l69-layout__content stories-main">

        @if(session('success'))
        <div class="stories-alert stories-alert--success">{{ session('success') }}</div>
        @endif

        <div class="stories-header">
            <h2 class="stories-page-title">📚 Mis historias</h2>
            <a href="{{ route('stories.create') }}" class="stories-btn-write"
               style="width:auto;padding:.5rem 1rem;display:inline-block">
                + Nueva
            </a>
        </div>

        @if($stories->isEmpty())
        <div class="stories-empty">
            <span class="stories-empty-icon">✍️</span>
            <p>Aún no has escrito ninguna historia.</p>
            <a href="{{ route('stories.create') }}" class="stories-btn-write"
               style="display:inline-block;width:auto;padding:.6rem 1.5rem;margin-top:.5rem">
                Escribe tu primera historia
            </a>
        </div>
        @else

        {{-- Tabla de historias --}}
        <div class="stories-my-table">
            @foreach($stories as $story)
            <div class="stories-my-row">
                {{-- Portada mini --}}
                <div class="stories-my-row__cover">
                    @if($story->cover_image)
                    <img src="{{ Storage::url($story->cover_image) }}"
                         alt="{{ $story->title }}" loading="lazy">
                    @else
                    <div class="stories-my-row__placeholder">📖</div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="stories-my-row__info">
                    <a href="{{ route('stories.show', $story->slug) }}"
                       class="stories-my-row__title">
                        {{ $story->title }}
                    </a>
                    <div class="stories-my-row__meta">
                        <span class="stories-cat--{{ $story->category }}">
                            {{ ucfirst($story->category) }}
                        </span>
                        <span>⏱ {{ $story->reading_time }} min</span>
                        <span>👁 {{ number_format($story->views) }}</span>
                        <span>{{ $story->created_at->format('d M Y') }}</span>
                    </div>
                    <p class="stories-my-row__excerpt">{{ $story->excerpt }}</p>
                </div>

                {{-- Estado + acciones --}}
                <div class="stories-my-row__actions">
                    <span class="stories-my-row__status stories-my-status--{{ $story->status }}">
                        {{ $story->status === 'published' ? '✅ Publicada' : '📝 Borrador' }}
                    </span>
                    <div class="stories-my-row__btns">
                        <a href="{{ route('stories.edit', $story) }}"
                           class="stories-my-btn stories-my-btn--edit">
                            ✏️ Editar
                        </a>
                        <form action="{{ route('stories.destroy', $story) }}"
                              method="POST" style="display:inline"
                              onsubmit="return confirm('¿Eliminar {{ addslashes($story->title) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="stories-my-btn stories-my-btn--delete">
                                🗑
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="stories-pagination">
            {{ $stories->links('pagination::simple-tailwind') }}
        </div>
        @endif
    </main>

    {{-- COLUMNA DERECHA --}}
    <aside class="l69-sidebar--right stories-sidebar-right">
        <div class="stories-widget">
            <h4 class="stories-widget__title">📈 Tus estadísticas</h4>
            <div class="stories-progress">
                <div class="stories-progress__item">
                    <span class="stories-progress__label">Total historias</span>
                    <span class="stories-progress__val">
                        {{ \App\Models\Story::byUser(Auth::id())->count() }}
                    </span>
                </div>
                <div class="stories-progress__item">
                    <span class="stories-progress__label">Total lecturas</span>
                    <span class="stories-progress__val">
                        {{ number_format(\App\Models\Story::byUser(Auth::id())->sum('views')) }}
                    </span>
                </div>
                <div class="stories-progress__item">
                    <span class="stories-progress__label">Historia más vista</span>
                    <span class="stories-progress__val">
                        @php
                            $top = \App\Models\Story::byUser(Auth::id())
                                ->orderByDesc('views')->first();
                        @endphp
                        {{ $top ? number_format($top->views) . ' 👁' : '—' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="stories-widget">
            <h4 class="stories-widget__title">🔥 Más vistas globales</h4>
            @foreach(\App\Models\Story::published()->with('author')->orderByDesc('views')->take(5)->get() as $i => $s)
            <a href="{{ route('stories.show', $s->slug) }}" class="stories-widget__item">
                <span class="stories-widget__rank">{{ $i + 1 }}</span>
                <div class="stories-widget__item-info">
                    <span class="stories-widget__item-title">{{ Str::limit($s->title, 38) }}</span>
                    <span class="stories-widget__item-views">👁 {{ number_format($s->views) }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </aside>

</div>
@endsection
