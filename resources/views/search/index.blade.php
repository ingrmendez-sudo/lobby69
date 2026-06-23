@extends('layouts.app')
@section('title', $q ? "Búsqueda: {$q}" : 'Buscar')

@section('content')
<div class="l69-page-content">
    <h1 style="font-size:1.4rem;font-weight:700;color:#fff;margin-bottom:1.25rem;">
        <i class="fas fa-search"></i>
        @if($q) Resultados para "<span style="color:#e056a0;">{{ $q }}</span>"
        @else Explorar miembros @endif
    </h1>

    @if($q && $results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->total() === 0)
    <div class="l69-sb-card" style="text-align:center;padding:2.5rem;">
        <i class="fas fa-user-slash" style="font-size:2rem;color:#4b5563;margin-bottom:1rem;display:block;"></i>
        <p style="color:#9ca3af;">No encontramos perfiles con "<strong>{{ $q }}</strong>"</p>
        <a href="{{ route('explore') }}" class="l69-quick-btn" style="display:inline-flex;margin-top:1rem;">
            Explorar todos los perfiles →
        </a>
    </div>
    @elseif($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->count() > 0)
    <div class="l69-feed__grid">
        @foreach($results as $profile)
        <div class="l69-profile-card">
            <a href="{{ route('profile.show', $profile->nickname) }}">
                <img src="{{ $profile->avatar_url ?? asset('img/default-avatar.svg') }}"
                     alt="{{ $profile->nickname }}"
                     class="l69-profile-card__avatar"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            </a>
            <a href="{{ route('profile.show', $profile->nickname) }}"
               class="l69-profile-card__nick">{{ $profile->nickname }}</a>
            <span class="l69-profile-card__type">
                @if($profile->profile_type === 'pareja') 👫 Pareja
                @elseif($profile->profile_type === 'unicornio') ⭐ Unicornio
                @else 👤 Single @endif
            </span>
        </div>
        @endforeach
    </div>
    {{ $results->appends(['q' => $q])->links() }}
    @endif
</div>
@endsection