@foreach($feed as $photo)
@php
    $photoUrl  = supabase_photo_url($photo->file_path);
    $avatarUrl = supabase_photo_url($photo->avatar_file_path ?? null)
                 ?? asset('img/default-avatar.svg');

    $ownerNick = $photo->nickname ?? null;
    $ownerName = $photo->display_name ?? 'Usuario';
    $ownerUrl  = $ownerNick ? route('profile.show', $ownerNick) : '#';

    $ptLabel = match($photo->profile_type ?? null) {
        'pareja'    => '👫 Pareja',
        'single'    => '🙋 Single',
        'unicornio' => '🦄 Unicornio',
        default     => '👤 Perfil',
    };
@endphp

<div class="l69-feed-card"
     data-photo-id="{{ $photo->photo_uuid }}"
     style="cursor:pointer;">

    <div class="l69-feed-card__img-wrap">

        <img src="{{ $photoUrl }}"
             alt="{{ $photo->caption ?? 'Foto' }}"
             class="l69-feed-card__img"
             loading="lazy"
             decoding="async"
             onerror="this.closest('.l69-feed-card').style.display='none'">

        <a href="{{ $ownerUrl }}"
           class="l69-feed-card__owner-top"
           onclick="event.stopPropagation()">
            <img src="{{ $avatarUrl }}"
                 alt="{{ $ownerName }}"
                 loading="lazy"
                 decoding="async"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            <span>{{ $ownerName }}</span>
        </a>

        <div class="l69-feed-card__overlay">
            <button class="l69-like-btn {{ ($photo->user_liked ?? false) ? 'is-liked' : '' }}"
                    data-photo-id="{{ $photo->photo_uuid }}">
                <i class="{{ ($photo->user_liked ?? false) ? 'fas' : 'far' }} fa-heart"
                   style="{{ ($photo->user_liked ?? false) ? 'color:#e056a0;' : 'color:#fff;' }}"></i>
                <span class="like-count" style="color:#fff;">{{ $photo->likes_count ?? 0 }}</span>
            </button>
            <span class="l69-feed-card__comments">
                <i class="far fa-comment"></i>
                {{ $photo->comments_count ?? 0 }}
            </span>
        </div>
    </div>

    <div style="padding:.5rem .75rem .6rem; display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:.78rem; font-weight:600; color:var(--theme-pink);">
            {{ $ptLabel }}
            @if(!empty($photo->verified_profile))
                <i class="fas fa-check-circle" style="color:#6C3FC5; font-size:.7rem; margin-left:.2rem;"></i>
            @endif
        </span>
        <span style="font-size:.72rem; color:var(--theme-muted);">
            {{ \Carbon\Carbon::parse($photo->created_at)->diffForHumans() }}
        </span>
    </div>
</div>
@endforeach