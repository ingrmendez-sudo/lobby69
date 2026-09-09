@extends('layouts.app')

@section('title', 'Historias')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/stories.css') }}">
@endpush

{{-- ═══ SIDEBAR IZQUIERDO ═══ --}}
@push('sidebar-left')
@php
    $stSUser  = auth()->user();
    $stAvatar = null;
    try {
        $stAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
            ->whereRaw('user_id::text = ?', [$stSUser->id])
            ->whereRaw('is_profile_photo = true')
            ->whereRaw('status = \'approved\'')
            ->first();
        if (!$stAvatarPhoto) {
            $stAvatarPhoto = \Illuminate\Support\Facades\DB::table('photos')
                ->whereRaw('user_id::text = ?', [$stSUser->id])
                ->whereRaw('album_type = \'public\'')
                ->whereRaw('status = \'approved\'')
                ->orderBy('sort_order')
                ->first();
        }
        $stAvatar = $stAvatarPhoto
            ? route('photos.serve', $stAvatarPhoto->id)
            : null;
    } catch(\Exception $e) {
        $stAvatar = null;
    }
    $stInitials = strtoupper(substr($stSUser->name ?? $stSUser->username ?? 'U', 0, 2));
    $stFallback = 'https://ui-avatars.com/api/?name='.urlencode($stSUser->name ?? $stSUser->username ?? 'U').'&background=e91e8c&color=fff&size=128&bold=true';
@endphp

<div class="l69-sidebar-card">
    @auth
    <div class="l69-mini-profile">
        <div class="profile-avatar-wrapper" style="width:72px;height:72px;border-radius:50%;overflow:hidden;margin:0 auto 10px;border:3px solid #e91e8c;background:linear-gradient(135deg,#e91e8c,#9c27b0);display:flex;align-items:center;justify-content:center;">
            @if($stAvatar)
                <img src="{{ $stAvatar }}"
                     alt="{{ $stSUser->name }}"
                     style="width:100%;height:100%;object-fit:cover;display:block;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <span style="display:none;color:#fff;font-weight:700;font-size:1.3rem;align-items:center;justify-content:center;width:100%;height:100%;">{{ $stInitials }}</span>
            @else
                <span style="color:#fff;font-weight:700;font-size:1.3rem;">{{ $stInitials }}</span>
            @endif
        </div>

        <div class="l69-mini-profile__nick">{{ $stSUser->name }}</div>
        <div class="l69-mini-profile__type">
            {{ $stSUser->username ?? 'usuario' }}
        </div>
    </div>

    <div class="stories-profile-stats" style="margin-bottom:1rem">
        <div class="stories-stat">
            <span class="stories-stat__number">
                {{ \App\Models\Story::byUser(Auth::id())->published()->count() }}
            </span>
            <span class="stories-stat__label">Publicadas</span>
        </div>
        <div class="stories-stat">
            <span class="stories-stat__number">
                {{ number_format(\App\Models\Story::byUser(Auth::id())->sum('views')) }}
            </span>
            <span class="stories-stat__label">Lecturas</span>
        </div>
        <div class="stories-stat">
            <span class="stories-stat__number">
                {{ \App\Models\Story::byUser(Auth::id())->where('status','draft')->count() }}
            </span>
            <span class="stories-stat__label">Borradores</span>
        </div>
    </div>

    <a href="{{ route('stories.create') }}" class="l69-quick-btn">
        <i class="fas fa-pen"></i> Escribir historia
    </a>
    <a href="{{ route('stories.my') }}" class="l69-quick-btn">
        <i class="fas fa-book"></i> Mis historias
    </a>
    @else
    <div style="text-align:center;padding:.5rem 0">
        <div style="font-size:2rem;margin-bottom:.5rem">📖</div>
        <p style="font-size:.82rem;color:var(--theme-text-muted,rgba(226,217,243,.5));margin-bottom:.75rem">
            Únete y comparte tus historias
        </p>
        <a href="{{ route('login') }}" class="l69-quick-btn" style="justify-content:center">
            <i class="fas fa-sign-in-alt"></i> Iniciar sesión
        </a>
    </div>
    @endauth
