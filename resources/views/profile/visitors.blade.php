@extends('layouts.app')
@section('title', 'Quién visitó mi perfil')
@push('sidebar-left')
    @include('layouts.sidebar-left')
@endpush
@push('sidebar-right')
    @include('layouts.sidebar-right')
@endpush
@section('content')
<div style="max-width:680px;margin:0 auto;padding:1.5rem 1rem;">
    <div class="l69-sidebar-card" style="margin-bottom:1.5rem;">
        <div class="l69-sidebar-card__title" style="font-size:1rem;margin-bottom:1rem;">
            <i class="fas fa-eye"></i> Últimas visitas a tu perfil
            <span style="font-size:.75rem;color:var(--theme-muted);font-weight:400;margin-left:.5rem;">
                (últimos 30 visitantes únicos)
            </span>
        </div>

        @forelse($visitors as $v)
        <a href="{{ $v->nickname ? route('profile.show', $v->nickname) : '#' }}"
           style="display:flex;align-items:center;gap:.75rem;padding:.65rem 0;
                  text-decoration:none;border-bottom:1px solid rgba(255,255,255,.06);">
            @if($v->avatar_id)
            <img src="{{ route('photos.serve', $v->avatar_id) }}"
                 style="width:42px;height:42px;border-radius:50%;object-fit:cover;flex-shrink:0;"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            @else
            <div style="width:42px;height:42px;border-radius:50%;background:rgba(224,86,160,.2);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-user" style="color:#f472b6;"></i>
            </div>
            @endif
            <div style="flex:1;min-width:0;">
                <div style="font-size:.88rem;font-weight:600;color:var(--theme-text);">
                    {{ $v->display_name ?? $v->nickname ?? 'Usuario' }}
                    @if($v->verified_profile)
                    <i class="fas fa-check-circle" style="color:#22c55e;font-size:.7rem;"></i>
                    @endif
                </div>
                <div style="font-size:.75rem;color:var(--theme-muted);">
                    {{ $v->profile_type ?? '' }} · {{ \Carbon\Carbon::parse($v->last_visit)->diffForHumans() }}
                </div>
            </div>
            <i class="fas fa-chevron-right" style="color:var(--theme-muted);font-size:.7rem;"></i>
        </a>
        @empty
        <div style="text-align:center;padding:2rem;color:var(--theme-muted);">
            <i class="fas fa-eye-slash" style="font-size:2rem;margin-bottom:.75rem;display:block;opacity:.4;"></i>
            <p style="margin:0;font-size:.88rem;">Aún nadie ha visitado tu perfil.</p>
            <a href="{{ route('explore') }}" style="font-size:.82rem;color:#e056a0;margin-top:.5rem;display:inline-block;">
                Explora perfiles para darte a conocer →
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
