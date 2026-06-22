@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="dashboard">

    {{-- ── SIDEBAR IZQUIERDO ── --}}
    <aside class="dashboard__sidebar dashboard__sidebar--left">

        {{-- Tarjeta de perfil --}}
        <article class="profile-summary-card card">
            <div class="profile-summary-card__header">
                <img src="{{ $profile->avatar_url ?? asset('img/default-avatar.svg') }}"
                     alt="{{ $profile->display_name ?? $user->name ?? $user->email }}"
                     class="avatar avatar--lg"
                     onerror="this.onerror=null; this.src='{{ asset('img/default-avatar.svg') }}'">
                <div>
                    <h2>{{ $profile->display_name ?? $user->name ?? $user->email }}</h2>
                    <p class="text-sm text-muted">
                        @if($user->isAdmin())
                            <span class="badge badge--vip">
                                <i class="fas fa-crown"></i> Administrador
                            </span>
                        @else
                            <span class="badge badge--verified">
                                <i class="fas fa-check-circle"></i> Miembro verificado
                            </span>
                        @endif
                    </p>
                    @if($profile?->nickname)
                    <p class="text-sm text-muted" style="margin-top:.3rem;">
                        {{ $profile->nickname }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Botones de acción del perfil --}}
            <div style="display:flex;flex-direction:column;gap:.6rem;margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f1f5f9;">

                @if($profile && $profile->profile_completed)
                    {{-- Perfil completo: botón editar --}}
                    <a href="{{ route('profile.edit') }}"
                       class="btn btn--primary"
                       style="text-align:center;font-size:.9rem;">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </a>
                @else
                    {{-- Sin perfil o incompleto: botón completar --}}
                    <a href="{{ route('profile.setup') }}"
                       class="btn btn--primary"
                       style="text-align:center;font-size:.9rem;background:linear-gradient(135deg,#f59e0b,#ef4444);">
                        <i class="fas fa-user-plus"></i> Completar Perfil
                    </a>
                @endif

                @if($user->isAdmin())
                <a href="{{ route('admin.invitations.index') }}"
                   class="btn btn--ghost"
                   style="text-align:center;font-size:.9rem;">
                    <i class="fas fa-shield-alt"></i> Panel Admin
                </a>
                @endif

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit"
                            class="btn btn--ghost"
                            style="width:100%;font-size:.9rem;color:#ef4444;border-color:#fca5a5;">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </article>

        {{-- Info rápida del perfil --}}
        @if($profile)
        <article class="card" style="padding:1.25rem;margin-top:1rem;">
            <h4 style="font-size:.85rem;font-weight:700;color:#374151;margin-bottom:.75rem;text-transform:uppercase;letter-spacing:.05em;">
                Mi Perfil
            </h4>
            <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.5rem;">
                @if($profile->profile_type)
                <li style="font-size:.85rem;color:#6b7280;">
                    <i class="fas fa-tag" style="width:1.2rem;color:#8b5cf6;"></i>
                    {{ ucfirst($profile->profile_type) }}
                </li>
                @endif
                @if($profile->city || $profile->state)
                <li style="font-size:.85rem;color:#6b7280;">
                    <i class="fas fa-map-marker-alt" style="width:1.2rem;color:#8b5cf6;"></i>
                    {{ implode(', ', array_filter([$profile->city, $profile->state])) }}
                </li>
                @endif
                @if($profile->age)
                <li style="font-size:.85rem;color:#6b7280;">
                    <i class="fas fa-birthday-cake" style="width:1.2rem;color:#8b5cf6;"></i>
                    {{ $profile->age }} años
                </li>
                @endif
            </ul>
        </article>
        @endif

    </aside>

    {{-- ── FEED CENTRAL ── --}}
    <section class="dashboard__feed">
        <h1 class="h2">Bienvenido a LOBBY69</h1>
        <p class="text-lg text-muted">
            ¡Hola <strong>{{ $profile->display_name ?? $user->name ?? $user->email }}</strong>!
            Explora la comunidad, conecta con personas afines y disfruta de una experiencia exclusiva.
        </p>

        {{-- Alerta si perfil incompleto --}}
        @if(!$profile || !$profile->profile_completed)
        <div class="card" style="padding:2rem;margin-top:1.5rem;text-align:center;border:2px dashed #f59e0b;">
            <i class="fas fa-user-edit" style="font-size:3rem;color:#f59e0b;margin-bottom:1rem;"></i>
            <h3>{{ $profile ? 'Completa tu perfil' : 'Crea tu perfil' }}</h3>
            <p style="margin-bottom:1rem;color:#6b7280;">
                Para aprovechar al máximo LOBBY69, completa tu perfil con fotos y una descripción.
            </p>
            <a href="{{ route('profile.setup') }}" class="btn btn--primary">
                <i class="fas fa-arrow-right"></i>
                {{ $profile ? 'Continuar configuración' : 'Crear Perfil' }}
            </a>
        </div>
        @endif

        {{-- Mensajes de sesión --}}
        @if(session('success'))
        <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:1rem;border-radius:10px;margin-top:1.5rem;">
            ✅ {{ session('success') }}
        </div>
        @endif

        {{-- Placeholder feed --}}
        <div class="card" style="padding:2rem;margin-top:1.5rem;text-align:center;color:#9ca3af;">
            <i class="fas fa-stream" style="font-size:2.5rem;margin-bottom:1rem;"></i>
            <p>El feed de actividad estará disponible próximamente.</p>
        </div>
    </section>

    {{-- ── SIDEBAR DERECHO ── --}}
    <aside class="dashboard__sidebar dashboard__sidebar--right">
        <section class="card" style="padding:1.5rem;">
            <h3 class="h4">Conectados</h3>
            <p class="text-sm text-muted">Próximamente: usuarios en línea</p>
        </section>
    </aside>

</section>
@endsection