</div>

{{-- Categorías --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">📂 Categorías</div>
    <ul class="l69-sidebar-nav">
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('stories.index') }}"
               class="{{ !$category ? 'is-active' : '' }}">
                <i class="fas fa-th-large"></i> Todas
            </a>
        </li>
        @foreach($categories as $key => $label)
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('stories.index', ['category' => $key]) }}"
               class="{{ $category === $key ? 'is-active' : '' }}">
                <i class="fas fa-tag"></i> {{ $label }}
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endpush

{{-- ═══ CONTENIDO CENTRAL ═══ --}}
@section('content')

@if(session('success'))
<div class="toast toast--success" style="position:relative;top:0;right:0;margin-bottom:1rem;animation:none">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
    <h2 style="font-size:1.1rem;font-weight:700;color:var(--theme-text);margin:0">
        {{ $category ? ($categories[$category] ?? 'Historias') : 'Todas las historias' }}
    </h2>
    @auth
    <a href="{{ route('stories.create') }}"
       style="padding:.45rem .9rem;background:rgba(180,60,120,.15);border:1px solid rgba(180,60,120,.3);
              border-radius:8px;color:#e056a0;font-size:.8rem;font-weight:600;text-decoration:none;
              transition:all .2s"
       onmouseover="this.style.background='rgba(180,60,120,.25)'"
       onmouseout="this.style.background='rgba(180,60,120,.15)'">
        ✍️ Nueva historia
    </a>
    @endauth
</div>

@if($stories->isEmpty())
<div class="stories-empty">
    <span class="stories-empty-icon">📝</span>
    <p>No hay historias en esta categoría todavía.</p>
    @auth
    <a href="{{ route('stories.create') }}" class="stories-btn-write"
       style="display:inline-block;width:auto;padding:.6rem 1.5rem;margin-top:.5rem">
        Sé el primero en escribir
    </a>
    @endauth
