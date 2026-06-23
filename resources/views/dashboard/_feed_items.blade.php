@foreach($feed as $photo)
@php
    $owner   = $photo->user?->profile;
    $ownerNick = $owner?->nickname ?? $photo->user?->name ?? 'Usuario';
    $ownerAvatar = $owner?->avatar_url ?? asset('img/default-avatar.svg');
    $isLiked = $photo->isLikedBy($user->id);
@endphp
<div class="l69-feed-card" data-photo-id="{{ $photo->id }}">
    {{-- Foto --}}
    <div class="l69-feed-card__img-wrap">
        <img src="{{ asset('storage/' . $photo->file_path) }}"
             alt="{{ $owner?->nickname ?? 'Foto' }}"
             class="l69-feed-card__img"
             loading="lazy"
             onerror="this.parentElement.style.background='#1a1028'">
        {{-- Overlay con acciones --}}
        <div class="l69-feed-card__overlay">
            <button class="l69-like-btn {{ $isLiked ? 'is-liked' : '' }}"
                    data-photo-id="{{ $photo->id }}">
                <i class="{{ $isLiked ? 'fas' : 'far' }} fa-heart"></i>
                <span>{{ $photo->likes_count }}</span>
            </button>
            <span class="l69-feed-card__comments">
                <i class="far fa-comment"></i>
                {{ $photo->comments_count }}
            </span>
        </div>
    </div>
    {{-- Footer de la tarjeta --}}
    <div class="l69-feed-card__footer">
        <a href="{{ $owner?->nickname ? route('profile.show', $owner->nickname) : '#' }}"
           class="l69-card__owner" title="Ver perfil de {{ $ownerNick }}">
            <img src="{{ $ownerAvatar }}"
                 alt="{{ $ownerNick }}"
                 class="l69-feed-card__owner-avatar"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span class="l69-feed-card__owner-nick">{{ $ownerNick }}</span>
        </a>
        @if($photo->caption)
        <p class="l69-feed-card__caption">{{ Str::limit($photo->caption, 60) }}</p>
        @endif
    </div>
</div>
@endforeach