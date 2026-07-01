@foreach($feed as $photo)
{{-- DEBUG TEMPORAL --}}
<!-- photo_id={{ $photo->id }} photo_keys={{ implode(',', array_keys($photo->toArray())) }} -->
@php
    $owner       = $photo->user?->profile;
    $ownerNick   = $owner?->nickname ?? $photo->user?->name ?? 'Usuario';
    $ownerAvatar = $owner?->avatar_url
                ? url('foto/' . $owner->avatar_url)
                : asset('img/default-avatar.svg');
    $isLiked     = $photo->userLiked ?? false;
@endphp
<div class="l69-feed-card" data-photo-id="{{ $photo->id }}">

    <div class="l69-feed-card__img-wrap">

        {{-- Foto --}}
        <img src="{{ url('foto/' . $photo->file_path) }}"
             alt="{{ $ownerNick }}"
             class="l69-feed-card__img"
             loading="lazy"
             onerror="this.parentElement.style.background='#1a1028'">

        {{-- Dueño en esquina superior izquierda --}}
        <a href="{{ $owner?->nickname ? route('profile.show', $owner->nickname) : '#' }}"
           class="l69-feed-card__owner-top"
           title="Ver perfil de {{ $ownerNick }}"
           onclick="event.stopPropagation()">
            <img src="{{ $ownerAvatar }}"
                 alt="{{ $ownerNick }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span>{{ $ownerNick }}</span>
        </a>

        {{-- Overlay con like y comentario --}}
        <div class="l69-feed-card__overlay">
            <button class="l69-like-btn {{ $isLiked ? 'is-liked' : '' }}"
                    data-photo-id="{{ $photo->id }}"
                    onclick="event.stopPropagation()">
                <i class="{{ $isLiked ? 'fas' : 'far' }} fa-heart"></i>
                <span>{{ $photo->likes_count ?? 0 }}</span>
            </button>
            <span class="l69-feed-card__comments">
                <i class="far fa-comment"></i>
                {{ $photo->comments_count ?? 0 }}
            </span>
        </div>

    </div>{{-- fin img-wrap --}}

</div>
@endforeach
