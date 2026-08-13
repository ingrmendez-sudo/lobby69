@php
    $avatarPath = $photo->avatar_path ?? null;
    $avatarUrl  = $avatarPath
        ? '/foto/' . ltrim($avatarPath, '/')
        : asset('img/default-avatar.svg');
    $profileUrl = !empty($photo->nickname)
        ? route('profile.show', $photo->nickname)
        : '#';
    $authorName = $photo->display_name ?? $photo->username ?? 'Usuario';
    $liked      = $isLiked ?? false;
    $likesCount = $photo->likes_count ?? 0;
    $photoScore     = (float)($photo->recommendation_score ?? 0);
    $photoFullStars = (int)floor($photoScore);
    $photoHalfStar  = ($photoScore - $photoFullStars) >= 0.5;

    $commCount  = $photo->comments_count ?? 0;
@endphp

<div class="l69-feed-card"
     data-photo-id="{{ $photo->id }}"
     style="cursor:pointer;">

    {{-- Imagen principal --}}
    <div class="l69-feed-card__img-wrap">
        <img loading="lazy" src="{{ $photo->thumbnail_path ? asset('storage/' . $photo->thumbnail_path) : route('photos.serve', $photo->id) }}"
             alt="{{ $photo->caption ?? 'Foto' }}"
             class="l69-feed-card__img"
             loading="lazy"
             onerror="this.closest('.l69-feed-card').style.display='none'">

        {{-- Avatar encima de la imagen (top-left) --}}
        <a href="{{ $profileUrl }}"
           class="l69-feed-card__owner-top"
           onclick="event.stopPropagation()">
            <img loading="lazy" src="{{ $avatarUrl }}"
                 alt="{{ $authorName }}"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span>{{ $authorName }}</span>
        </a>

        {{-- Overlay con likes y comentarios --}}
        <div class="l69-feed-card__overlay">
            <button class="l69-like-btn {{ $liked ? 'is-liked' : '' }}"
                    data-photo-id="{{ $photo->id }}"
                    onclick="event.stopPropagation(); handleLike(this)">
                <i class="{{ $liked ? 'fas' : 'far' }} fa-heart"
                   style="{{ $liked ? 'color:#e056a0;' : 'color:#fff;' }}"></i>
                <span class="like-count" style="color:#fff;">{{ $likesCount }}</span>
            </button>

            <span class="l69-feed-card__comments">
                <i class="far fa-comment"></i>
                {{ $commCount }}
            </span>
        </div>
    </div>

    {{-- Footer con tipo de perfil (debajo de la imagen) --}}
    @php
        $profileTypeLabel = match($photo->profile_type ?? null) {
            'pareja'    => '👫 Pareja',
            'single'    => '🙋 Single',
            'unicornio' => '🦄 Unicornio',
            default      => '👤 Perfil',
        };
    @endphp
    <div style="padding:.5rem .75rem .6rem; display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:.78rem; font-weight:600; color:var(--theme-pink);">
            {{ $profileTypeLabel }}
            @if(!empty($photo->verified_profile))
                <i class="fas fa-check-circle" style="color:#6C3FC5;font-size:.7rem;margin-left:.2rem;"></i>
            @endif
        </span>
        {{-- Score estrellas --}}        @if($photoScore > 0)
        <span style="display:inline-flex;align-items:center;gap:.05rem;font-size:.7rem;">
            @for($si=0;$si<$photoFullStars;$si++)<i class="fas fa-star" style="color:#f59e0b;"></i>@endfor
            @if($photoHalfStar)<i class="fas fa-star-half-alt" style="color:#f59e0b;"></i>@endif
        </span>
        @endif
        <span style="font-size:.72rem; color:var(--theme-muted);">
            {{ \Carbon\Carbon::parse($photo->created_at)->diffForHumans() }}
        </span>
    </div>

</div>



