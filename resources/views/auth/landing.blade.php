@extends('layouts.app')

@section('title', 'Tu acceso exclusivo al placer compartido')

@push('styles')
<style>
/* ══ LANDING REDESIGN ══ */
:root {
    --l-gold: #f59e0b;
    --l-purple: #6C3FC5;
    --l-pink: #e056a0;
    --l-grad: linear-gradient(135deg, #6C3FC5, #e056a0);
    --l-grad-gold: linear-gradient(135deg, #f59e0b, #ef4444);
}

/* HERO */
.lp-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #0a0a0f;
}
.lp-hero__bg {
    position: absolute;
    inset: 0;
    background-image: url('/img/hero.png');
    background-size: cover;
    background-position: center top;
    background-repeat: no-repeat;
    z-index: 0;
}
.lp-hero__bg::after {
    content: """";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(10,10,15,.55) 0%,
        rgba(10,10,15,.35) 40%,
        rgba(10,10,15,.75) 100%
    );
    z-index: 1;
}
.lp-hero__particles {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(1px 1px at 20% 30%, rgba(255,255,255,.4) 0%, transparent 100%),
        radial-gradient(1px 1px at 80% 20%, rgba(255,255,255,.3) 0%, transparent 100%),
        radial-gradient(1px 1px at 60% 70%, rgba(255,255,255,.2) 0%, transparent 100%),
        radial-gradient(1px 1px at 40% 80%, rgba(255,255,255,.3) 0%, transparent 100%),
        radial-gradient(1px 1px at 90% 60%, rgba(255,255,255,.2) 0%, transparent 100%);
}
.lp-hero__content {
    position: relative;
    z-index: 2;
    text-align: center;
    padding: 2rem 1rem;
    max-width: 820px;
    margin: 0 auto;
}
.lp-hero__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(108,63,197,.2);
    border: 1px solid rgba(108,63,197,.4);
    border-radius: 999px;
    padding: .35rem 1rem;
    font-size: .78rem;
    font-weight: 700;
    color: #c4b5fd;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 1.5rem;
}
.lp-hero__eyebrow span { display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 8px #22c55e; }
.lp-hero__logo { height: 56px; width: auto; margin: 0 auto 1.5rem; }
.lp-hero__title {
    font-size: clamp(2.2rem, 6vw, 4rem);
    font-weight: 900;
    line-height: 1.1;
    color: #fff;
    margin-bottom: 1rem;
    letter-spacing: -.03em;
}
.lp-hero__title em {
    font-style: normal;
    background: var(--l-grad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.lp-hero__sub {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: rgba(255,255,255,.65);
    margin-bottom: 2rem;
    line-height: 1.7;
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
}
.lp-hero__badges {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: .6rem;
    margin-bottom: 2.5rem;
}
.lp-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .4rem .9rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 700;
    border: 1px solid;
}
.lp-badge--green  { background: rgba(34,197,94,.1);  border-color: rgba(34,197,94,.3);  color: #86efac; }
.lp-badge--purple { background: rgba(108,63,197,.1); border-color: rgba(108,63,197,.3); color: #c4b5fd; }
.lp-badge--gold   { background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.3); color: #fcd34d; }
.lp-hero__actions { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
.lp-btn-primary {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--l-grad);
    color: #fff; font-weight: 800; font-size: 1rem;
    padding: .85rem 2rem; border-radius: 12px;
    text-decoration: none; border: none; cursor: pointer;
    box-shadow: 0 4px 24px rgba(108,63,197,.4);
    transition: transform .2s, box-shadow .2s;
}
.lp-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(108,63,197,.6); color: #fff; }
.lp-btn-secondary {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(255,255,255,.07);
    color: #fff; font-weight: 700; font-size: 1rem;
    padding: .85rem 2rem; border-radius: 12px;
    text-decoration: none; border: 1px solid rgba(255,255,255,.2);
    transition: background .2s, transform .2s;
}
.lp-btn-secondary:hover { background: rgba(255,255,255,.12); transform: translateY(-2px); color: #fff; }
.lp-hero__scroll {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    color: rgba(255,255,255,.3);
    font-size: .75rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .4rem;
    animation: bounce 2s infinite;
}
@keyframes bounce { 0%,100%{transform:translateX(-50%) translateY(0)} 50%{transform:translateX(-50%) translateY(6px)} }

/* STATS BAR */
.lp-stats {
    background: rgba(255,255,255,.03);
    border-top: 1px solid rgba(255,255,255,.06);
    border-bottom: 1px solid rgba(255,255,255,.06);
    padding: 1.5rem 0;
}
[data-theme="light"] .lp-stats { background: rgba(0,0,0,.03); border-color: rgba(0,0,0,.08); }
.lp-stats__inner {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    justify-content: center;
    gap: 3rem;
    flex-wrap: wrap;
}
.lp-stat { text-align: center; }
.lp-stat__num { font-size: 1.8rem; font-weight: 900; background: var(--l-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.lp-stat__label { font-size: .75rem; color: var(--theme-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; margin-top: .1rem; }

/* SECCIONES */
.lp-section { padding: 5rem 1.5rem; }
.lp-section--dark { background: #07070d; }
.lp-section--alt  { background: rgba(108,63,197,.04); }
[data-theme="light"] .lp-section--dark { background: #f8f7ff; }
[data-theme="light"] .lp-section--alt  { background: #f0eeff; }
.lp-container { max-width: 1100px; margin: 0 auto; }
.lp-eyebrow {
    display: inline-block;
    background: var(--l-grad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-bottom: .75rem;
}
.lp-h2 {
    font-size: clamp(1.6rem, 4vw, 2.4rem);
    font-weight: 900;
    color: var(--theme-text);
    margin-bottom: .75rem;
    letter-spacing: -.02em;
    line-height: 1.2;
}
.lp-lead { font-size: 1rem; color: var(--theme-muted); margin-bottom: 3rem; max-width: 540px; line-height: 1.7; }
.lp-text-center { text-align: center; }
.lp-text-center .lp-lead { margin-left: auto; margin-right: auto; }

/* FEATURES GRID */
.lp-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; }
.lp-feat {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: 1.75rem 1.5rem;
    transition: transform .2s, border-color .2s;
}
.lp-feat:hover { transform: translateY(-4px); border-color: rgba(108,63,197,.4); }
[data-theme="light"] .lp-feat { background: #fff; border-color: rgba(0,0,0,.08); box-shadow: 0 2px 16px rgba(0,0,0,.06); }
[data-theme="light"] .lp-feat:hover { border-color: rgba(108,63,197,.3); box-shadow: 0 8px 32px rgba(108,63,197,.12); }
.lp-feat__icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; margin-bottom: 1rem;
    background: var(--l-grad);
    color: #fff;
}
.lp-feat__title { font-size: 1rem; font-weight: 800; color: var(--theme-text); margin-bottom: .4rem; }
.lp-feat__desc  { font-size: .85rem; color: var(--theme-muted); line-height: 1.6; }

/* HOW IT WORKS */
.lp-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; position: relative; }
.lp-step { text-align: center; }
.lp-step__num {
    width: 52px; height: 52px; border-radius: 50%;
    background: var(--l-grad);
    color: #fff; font-size: 1.2rem; font-weight: 900;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 20px rgba(108,63,197,.4);
}
.lp-step__title { font-size: .95rem; font-weight: 800; color: var(--theme-text); margin-bottom: .35rem; }
.lp-step__desc  { font-size: .82rem; color: var(--theme-muted); line-height: 1.6; }

/* PLANES */
.lp-plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: start; }
.lp-plan {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 16px;
    padding: 1.5rem 1.25rem;
    text-align: center;
    position: relative;
    transition: transform .2s;
}
.lp-plan:hover { transform: translateY(-4px); }
[data-theme="light"] .lp-plan { background: #fff; border-color: rgba(0,0,0,.1); box-shadow: 0 2px 16px rgba(0,0,0,.06); }
.lp-plan--featured {
    background: linear-gradient(135deg, rgba(108,63,197,.2), rgba(224,86,160,.15));
    border-color: rgba(108,63,197,.5);
    transform: scale(1.03);
}
.lp-plan--featured:hover { transform: scale(1.03) translateY(-4px); }
[data-theme="light"] .lp-plan--featured { background: linear-gradient(135deg, #f3eeff, #fce7f3); border-color: rgba(108,63,197,.4); }
.lp-plan__badge {
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    background: var(--l-grad); color: #fff;
    font-size: .68rem; font-weight: 800; letter-spacing: .06em;
    padding: .25rem .75rem; border-radius: 999px; white-space: nowrap;
}
.lp-plan__icon { font-size: 1.5rem; margin-bottom: .75rem; background: var(--l-grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.lp-plan__name { font-size: 1rem; font-weight: 900; color: var(--theme-text); margin-bottom: .25rem; }
.lp-plan__desc { font-size: .75rem; color: var(--theme-muted); margin-bottom: 1rem; font-style: italic; }
.lp-plan__price { font-size: 1.8rem; font-weight: 900; color: var(--theme-text); line-height: 1; }
.lp-plan__price sup { font-size: .9rem; vertical-align: super; }
.lp-plan__price-old { font-size: .8rem; color: var(--theme-muted); text-decoration: line-through; margin-bottom: .25rem; }
.lp-plan__duration { font-size: .72rem; font-weight: 700; color: var(--theme-muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 1.25rem; }

/* TESTIMONIALES */
.lp-testimonials { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; }
.lp-testi {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 16px;
    padding: 1.5rem;
}
[data-theme="light"] .lp-testi { background: #fff; border-color: rgba(0,0,0,.08); box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.lp-testi__stars { color: #f59e0b; font-size: .85rem; margin-bottom: .75rem; }
.lp-testi__text  { font-size: .88rem; color: var(--theme-muted); line-height: 1.7; margin-bottom: 1rem; font-style: italic; }
.lp-testi__author { display: flex; align-items: center; gap: .6rem; }
.lp-testi__avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--l-grad);
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 800; color: #fff;
}
.lp-testi__name { font-size: .82rem; font-weight: 700; color: var(--theme-text); }
.lp-testi__meta { font-size: .72rem; color: var(--theme-muted); }

/* CTA FINAL */
.lp-cta {
    background: linear-gradient(135deg, #0f0720 0%, #1a0a3e 50%, #0f0720 100%);
    padding: 5rem 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
[data-theme="light"] .lp-cta { background: linear-gradient(135deg, #f3eeff 0%, #fce7f3 50%, #f3eeff 100%); }
.lp-cta::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 70% 60% at 50% 50%, rgba(108,63,197,.2) 0%, transparent 70%);
}
.lp-cta__content { position: relative; z-index: 2; max-width: 640px; margin: 0 auto; }
.lp-cta__title { font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 900; color: #fff; margin-bottom: 1rem; line-height: 1.2; }
[data-theme="light"] .lp-cta__title { color: var(--theme-text); }
.lp-cta__sub { font-size: 1rem; color: rgba(255,255,255,.6); margin-bottom: 2rem; line-height: 1.7; }
[data-theme="light"] .lp-cta__sub { color: var(--theme-muted); }
.lp-cta__note { font-size: .78rem; color: rgba(255,255,255,.35); margin-top: 1rem; }
[data-theme="light"] .lp-cta__note { color: var(--theme-muted); }

@media(max-width:640px) {
    .lp-hero { min-height: 100svh; }
    .lp-stats__inner { gap: 1.5rem; }
    .lp-section { padding: 3rem 1rem; }
    .lp-plan--featured { transform: none; }
}
</style>
@endpush

@section('content')

{{-- ══ HERO ══ --}}
<section class="lp-hero">
    <div class="lp-hero__bg"></div>
    <div class="lp-hero__particles"></div>
    <div class="lp-hero__content">
        <div class="lp-hero__eyebrow">
            <span></span> Comunidad activa — México y LATAM
        </div>
        <img src="{{ asset('img/logo-lobby69.png') }}" class="lp-hero__logo" alt="LOBBY69"
             onerror="this.style.display='none'">
        <h1 class="lp-hero__title">
            El club privado donde<br>
            <em>las conexiones reales suceden</em>
        </h1>
        <p class="lp-hero__sub">
            La comunidad swinger más discreta de México y LATAM.<br>
            Acceso exclusivo por invitación para mayores de 18 años.
        </p>
        <div class="lp-hero__badges">
            <span class="lp-badge lp-badge--green"><i class="fas fa-check-circle"></i> Solo por invitación</span>
            <span class="lp-badge lp-badge--purple"><i class="fas fa-crown"></i> Perfiles verificados</span>
            <span class="lp-badge lp-badge--gold"><i class="fas fa-shield-alt"></i> 100% Discreto</span>
        </div>
        <div class="lp-hero__actions">
            <a href="{{ route('invitation.show') }}" class="lp-btn-primary">
                <i class="fas fa-envelope"></i> Solicitar Acceso
            </a>
            <a href="{{ route('login') }}" class="lp-btn-secondary">
                <i class="fas fa-sign-in-alt"></i> Ya soy miembro
            </a>
        </div>
    </div>
    <div class="lp-hero__scroll">
        <i class="fas fa-chevron-down"></i>
        <span>Descubre más</span>
    </div>
</section>

{{-- ══ STATS ══ --}}
<div class="lp-stats">
    <div class="lp-stats__inner">
        <div class="lp-stat">
            <div class="lp-stat__num">+500</div>
            <div class="lp-stat__label">Miembros activos</div>
        </div>
        <div class="lp-stat">
            <div class="lp-stat__num">+2K</div>
            <div class="lp-stat__label">Conexiones realizadas</div>
        </div>
        <div class="lp-stat">
            <div class="lp-stat__num">4.8★</div>
            <div class="lp-stat__label">Satisfacción</div>
        </div>
        <div class="lp-stat">
            <div class="lp-stat__num">100%</div>
            <div class="lp-stat__label">Perfiles verificados</div>
        </div>
    </div>
</div>

{{-- ══ FEATURES ══ --}}
<section class="lp-section lp-section--dark">
    <div class="lp-container">
        <div class="lp-text-center">
            <span class="lp-eyebrow">¿Por qué LOBBY69?</span>
            <h2 class="lp-h2">Todo lo que necesitas en un solo lugar</h2>
            <p class="lp-lead">Más que una red social, un espacio seguro diseñado para la comunidad</p>
        </div>
        <div class="lp-features">
            <div class="lp-feat">
                <div class="lp-feat__icon"><i class="fas fa-shield-alt"></i></div>
                <div class="lp-feat__title">Privacidad Total</div>
                <div class="lp-feat__desc">Tu información nunca se comparte. Perfiles con nickname, sin nombres reales visibles.</div>
            </div>
            <div class="lp-feat">
                <div class="lp-feat__icon"><i class="fas fa-user-check"></i></div>
                <div class="lp-feat__title">Verificación Real</div>
                <div class="lp-feat__desc">Todos los miembros pasan por verificación de identidad. Cero perfiles falsos o bots.</div>
            </div>
            <div class="lp-feat">
                <div class="lp-feat__icon"><i class="fas fa-star"></i></div>
                <div class="lp-feat__title">Sistema de Reputación</div>
                <div class="lp-feat__desc">Estrellas y reseñas verificadas. Construye tu reputación y conecta con los mejores perfiles.</div>
            </div>
            <div class="lp-feat">
                <div class="lp-feat__icon"><i class="fas fa-comments"></i></div>
                <div class="lp-feat__title">Mensajería Privada</div>
                <div class="lp-feat__desc">Chat directo con otros miembros. Conversaciones seguras y confidenciales.</div>
            </div>
            <div class="lp-feat">
                <div class="lp-feat__icon"><i class="fas fa-calendar-check"></i></div>
                <div class="lp-feat__title">Disponibilidad</div>
                <div class="lp-feat__desc">Indica cuándo estás disponible. Encuentra quién está libre hoy, este finde o en la semana.</div>
            </div>
            <div class="lp-feat">
                <div class="lp-feat__icon"><i class="fas fa-images"></i></div>
                <div class="lp-feat__title">Galería Privada</div>
                <div class="lp-feat__desc">Álbumes públicos y privados. Tú decides quién ve qué y cuándo.</div>
            </div>
        </div>
    </div>
</section>

{{-- ══ CÓMO FUNCIONA ══ --}}
<section class="lp-section lp-section--alt">
    <div class="lp-container">
        <div class="lp-text-center">
            <span class="lp-eyebrow">Proceso de acceso</span>
            <h2 class="lp-h2">Tres pasos para entrar al club</h2>
            <p class="lp-lead">Simple, rápido y 100% confidencial</p>
        </div>
        <div class="lp-steps">
            <div class="lp-step">
                <div class="lp-step__num">1</div>
                <div class="lp-step__title">Solicita tu invitación</div>
                <div class="lp-step__desc">Llena el formulario con tus datos básicos. Si tienes un código de referido, tu acceso es prioritario.</div>
            </div>
            <div class="lp-step">
                <div class="lp-step__num">2</div>
                <div class="lp-step__title">Revisamos tu solicitud</div>
                <div class="lp-step__desc">Nuestro equipo revisa cada solicitud para garantizar la calidad y seguridad de la comunidad.</div>
            </div>
            <div class="lp-step">
                <div class="lp-step__num">3</div>
                <div class="lp-step__title">Recibe tus credenciales</div>
                <div class="lp-step__desc">Al aprobar, recibes tu usuario y contraseña temporal. Completa tu perfil y comienza a conectar.</div>
            </div>
            <div class="lp-step">
                <div class="lp-step__num">4</div>
                <div class="lp-step__title">Conecta y disfruta</div>
                <div class="lp-step__desc">Explora perfiles, sube fotos, activa tu disponibilidad y construye tu reputación en la comunidad.</div>
            </div>
        </div>
    </div>
</section>

{{-- ══ PLANES ══ --}}
<section class="lp-section lp-section--dark">
    <div class="lp-container">
        <div class="lp-text-center">
            <span class="lp-eyebrow">Membresías</span>
            <h2 class="lp-h2">Elige tu nivel de acceso</h2>
            <p class="lp-lead">Con código de invitación válido, obtienes <strong style="color:#f59e0b;">1 mes gratis</strong> en cualquier plan</p>
        </div>
        @php
        $plans = [
            ['name'=>'Explorer',   'desc'=>'Estoy explorando la plataforma',          'price'=>'180',   'old'=>'297',   'dur'=>'1 MES',      'icon'=>'fa-compass',    'featured'=>false],
            ['name'=>'Connectors', 'desc'=>'Quiero conectar con más gente',            'price'=>'299',   'old'=>'493',   'dur'=>'3 MESES',    'icon'=>'fa-link',       'featured'=>false],
            ['name'=>'Influencer', 'desc'=>'Quiero destacar en la comunidad',          'price'=>'599',   'old'=>'988',   'dur'=>'6 MESES',    'icon'=>'fa-chart-line', 'featured'=>false],
            ['name'=>'VIP Elite',  'desc'=>'Acceso completo todo el año',              'price'=>'1,199', 'old'=>'1,978', 'dur'=>'1 AÑO',      'icon'=>'fa-crown',      'featured'=>true],
            ['name'=>'Fundador',   'desc'=>'Solo 20 membresías disponibles',           'price'=>'3,500', 'old'=>'5,775', 'dur'=>'PERMANENTE', 'icon'=>'fa-infinity',   'featured'=>false],
        ];
        @endphp
        <div class="lp-plans">
        @foreach($plans as $plan)
        <div class="lp-plan {{ $plan['featured'] ? 'lp-plan--featured' : '' }}">
            @if($plan['featured'])
            <div class="lp-plan__badge">⭐ MÁS POPULAR</div>
            @endif
            <div class="lp-plan__icon"><i class="fas {{ $plan['icon'] }}"></i></div>
            <div class="lp-plan__name">{{ $plan['name'] }}</div>
            <div class="lp-plan__desc">"{{ $plan['desc'] }}"</div>
            <div class="lp-plan__price-old">${{ $plan['old'] }} MXN</div>
            <div class="lp-plan__price"><sup>$</sup>{{ $plan['price'] }}</div>
            <div class="lp-plan__duration">{{ $plan['dur'] }}</div>
            <a href="{{ route('invitation.show') }}" class="lp-btn-primary" style="width:100%;justify-content:center;font-size:.85rem;padding:.65rem 1rem;">
                <i class="fas fa-envelope"></i> Solicitar acceso
            </a>
        </div>
        @endforeach
        </div>
    </div>
</section>

{{-- ══ TESTIMONIALES ══ --}}
<section class="lp-section lp-section--alt">
    <div class="lp-container">
        <div class="lp-text-center">
            <span class="lp-eyebrow">Lo que dicen nuestros miembros</span>
            <h2 class="lp-h2">Experiencias reales de la comunidad</h2>
            <p class="lp-lead">Más de 500 miembros ya forman parte del club más exclusivo</p>
        </div>
        <div class="lp-testimonials">
            <div class="lp-testi">
                <div class="lp-testi__stars">★★★★★</div>
                <div class="lp-testi__text">"La plataforma más seria y discreta que he encontrado. El proceso de verificación da mucha confianza a todos."</div>
                <div class="lp-testi__author">
                    <div class="lp-testi__avatar">L</div>
                    <div>
                        <div class="lp-testi__name">Luna_MX</div>
                        <div class="lp-testi__meta">Unicornio · CDMX · Miembro verificado</div>
                    </div>
                </div>
            </div>
            <div class="lp-testi">
                <div class="lp-testi__stars">★★★★★</div>
                <div class="lp-testi__text">"Como pareja fue exactamente lo que buscábamos. Perfiles reales, personas maduras y un ambiente de total respeto."</div>
                <div class="lp-testi__author">
                    <div class="lp-testi__avatar">P</div>
                    <div>
                        <div class="lp-testi__name">ParejaGDL</div>
                        <div class="lp-testi__meta">Pareja · Guadalajara · Miembro verificado</div>
                    </div>
                </div>
            </div>
            <div class="lp-testi">
                <div class="lp-testi__stars">★★★★★</div>
                <div class="lp-testi__text">"El sistema de estrellas es brillante. Puedes ver quién es activo y confiable antes de iniciar cualquier conversación."</div>
                <div class="lp-testi__author">
                    <div class="lp-testi__avatar">A</div>
                    <div>
                        <div class="lp-testi__name">Alex_Single</div>
                        <div class="lp-testi__meta">Single · Monterrey · Miembro verificado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ CTA FINAL ══ --}}
<section class="lp-cta">
    <div class="lp-cta__content">
        <div style="font-size:3rem;margin-bottom:1rem;">🔑</div>
        <h2 class="lp-cta__title">¿Listo para ser parte<br>de algo exclusivo?</h2>
        <p class="lp-cta__sub">
            La comunidad te espera. Solicita tu invitación hoy y empieza a conectar con personas reales en un espacio seguro y discreto.
        </p>
        <a href="{{ route('invitation.show') }}" class="lp-btn-primary" style="font-size:1.1rem;padding:1rem 2.5rem;">
            <i class="fas fa-envelope"></i> Solicitar Invitación Ahora
        </a>
        <div class="lp-cta__note">
            <i class="fas fa-lock"></i> Solo para mayores de 18 años · Acceso por invitación · 100% confidencial
        </div>
    </div>
</section>

@endsection


