@extends('layouts.app')

@section('title', $story->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stories.css') }}">
@endpush

{{-- ═══ SIDEBAR IZQUIERDO ═══ --}}
@push('sidebar-left')
<div class="l69-sidebar-card">
    <div class="l69-mini-profile">
        <div class="l69-mini-profile__avatar-wrap">
            {{-- Avatar del autor en el feed --}}
            <img 
                src="{{ $story->user->profile_photo_url }}" 
                alt="{{ $story->user->name }}"
                class="rounded-circle"
                style="width:32px; height:32px; object-fit:cover;"
                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($story->user->name ?? $story->user->username) }}&background=e91e8c&color=fff&size=128&bold=true'"
            >
        </div>
        <div class="l69-mini-profile__nick">{{ $story->author->name }}</div>
        <div class="l69-mini-profile__type">
            {{ $story->author->username ?? 'usuario' }}
        </div>
    </div>

    <div class="stories-profile-stats" style="margin-bottom:1rem">
        <div class="stories-stat">
            <span class="stories-stat__number">
                {{ \App\Models\Story::byUser($story->author->id)->published()->count() }}
            </span>
            <span class="stories-stat__label">Historias</span>
        </div>
        <div class="stories-stat">
            <span class="stories-stat__number">
                {{ number_format(\App\Models\Story::byUser($story->author->id)->sum('views')) }}
            </span>
            <span class="stories-stat__label">Lecturas</span>
        </div>
    </div>

    <a href="{{ route('stories.index') }}" class="l69-quick-btn">
        <i class="fas fa-arrow-left"></i> Todas las historias
    </a>

    @auth
    @if(Auth::id() === $story->user_id)
    <a href="{{ route('stories.edit', $story) }}" class="l69-quick-btn" style="margin-top:.25rem">
        <i class="fas fa-edit"></i> Editar historia
    </a>
    <form action="{{ route('stories.destroy', $story) }}" method="POST"
          onsubmit="return confirm('¿Eliminar esta historia?')" style="margin-top:.25rem">
        @csrf @method('DELETE')
        <button type="submit" class="l69-quick-btn"
                style="width:100%;color:#f87171;border-color:rgba(239,68,68,.2)">
            <i class="fas fa-trash"></i> Eliminar
        </button>
    </form>
    @endif
    @endauth
</div>

<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">📋 Detalles</div>
    <div class="stories-detail-item">
        <span>📂 Categoría</span>
        <span class="stories-cat--{{ $story->category }}">{{ ucfirst($story->category) }}</span>
    </div>
    <div class="stories-detail-item">
        <span>⏱ Lectura</span>
        <span>{{ $story->reading_time }} min</span>
    </div>
    <div class="stories-detail-item">
        <span>👁 Vistas</span>
        <span>{{ number_format($story->views) }}</span>
    </div>
    <div class="stories-detail-item">
        <span>📅 Publicada</span>
        <span>{{ $story->created_at->format('d M Y') }}</span>
    </div>
</div>
@endpush

{{-- ═══ CONTENIDO CENTRAL ═══ --}}
@section('content')
<article class="stories-article">
    @if($story->cover_image)
    <div class="stories-article__cover">
        <img src="{{ Storage::url($story->cover_image) }}"
             alt="{{ $story->title }}" loading="lazy">
    </div>
    @endif

    <header class="stories-article__header">
        <div class="stories-article__meta">
            <span class="stories-cat--{{ $story->category }} stories-cat-badge">
                {{ ucfirst($story->category) }}
            </span>
            <span>⏱ {{ $story->reading_time }} min de lectura</span>
            <span>👁 {{ number_format($story->views) }} lecturas</span>
        </div>
        <h1 class="stories-article__title">{{ $story->title }}</h1>
        <div class="stories-article__author">
            <img src="{{ $story->author->profile_photo_url ?? asset('images/default-avatar.png') }}"
                 alt="{{ $story->author->name }}" loading="lazy">
            <div>
                <strong>{{ $story->author->name }}</strong>
                <span>{{ $story->created_at->format('d \d\e F \d\e Y') }}</span>
            </div>
        </div>
    </header>

    <div class="stories-article__content">
        {!! $story->content !!}
    </div>

    <footer class="stories-article__footer">
        <span class="stories-cat--{{ $story->category }} stories-cat-badge">
            {{ ucfirst($story->category) }}
        </span>
        <span>{{ $story->created_at->diffForHumans() }}</span>
    </footer>
</article>

{{-- Relacionadas --}}
@if($related->isNotEmpty())
<section style="margin-top:1.25rem">
    <h3 style="font-size:.95rem;font-weight:700;color:var(--theme-text);margin-bottom:.75rem">
        📚 Historias relacionadas
    </h3>
    <div class="stories-related-grid">
        @foreach($related as $r)
        <a href="{{ route('stories.show', $r->slug) }}" class="stories-related-card">
            @if($r->cover_image)
            <img src="{{ Storage::url($r->cover_image) }}" alt="{{ $r->title }}" loading="lazy">
            @else
            <div class="stories-related-card__placeholder">📖</div>
            @endif
            <div class="stories-related-card__info">
                <span class="stories-related-card__title">{{ Str::limit($r->title, 50) }}</span>
                <span class="stories-related-card__author">{{ $r->author->name }}</span>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif
@endsection

{{-- ═══ SIDEBAR DERECHO ═══ --}}
@push('sidebar-right')
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">🔥 Más vistas</div>
    @foreach(\App\Models\Story::published()->with('author')->orderByDesc('views')->take(6)->get() as $i => $s)
    <a href="{{ route('stories.show', $s->slug) }}" class="stories-widget__item">
        <span class="stories-widget__rank">{{ $i + 1 }}</span>
        <div class="stories-widget__item-info">
            <span class="stories-widget__item-title">{{ Str::limit($s->title, 36) }}</span>
            <span class="stories-widget__item-views">👁 {{ number_format($s->views) }}</span>
        </div>
    </a>
    @endforeach
</div>

<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">🆕 Recientes</div>
    @foreach(\App\Models\Story::published()->with('author')->latest()->take(5)->get() as $s)
    <a href="{{ route('stories.show', $s->slug) }}" class="stories-widget__item">
        <div class="stories-widget__item-info">
            <span class="stories-widget__item-title">{{ Str::limit($s->title, 40) }}</span>
            <span class="stories-widget__item-author">{{ $s->author->name }}</span>
        </div>
    </a>
    @endforeach
</div>

@auth
<div class="l69-sidebar-card" style="text-align:center">
    <div class="l69-sidebar-card__title">✍️ Escribe tú también</div>
    <p style="font-size:.8rem;color:var(--theme-text-muted,rgba(226,217,243,.5));margin-bottom:.75rem">
        ¿Tienes una historia que contar?
    </p>
    <a href="{{ route('stories.create') }}" class="l69-quick-btn" style="justify-content:center">
        <i class="fas fa-pen"></i> Escribir ahora
    </a>
</div>
@endauth
@endpush
