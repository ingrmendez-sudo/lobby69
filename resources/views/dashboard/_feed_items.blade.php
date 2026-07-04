@foreach($feed as $photo)
<div class="l69-feed-card"
     data-photo-id="{{ $photo->id }}"
     style="cursor:pointer;">

    <div class="l69-feed-card__img-wrap">
        <img src="/foto/{{ $photo->file_path }}"
             alt="{{ $photo->caption ?? 'Foto' }}"
             class="l69-feed-card__img"
             loading="lazy"
             onerror="this.closest('.l69-feed-card').style.display='none'">

        {{-- Avatar + nombre sobre la imagen --}}
        @php
            $ownerNick   = $photo->nickname    ?? null;
            $ownerName   = $photo->display_name ?? $photo->username ?? 'Usuario';
            $ownerAvatar = $photo->avatar_path
                ? '/foto/' . ltrim($photo->avatar_path, '/')
                : asset('img/default-avatar.svg');
            $ownerUrl    = $ownerNick ? route('profile.show', $ownerNick) : '#';
        @endphp

        <a href="{{ $ownerUrl }}"
           class="l69-feed-card__owner-top"
           onclick="event.stopPropagation()">
            <img src="{{ $ownerAvatar }}"
                 alt="{{ $ownerName }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span>{{ $ownerName }}</span>
        </a>

        {{-- Overlay likes/comentarios --}}
        <div class="l69-feed-card__overlay">
            <button class="l69-like-btn {{ ($photo->userLiked ?? false) ? 'is-liked' : '' }}"
                    data-photo-id="{{ $photo->id }}"
                    onclick="event.stopPropagation(); handleLike(this)">
                <i class="{{ ($photo->userLiked ?? false) ? 'fas' : 'far' }} fa-heart"
                   style="{{ ($photo->userLiked ?? false) ? 'color:#e056a0;' : 'color:#fff;' }}"></i>
                <span class="like-count" style="color:#fff;">{{ $photo->likes_count ?? 0 }}</span>
            </button>
            <span class="l69-feed-card__comments">
                <i class="far fa-comment"></i>
                {{ $photo->comments_count ?? 0 }}
            </span>
        </div>
    </div>

    {{-- Footer --}}
    <div style="padding:.5rem .75rem .6rem; display:flex; align-items:center; justify-content:space-between;">
        <a href="{{ $ownerUrl }}"
           style="font-size:.82rem; font-weight:600; color:var(--theme-text); text-decoration:none;"
           onclick="event.stopPropagation()">
            {{ $ownerName }}
            @if(!empty($photo->verified_profile))
                <i class="fas fa-check-circle" style="color:#6C3FC5; font-size:.7rem;"></i>
            @endif
        </a>
        <span style="font-size:.72rem; color:var(--theme-muted);">
            {{ \Carbon\Carbon::parse($photo->created_at)->diffForHumans() }}
        </span>
    </div>

</div>
@endforeach
