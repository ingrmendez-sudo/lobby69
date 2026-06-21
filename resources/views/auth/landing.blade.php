@extends('layouts.app')

@section('title', 'Tu acceso exclusivo al placer compartido')

@section('content')
<!-- HERO SECTION -->
<section class="landing-hero landing-hero--with-image">
    <div class="landing-hero__overlay"></div>
    <div class="container landing-hero__content">
        <div class="landing-hero__logo">
            <img src="{{ asset('img/logo-lobby69.png') }}" class="landing-hero__logo-img"
                 onerror="this.style.display='none'">
            <!--<h1 class="landing-hero__title">LOBBY69</h1>-->
        </div>
        <p class="landing-hero__subtitle">Tu acceso exclusivo al placer compartido</p>
        <p class="landing-hero__description">
            La comunidad swinger más discreta de México te espera.<br>
            Un Club Privado para mayores de 18 años, donde conectas con personas<br>
            afines en un espacio seguro y confidencial.
        </p>
        <div class="landing-hero__badges">
            <span class="badge badge--verified"><i class="fas fa-check-circle"></i> Solo por invitación</span>
            <span class="badge badge--vip"><i class="fas fa-crown"></i> Membresías verificadas</span>
            <span class="badge badge--new"><i class="fas fa-shield-alt"></i> 100% Discreto</span>
        </div>
        <div class="landing-hero__actions">
            <a href="{{ route('login') }}" class="btn btn--primary btn--lg">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </a>
            <a href="{{ route('invitation.show') }}" class="btn btn--secondary btn--lg">
                <i class="fas fa-envelope"></i> Solicitar Invitación
            </a>
        </div>
    </div>
</section>

<!-- DIFERENCIALES -->
<section class="section-differentials">
    <div class="container">
        <h2 class="section-title">¿Por qué LOBBY69?</h2>
        <p class="section-subtitle">Más que una red social, un club exclusivo para la comunidad</p>

        <div class="differentials-grid">
            <div class="differential-card card">
                <div class="differential-card__icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Seguridad y Privacidad</h3>
                <p>Mantenemos nuestra comunidad auténtica y segura a través de membresías verificadas y moderación activa.</p>
            </div>

            <div class="differential-card card">
                <div class="differential-card__icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3>Verificación Obligatoria</h3>
                <p>Todos los miembros pasan por un proceso de verificación de identidad. Sin perfiles falsos.</p>
            </div>

            <div class="differential-card card">
                <div class="differential-card__icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>Conexiones Reales</h3>
                <p>Singles, parejas, unicornios. Encuentra personas afines en un ambiente de respeto y confianza.</p>
            </div>

            <div class="differential-card card">
                <div class="differential-card__icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Sistema de Reputación</h3>
                <p>Reseñas verificadas de otros miembros. Construye tu reputación en la comunidad.</p>
            </div>
        </div>
    </div>
</section>

<!-- PLANES -->
<section class="section-memberships">
    <div class="container">
        <h2 class="section-title">Planes de Membresía</h2>
        <p class="section-subtitle">Elige el plan que mejor se adapte a ti</p>

        <div class="plans-grid">
            @php
                $plans = [
                    ['code' => 'EXPLORER', 'name' => 'Explorer', 'desc' => 'Estoy explorando la plataforma', 'price' => '180', 'price_normal' => '297', 'duration' => '1 MES', 'icon' => 'fa-compass', 'featured' => false],
                    ['code' => 'CONNECTORS', 'name' => 'Connectors', 'desc' => 'Estoy conectado con más gente', 'price' => '299', 'price_normal' => '493', 'duration' => '3 MESES', 'icon' => 'fa-link', 'featured' => false],
                    ['code' => 'INFLUENCER', 'name' => 'Influencer', 'desc' => 'Tengo influencia en la comunidad', 'price' => '599', 'price_normal' => '988', 'duration' => '6 MESES', 'icon' => 'fa-chart-line', 'featured' => false],
                    ['code' => 'VIP_ELITE', 'name' => 'VIP Elite', 'desc' => 'Soy parte de la élite', 'price' => '1,199', 'price_normal' => '1,978', 'duration' => '1 AÑO', 'icon' => 'fa-crown', 'featured' => true],
                    ['code' => 'VITALICIO', 'name' => 'Vitalicio', 'desc' => 'Fundador de la comunidad Lobby69 (Solo 20 Membresias disponibles)', 'price' => '3,500', 'price_normal' => '5,775', 'duration' => 'PERMANENTE', 'icon' => 'fa-infinity', 'featured' => false],
                ];
            @endphp

            @foreach($plans as $plan)
            <div class="plan-card card {{ $plan['featured'] ? 'plan-card--featured' : '' }}">
                @if($plan['featured'])
                    <span class="plan-card__badge">MÁS POPULAR</span>
                @endif
                <div class="plan-card__icon">
                    <i class="fas {{ $plan['icon'] }}"></i>
                </div>
                <h3 class="plan-card__name">{{ $plan['name'] }}</h3>
                <p class="plan-card__desc">"{{ $plan['desc'] }}"</p>
                <div class="plan-card__price">
                    <span class="plan-card__price-current">${{ $plan['price'] }}</span>
                    <span class="plan-card__price-normal">${{ $plan['price_normal'] }}</span>
                </div>
                <p class="plan-card__duration">{{ $plan['duration'] }}</p>
                <a href="{{ route('invitation.show') }}" class="btn btn--primary btn--block">
                    Solicitar Invitación
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="section-cta">
    <div class="container">
        <h2>¿Listo para formar parte de la comunidad?</h2>
        <p>Si aún no tienes acceso, solicita una invitación y te ayudaremos a conectar con la comunidad.</p>
        <a href="{{ route('invitation.show') }}" class="btn btn--primary btn--lg">
            <i class="fas fa-envelope"></i> Solicitar Invitación
        </a>
    </div>
</section>
@endsection