</div>
@else
<div class="stories-feed">
    @foreach($stories as $story)
    @php
        $cardAvatar = null;
        try {
            $cardPhoto = \Illuminate\Support\Facades\DB::table('photos')
                ->whereRaw('user_id::text = ?', [$story->user_id])
                ->whereRaw('is_profile_photo = true')
                ->whereRaw('status = \'approved\'')
                ->first();
            if (!$cardPhoto) {
                $cardPhoto = \Illuminate\Support\Facades\DB::table('photos')
                    ->whereRaw('user_id::text = ?', [$story->user_id])
                    ->whereRaw('album_type = \'public\'')
                    ->whereRaw('status = \'approved\'')
                    ->orderBy('sort_order')
                    ->first();
            }
            $cardAvatar = $cardPhoto ? route('photos.serve', $cardPhoto->id) : null;
        } catch(\Exception $e) {
            $cardAvatar = null;
        }
        $cardInitials = strtoupper(substr($story->author->name ?? $story->author->username ?? 'U', 0, 2));
    @endphp
    <article class="stories-card" onclick="window.location='{{ route('stories.show', $story->slug) }}'">
        @if($story->cover_image)
        <div class="stories-card__cover">
            <img src="{{ Storage::url($story->cover_image) }}"
                 alt="{{ $story->title }}" loading="lazy">
        </div>
        @endif
        <div class="stories-card__body">
            <div class="stories-card__meta">
                <span class="stories-card__cat stories-cat--{{ $story->category }}">
                    {{ $categories[$story->category] ?? $story->category }}
                </span>
                <span class="stories-card__time">⏱ {{ $story->reading_time }} min</span>
                <span class="stories-card__views">👁 {{ number_format($story->views) }}</span>
            </div>
            <h3 class="stories-card__title">{{ $story->title }}</h3>
            <p class="stories-card__excerpt">{{ $story->excerpt }}</p>
            <div class="stories-card__footer">
                <div class="stories-card__author">
                    <div style="width:32px;height:32px;border-radius:50%;overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,#e91e8c,#9c27b0);display:flex;align-items:center;justify-content:center;">
                        @if($cardAvatar)
                            <img src="{{ $cardAvatar }}"
                                 alt="{{ $story->author->name ?? '' }}"
                                 style="width:100%;height:100%;object-fit:cover;display:block;"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                                 loading="lazy">
                            <span style="display:none;color:#fff;font-size:.7rem;font-weight:700;align-items:center;justify-content:center;width:100%;height:100%;">{{ $cardInitials }}</span>
                        @else
                            <span style="color:#fff;font-size:.7rem;font-weight:700;">{{ $cardInitials }}</span>
                        @endif
                    </div>
                    <span>{{ $story->author->name ?? $story->author->username }}</span>
                </div>
                <span class="stories-card__date">{{ $story->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </article>
    @endforeach
</div>

<div class="stories-pagination">
    {{ $stories->links('pagination::simple-tailwind') }}
</div>
@endif
@endsection

{{-- ═══ SIDEBAR DERECHO ═══ --}}
@push('sidebar-right')

{{-- Últimas historias --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">🆕 Últimas historias</div>
    @forelse($latestStories as $s)
    <a href="{{ route('stories.show', $s->slug) }}" class="stories-widget__item">
        <div class="stories-widget__item-info">
            <span class="stories-widget__item-title">{{ Str::limit($s->title, 42) }}</span>
            <span class="stories-widget__item-author">{{ $s->author->name }}</span>
        </div>
        <span class="stories-widget__item-time">{{ $s->created_at->diffForHumans(null, true) }}</span>
    </a>
    @empty
    <p style="font-size:.78rem;color:var(--theme-text-muted,rgba(226,217,243,.4));text-align:center;padding:.5rem 0">
        Sin historias aún
    </p>
    @endforelse
</div>

{{-- Más vistas --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">🔥 Más vistas</div>
    @forelse($mostViewed as $i => $s)
    <a href="{{ route('stories.show', $s->slug) }}" class="stories-widget__item">
        <span class="stories-widget__rank">{{ $i + 1 }}</span>
        <div class="stories-widget__item-info">
            <span class="stories-widget__item-title">{{ Str::limit($s->title, 38) }}</span>
            <span class="stories-widget__item-views">👁 {{ number_format($s->views) }}</span>
        </div>
    </a>
    @empty
    <p style="font-size:.78rem;color:var(--theme-text-muted,rgba(226,217,243,.4));text-align:center;padding:.5rem 0">
        Sin historias aún
    </p>
    @endforelse
</div>

{{-- Recomendadas --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">⭐ Recomendadas</div>
    @forelse($recommended as $s)
    <a href="{{ route('stories.show', $s->slug) }}" class="stories-widget__item stories-widget__item--rec">
        @if($s->cover_image)
        <img src="{{ Storage::url($s->cover_image) }}" alt="{{ $s->title }}" loading="lazy"
             style="width:40px;height:40px;border-radius:6px;object-fit:cover;flex-shrink:0">
        @else
        <div style="width:40px;height:40px;border-radius:6px;background:rgba(180,60,120,.1);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem">
            📖
        </div>
        @endif
        <div class="stories-widget__item-info">
            <span class="stories-widget__item-title">{{ Str::limit($s->title, 32) }}</span>
            <span class="stories-cat--{{ $s->category }}" style="font-size:.7rem">
                {{ $categories[$s->category] ?? $s->category }}
            </span>
        </div>
    </a>
    @empty
    <p style="font-size:.78rem;color:var(--theme-text-muted,rgba(226,217,243,.4));text-align:center;padding:.5rem 0">
        Sin historias aún
    </p>
    @endforelse
</div>
@endpush
