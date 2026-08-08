@if($availableUsers->isNotEmpty())
<section class="avail-section">
    <div class="avail-section__header">
        <span class="avail-section__dot"></span>
        <h3 class="avail-section__title">Disponibles ahora</h3>
        <span class="avail-section__count">{{ $availableUsers->count() }}</span>
    </div>

    <div class="avail-section__list">
        @foreach($availableUsers as $av)
        @php
            $avatarUrl = supabase_photo_url($av->avatar_path ?? null)
                         ?? asset('img/default-avatar.svg');
            $profileUrl = $av->nickname
                ? route('profile.show', $av->nickname)
                : '#';
            $mins  = max(0, (int) now()->diffInMinutes($av->expires_at, false));
            $hrs   = floor($mins / 60);
            $rem   = $mins % 60;
            $label = $mins < 60
                ? "{$mins}min"
                : ($rem > 0 ? "{$hrs}h {$rem}m" : "{$hrs}h");
        @endphp
        <a href="{{ $profileUrl }}" class="avail-card" title="{{ $av->display_name }}">
            <div class="avail-card__avatar-wrap">
                <img src="{{ $avatarUrl }}"
                     alt="{{ $av->display_name }}"
                     class="avail-card__avatar"
                     loading="lazy"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                <span class="avail-card__dot"></span>
            </div>
            <span class="avail-card__name">{{ Str::limit($av->display_name, 12) }}</span>
            <span class="avail-card__time">{{ $label }}</span>
        </a>
        @endforeach
    </div>
</section>
@endif