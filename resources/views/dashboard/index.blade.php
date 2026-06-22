@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<section class="dashboard">
    <aside class="dashboard__sidebar dashboard__sidebar--left">
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
                            <span class="badge badge--vip"><i class="fas fa-crown"></i> Administrador</span>
                        @else
                            <span class="badge badge--verified"><i class="fas fa-check-circle"></i> Miembro verificado</span>
                        @endif
                    </p>
                </div>
            </div>
        </article>
    </aside>

    <section class="dashboard__feed">
        <h1 class="h2">Bienvenido a LOBBY69</h1>
        <p class="text-lg text-muted">¡Hola <strong>{{ $user->name ?? $user->email }}</strong>! Explora la comunidad, conecta con personas afines y disfruta de una experiencia exclusiva.</p>

        @if(!$profile)
        <div class="card" style="padding: 2rem; margin-top: 1.5rem; text-align: center;">
            <i class="fas fa-user-edit" style="font-size: 3rem; color: var(--color-coral); margin-bottom: 1rem;"></i>
            <h3>Completa tu perfil</h3>
            <p style="margin-bottom: 1rem;">Para aprovechar al máximo LOBBY69, completa tu perfil con fotos y una descripción.</p>
            <a href="#" class="btn btn--primary">Editar Perfil</a>
        </div>
        @endif
    </section>

    <aside class="dashboard__sidebar dashboard__sidebar--right">
        <section class="card" style="padding: 1.5rem;">
            <h3 class="h4">Conectados</h3>
            <p class="text-sm text-muted">Próximamente: usuarios en línea</p>
        </section>
    </aside>
</section>
@endsection
