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
    $commCount  = $photo->comments_count ?? 0;
@endphp

<div class="l69-feed-card"
     data-photo-id="{{ $photo->id }}"
     style="cursor:pointer;">

    {{-- Imagen principal --}}
    <div class="l69-feed-card__img-wrap">
        <img loading="lazy" src="/foto/{{ $photo->file_path }}"
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
        <span style="font-size:.72rem; color:var(--theme-muted);">
            {{ \Carbon\Carbon::parse($photo->created_at)->diffForHumans() }}
        </span>
    </div>

</div>

