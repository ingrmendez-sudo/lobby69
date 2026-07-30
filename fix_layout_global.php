<?php
/**
 * fix_layout_global.php
 * Rediseño completo de Navbar + Layout 3 columnas + Sidebars
 * Ejecutar: C:\php\php.exe fix_layout_global.php
 */

$base = __DIR__;

// ============================================================
// 1. NAVBAR DINÁMICO COMPLETO
// ============================================================
$navbar = <<<'BLADE'
{{-- ══════════════════════════════════════════════════════
     NAVBAR LOBBY69 — Dinámico, sticky, con dropdown
     ══════════════════════════════════════════════════════ --}}

<style>
/* ── Variables del navbar ── */
:root {
    --nav-h: 64px;
    --nav-bg: rgba(15, 10, 26, 0.97);
    --nav-border: rgba(180, 60, 120, 0.25);
    --nav-text: #e2d9f3;
    --nav-text-muted: #9b8aaa;
    --nav-accent: #c0392b;
    --nav-accent-2: #8e44ad;
    --nav-hover-bg: rgba(180, 60, 120, 0.12);
    --nav-active-color: #e056a0;
    --nav-dropdown-bg: rgba(20, 14, 35, 0.98);
    --nav-shadow: 0 4px 32px rgba(0,0,0,0.45);
}

/* ── Navbar base ── */
.l69-nav {
    position: sticky;
    top: 0;
    z-index: 9000;
    height: var(--nav-h);
    background: var(--nav-bg);
    border-bottom: 1px solid var(--nav-border);
    box-shadow: var(--nav-shadow);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.l69-nav__inner {
    max-width: 1400px;
    margin: 0 auto;
    height: 100%;
    padding: 0 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* ── Logo ── */
.l69-nav__brand {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    text-decoration: none;
    flex-shrink: 0;
}
.l69-nav__logo {
    height: 36px;
    width: auto;
}
.l69-nav__brand-name {
    font-family: 'Montserrat', sans-serif;
    font-weight: 800;
    font-size: 1.15rem;
    background: linear-gradient(135deg, #e056a0, #8e44ad);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 0.04em;
}

/* ── Spacer ── */
.l69-nav__spacer { flex: 1; }

/* ── Links principales ── */
.l69-nav__links {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.l69-nav__link {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.85rem;
    border-radius: 8px;
    color: var(--nav-text);
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: background 0.18s, color 0.18s;
    white-space: nowrap;
    position: relative;
}
.l69-nav__link:hover {
    background: var(--nav-hover-bg);
    color: #fff;
}
.l69-nav__link.is-active {
    color: var(--nav-active-color);
}
.l69-nav__link.is-active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 20px;
    height: 2px;
    background: var(--nav-active-color);
    border-radius: 2px;
}
.l69-nav__link i { font-size: 0.85rem; opacity: 0.85; }

/* ── Badge pill ── */
.l69-nav__badge {
    display: inline-flex;
    align-items: center;
    padding: 0.18rem 0.55rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-left: 0.3rem;
}
.l69-nav__badge--new {
    background: linear-gradient(135deg, #e056a0, #8e44ad);
    color: #fff;
}
.l69-nav__badge--admin {
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: #fff;
}

/* ── Divider vertical ── */
.l69-nav__divider {
    width: 1px;
    height: 28px;
    background: var(--nav-border);
    margin: 0 0.5rem;
    flex-shrink: 0;
}

/* ── Dropdown de usuario ── */
.l69-nav__user {
    position: relative;
}
.l69-nav__user-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.75rem 0.35rem 0.35rem;
    background: var(--nav-hover-bg);
    border: 1px solid var(--nav-border);
    border-radius: 40px;
    cursor: pointer;
    transition: background 0.18s, border-color 0.18s;
    color: var(--nav-text);
    font-size: 0.88rem;
    font-weight: 500;
}
.l69-nav__user-btn:hover {
    background: rgba(180, 60, 120, 0.2);
    border-color: rgba(180, 60, 120, 0.5);
}
.l69-nav__user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(180,60,120,0.4);
    flex-shrink: 0;
}
.l69-nav__user-nick {
    max-width: 110px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.l69-nav__user-caret {
    font-size: 0.7rem;
    opacity: 0.6;
    transition: transform 0.2s;
}
.l69-nav__user.is-open .l69-nav__user-caret {
    transform: rotate(180deg);
}

/* ── Dropdown menu ── */
.l69-nav__dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 220px;
    background: var(--nav-dropdown-bg);
    border: 1px solid var(--nav-border);
    border-radius: 14px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.5);
    padding: 0.5rem;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: opacity 0.18s, transform 0.18s, visibility 0.18s;
    z-index: 9100;
}
.l69-nav__user.is-open .l69-nav__dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.l69-nav__dropdown-header {
    padding: 0.6rem 0.75rem 0.5rem;
    border-bottom: 1px solid var(--nav-border);
    margin-bottom: 0.4rem;
}
.l69-nav__dropdown-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: #fff;
}
.l69-nav__dropdown-sub {
    font-size: 0.75rem;
    color: var(--nav-text-muted);
    margin-top: 0.15rem;
}

.l69-nav__dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    color: var(--nav-text);
    text-decoration: none;
    font-size: 0.86rem;
    font-weight: 500;
    transition: background 0.15s, color 0.15s;
    width: 100%;
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
}
.l69-nav__dropdown-item:hover {
    background: var(--nav-hover-bg);
    color: #fff;
}
.l69-nav__dropdown-item i {
    width: 16px;
    text-align: center;
    opacity: 0.75;
    font-size: 0.82rem;
}
.l69-nav__dropdown-item--danger { color: #f87171; }
.l69-nav__dropdown-item--danger:hover { background: rgba(239,68,68,0.12); color: #fca5a5; }

.l69-nav__dropdown-sep {
    height: 1px;
    background: var(--nav-border);
    margin: 0.4rem 0;
}

/* ── Botones guest ── */
.l69-nav__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1.1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.18s;
    white-space: nowrap;
}
.l69-nav__btn--ghost {
    color: var(--nav-text);
    border: 1px solid var(--nav-border);
}
.l69-nav__btn--ghost:hover {
    background: var(--nav-hover-bg);
    border-color: rgba(180,60,120,0.5);
    color: #fff;
}
.l69-nav__btn--primary {
    background: linear-gradient(135deg, #c0392b, #8e44ad);
    color: #fff;
    border: 1px solid transparent;
}
.l69-nav__btn--primary:hover {
    opacity: 0.88;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(192,57,43,0.35);
}

/* ── Hamburger ── */
.l69-nav__hamburger {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 26px;
    height: 18px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
}
.l69-nav__hamburger span {
    display: block;
    height: 2px;
    background: var(--nav-text);
    border-radius: 2px;
    transition: all 0.25s;
}
.l69-nav__hamburger.is-open span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.l69-nav__hamburger.is-open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.l69-nav__hamburger.is-open span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

/* ── Mobile drawer ── */
@media (max-width: 767px) {
    .l69-nav__hamburger { display: flex; }
    .l69-nav__links,
    .l69-nav__divider,
    .l69-nav__user { display: none !important; }

    .l69-nav__mobile-drawer {
        position: fixed;
        top: var(--nav-h);
        left: 0; right: 0; bottom: 0;
        background: rgba(10, 6, 20, 0.97);
        z-index: 8999;
        padding: 1.5rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        overflow-y: auto;
        transform: translateX(-100%);
        transition: transform 0.28s cubic-bezier(.4,0,.2,1);
        border-top: 1px solid var(--nav-border);
    }
    .l69-nav__mobile-drawer.is-open {
        transform: translateX(0);
    }
    .l69-nav__mobile-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.8rem 1rem;
        border-radius: 10px;
        color: var(--nav-text);
        text-decoration: none;
        font-size: 1rem;
        font-weight: 500;
        transition: background 0.15s;
    }
    .l69-nav__mobile-link:hover,
    .l69-nav__mobile-link.is-active {
        background: var(--nav-hover-bg);
        color: #fff;
    }
    .l69-nav__mobile-link i { width: 20px; text-align: center; }
    .l69-nav__mobile-sep {
        height: 1px;
        background: var(--nav-border);
        margin: 0.5rem 0;
    }
    .l69-nav__mobile-user {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        background: var(--nav-hover-bg);
        border-radius: 12px;
        margin-bottom: 0.5rem;
    }
    .l69-nav__mobile-user img {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(180,60,120,0.5);
    }
}
@media (min-width: 768px) {
    .l69-nav__mobile-drawer { display: none !important; }
}
</style>

@php
    $currentRoute = request()->route()?->getName() ?? '';
    $isActive = fn(string $r) => str_starts_with($currentRoute, $r) ? 'is-active' : '';
@endphp

<nav class="l69-nav" id="l69-navbar">
    <div class="l69-nav__inner">

        {{-- Logo --}}
        <a href="{{ route('landing') }}" class="l69-nav__brand">
            <img src="{{ asset('img/logo-lobby69_.png') }}"
                 alt="LOBBY69"
                 class="l69-nav__logo"
                 onerror="this.style.display='none'">
            <span class="l69-nav__brand-name">LOBBY69</span>
        </a>

        <div class="l69-nav__spacer"></div>

        @auth
        {{-- ── Links autenticado ── --}}
        <ul class="l69-nav__links">
            <li>
                <a href="{{ route('dashboard') }}"
                   class="l69-nav__link {{ $isActive('dashboard') }}">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </li>
            <li>
                <a href="{{ route('explore') }}"
                   class="l69-nav__link {{ $isActive('explore') }}">
                    <i class="fas fa-compass"></i> Explorar
                </a>
            </li>
            <li>
                <a href="{{ route('photos.index') }}"
                   class="l69-nav__link {{ $isActive('photos') }}">
                    <i class="fas fa-images"></i> Mis Fotos
                </a>
            </li>
            {{-- Próximas fases (comentadas, listas para activar) --}}
            {{-- <li>
                <a href="#" class="l69-nav__link">
                    <i class="fas fa-calendar-day"></i> Disponible HOY
                    <span class="l69-nav__badge l69-nav__badge--new">Pronto</span>
                </a>
            </li>
            <li>
                <a href="#" class="l69-nav__link">
                    <i class="fas fa-book-open"></i> Historias
                    <span class="l69-nav__badge l69-nav__badge--new">Pronto</span>
                </a>
            </li> --}}
        </ul>

        <div class="l69-nav__divider"></div>

        {{-- ── Dropdown de usuario ── --}}
        <div class="l69-nav__user" id="navUserDropdown">
            <button class="l69-nav__user-btn" id="navUserBtn" aria-expanded="false">
                @php
                    $navProfile = auth()->user()->profile ?? null;
                    $navAvatar  = $navProfile?->avatar_url ?? asset('img/default-avatar.svg');
                    $navNick    = $navProfile?->nickname ?? auth()->user()->name ?? 'Usuario';
                    $navMembership = auth()->user()->membership_type ?? 'trial';
                @endphp
                <img src="{{ $navAvatar }}"
                     alt="{{ $navNick }}"
                     class="l69-nav__user-avatar"
                     onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
                <span class="l69-nav__user-nick">{{ $navNick }}</span>
                <i class="fas fa-chevron-down l69-nav__user-caret"></i>
            </button>

            <div class="l69-nav__dropdown" id="navDropdownMenu" role="menu">
                {{-- Header del dropdown --}}
                <div class="l69-nav__dropdown-header">
                    <div class="l69-nav__dropdown-name">{{ $navNick }}</div>
                    <div class="l69-nav__dropdown-sub">
                        @if(auth()->user()->isAdmin())
                            <span style="color:#f59e0b;"><i class="fas fa-crown"></i> Administrador</span>
                        @else
                            <span style="text-transform:capitalize;">
                                {{ ucfirst($navMembership) }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Items del dropdown --}}
                <a href="{{ route('profile.public', $navNick) }}"
                   class="l69-nav__dropdown-item" role="menuitem">
                    <i class="fas fa-user"></i> Ver mi perfil
                </a>
                <a href="{{ route('profile.edit') }}"
                   class="l69-nav__dropdown-item" role="menuitem">
                    <i class="fas fa-user-edit"></i> Editar perfil
                </a>
                <a href="{{ route('photos.index') }}"
                   class="l69-nav__dropdown-item" role="menuitem">
                    <i class="fas fa-images"></i> Mis fotos
                </a>
                <a href="{{ route('profile.setup') }}"
                   class="l69-nav__dropdown-item" role="menuitem">
                    <i class="fas fa-sliders-h"></i> Configuración
                </a>

                @if(auth()->user()->isAdmin())
                <div class="l69-nav__dropdown-sep"></div>
                <a href="{{ route('admin.invitations.index') }}"
                   class="l69-nav__dropdown-item" role="menuitem">
                    <i class="fas fa-shield-alt"></i>
                    Panel Admin
                    <span class="l69-nav__badge l69-nav__badge--admin">Admin</span>
                </a>
                @endif

                <div class="l69-nav__dropdown-sep"></div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit"
                            class="l69-nav__dropdown-item l69-nav__dropdown-item--danger"
                            role="menuitem">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Mobile hamburger ── --}}
        <button class="l69-nav__hamburger" id="navHamburger" aria-label="Menú">
            <span></span><span></span><span></span>
        </button>

        @else
        {{-- ── Guest ── --}}
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <a href="{{ route('login') }}" class="l69-nav__btn l69-nav__btn--ghost">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </a>
            <a href="{{ route('invitation.show') }}" class="l69-nav__btn l69-nav__btn--primary">
                <i class="fas fa-envelope"></i> Solicitar Acceso
            </a>
        </div>
        @endauth

    </div>
</nav>

@auth
{{-- ── Mobile Drawer ── --}}
<div class="l69-nav__mobile-drawer" id="navMobileDrawer">
    @php
        $drawerProfile = auth()->user()->profile ?? null;
        $drawerAvatar  = $drawerProfile?->avatar_url ?? asset('img/default-avatar.svg');
        $drawerNick    = $drawerProfile?->nickname ?? auth()->user()->name ?? 'Usuario';
    @endphp
    {{-- Usuario en móvil --}}
    <div class="l69-nav__mobile-user">
        <img src="{{ $drawerAvatar }}"
             alt="{{ $drawerNick }}"
             onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
        <div>
            <div style="font-weight:700;color:#fff;">{{ $drawerNick }}</div>
            <div style="font-size:.8rem;color:#9b8aaa;">
                {{ ucfirst(auth()->user()->membership_type ?? 'trial') }}
            </div>
        </div>
    </div>

    <a href="{{ route('dashboard') }}" class="l69-nav__mobile-link {{ $isActive('dashboard') }}">
        <i class="fas fa-home"></i> Inicio
    </a>
    <a href="{{ route('explore') }}" class="l69-nav__mobile-link {{ $isActive('explore') }}">
        <i class="fas fa-compass"></i> Explorar
    </a>
    <a href="{{ route('photos.index') }}" class="l69-nav__mobile-link {{ $isActive('photos') }}">
        <i class="fas fa-images"></i> Mis Fotos
    </a>
    <a href="{{ route('profile.edit') }}" class="l69-nav__mobile-link">
        <i class="fas fa-user-edit"></i> Editar Perfil
    </a>
    <a href="{{ route('profile.public', $drawerNick) }}" class="l69-nav__mobile-link">
        <i class="fas fa-user"></i> Ver mi Perfil
    </a>

    @if(auth()->user()->isAdmin())
    <div class="l69-nav__mobile-sep"></div>
    <a href="{{ route('admin.invitations.index') }}" class="l69-nav__mobile-link">
        <i class="fas fa-shield-alt"></i> Panel Admin
    </a>
    @endif

    <div class="l69-nav__mobile-sep"></div>
    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="l69-nav__mobile-link"
                style="width:100%;background:none;border:none;cursor:pointer;color:#f87171;">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </form>
</div>

<script>
(function(){
    // ── Dropdown usuario ──
    var userWrap = document.getElementById('navUserDropdown');
    var userBtn  = document.getElementById('navUserBtn');
    if (userBtn && userWrap) {
        userBtn.addEventListener('click', function(e){
            e.stopPropagation();
            var open = userWrap.classList.toggle('is-open');
            userBtn.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', function(e){
            if (!userWrap.contains(e.target)) {
                userWrap.classList.remove('is-open');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') {
                userWrap.classList.remove('is-open');
                userBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ── Hamburger / Mobile drawer ──
    var hamburger = document.getElementById('navHamburger');
    var drawer    = document.getElementById('navMobileDrawer');
    if (hamburger && drawer) {
        hamburger.addEventListener('click', function(){
            var open = hamburger.classList.toggle('is-open');
            drawer.classList.toggle('is-open', open);
            document.body.style.overflow = open ? 'hidden' : '';
        });
        // Cerrar drawer al hacer clic en un link
        drawer.querySelectorAll('a, button[type="submit"]').forEach(function(el){
            el.addEventListener('click', function(){
                hamburger.classList.remove('is-open');
                drawer.classList.remove('is-open');
                document.body.style.overflow = '';
            });
        });
    }
})();
</script>
@endauth
BLADE;

// ============================================================
// 2. LAYOUT APP.BLADE.PHP — Con sistema de 3 columnas
// ============================================================
$appLayout = <<<'BLADE'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LOBBY69') | LOBBY69</title>
    <meta name="description" content="LOBBY69 - La comunidad swinger más discreta de México">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/00-vivid-nights.css') }}">
    @stack('styles')
    <style>
    /* ── Modal crítico — inline para garantizar carga ── */
    .modal-overlay {
        display: none; position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        width: 100vw; height: 100vh;
        background: rgba(44, 62, 80, 0.75);
        z-index: 99999; align-items: center;
        justify-content: center; padding: 1rem;
    }
    .modal-overlay.is-open { display: flex; }
    .modal {
        background: #ffffff; border-radius: 20px;
        max-width: 560px; width: 100%;
        max-height: 88vh; overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.3); position: relative;
    }
    .modal__header {
        display: flex; align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(44,62,80,0.08);
        position: sticky; top: 0; background: #fff; z-index: 1;
    }
    .modal__body { padding: 1.5rem; line-height: 1.7; }
    .modal__body h3 { margin: 1rem 0 0.4rem; font-size: 1rem; font-weight: 600; }
    .modal__body p, .modal__body li { font-size: 0.9rem; color: #4a5568; }
    .modal__body ul { padding-left: 1.5rem; list-style: disc; }
    .modal__footer {
        display: flex; justify-content: flex-end;
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(44,62,80,0.08);
        position: sticky; bottom: 0; background: #fff;
    }
    /* ── Toast ── */
    .toast {
        position: fixed; top: 1.5rem; right: 1.5rem;
        z-index: 99998; padding: 1rem 1.5rem;
        border-radius: 12px; font-weight: 600; font-size: 0.9rem;
        display: flex; align-items: center; gap: 0.75rem;
        max-width: 420px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        animation: toastIn 0.3s ease;
    }
    .toast--success { background: #27AE60; color: #fff; }
    .toast--error   { background: #E74C3C; color: #fff; }
    @keyframes toastIn {
        from { transform: translateX(120%); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }

    /* ══════════════════════════════════════════
       SISTEMA DE LAYOUT 3 COLUMNAS
       ══════════════════════════════════════════ */
    :root {
        --sidebar-left-w: 260px;
        --sidebar-right-w: 240px;
        --content-gap: 1.5rem;
        --sidebar-top: 64px; /* altura del navbar */
    }

    /* Wrapper principal del body con 3 columnas */
    .l69-layout {
        display: grid;
        grid-template-columns: var(--sidebar-left-w) 1fr var(--sidebar-right-w);
        gap: var(--content-gap);
        max-width: 1400px;
        margin: 0 auto;
        padding: 1.5rem 1.25rem;
        min-height: calc(100vh - var(--sidebar-top));
        align-items: start;
    }

    /* Layout de 1 columna (páginas sin sidebar) */
    .l69-layout--single {
        grid-template-columns: 1fr;
        max-width: 860px;
    }

    /* Layout de 2 columnas (con solo sidebar izquierdo) */
    .l69-layout--left-only {
        grid-template-columns: var(--sidebar-left-w) 1fr;
    }

    /* ── Sidebars sticky ── */
    .l69-sidebar {
        position: sticky;
        top: calc(var(--sidebar-top) + 1.25rem);
        max-height: calc(100vh - var(--sidebar-top) - 2rem);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(180,60,120,0.3) transparent;
    }
    .l69-sidebar::-webkit-scrollbar { width: 4px; }
    .l69-sidebar::-webkit-scrollbar-track { background: transparent; }
    .l69-sidebar::-webkit-scrollbar-thumb {
        background: rgba(180,60,120,0.3);
        border-radius: 4px;
    }

    /* ── Tarjeta de sidebar ── */
    .l69-sidebar-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(180,60,120,0.15);
        border-radius: 16px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .l69-sidebar-card__title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(180,60,120,0.8);
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* ── Mini perfil sidebar ── */
    .l69-mini-profile {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(180,60,120,0.15);
    }
    .l69-mini-profile__avatar-wrap {
        position: relative;
        margin-bottom: 0.75rem;
    }
    .l69-mini-profile__avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(180,60,120,0.4);
    }
    .l69-mini-profile__verified {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 20px;
        height: 20px;
        background: #27AE60;
        border-radius: 50%;
        border: 2px solid #0f0a1a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.55rem;
        color: #fff;
    }
    .l69-mini-profile__nick {
        font-weight: 700;
        font-size: 0.95rem;
        color: #fff;
        margin-bottom: 0.2rem;
    }
    .l69-mini-profile__type {
        font-size: 0.75rem;
        color: rgba(180,60,120,0.85);
        margin-bottom: 0.5rem;
    }

    /* Badge de membresía */
    .l69-membership-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.2rem 0.65rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .l69-membership-badge--trial    { background: rgba(107,114,128,0.25); color: #9ca3af; border: 1px solid rgba(107,114,128,0.3); }
    .l69-membership-badge--explorer { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.25); }
    .l69-membership-badge--connectors { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
    .l69-membership-badge--influencer { background: rgba(139,92,246,0.15); color: #a78bfa; border: 1px solid rgba(139,92,246,0.25); }
    .l69-membership-badge--vip_elite  { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
    .l69-membership-badge--Fundador  { background: linear-gradient(135deg,rgba(192,57,43,0.2),rgba(142,68,173,0.2)); color: #e056a0; border: 1px solid rgba(192,57,43,0.3); }

    /* ── Progress bar de perfil ── */
    .l69-profile-progress {
        margin-top: 0.75rem;
    }
    .l69-profile-progress__label {
        display: flex;
        justify-content: space-between;
        font-size: 0.73rem;
        color: rgba(226,217,243,0.6);
        margin-bottom: 0.35rem;
    }
    .l69-profile-progress__bar {
        height: 5px;
        background: rgba(255,255,255,0.08);
        border-radius: 4px;
        overflow: hidden;
    }
    .l69-profile-progress__fill {
        height: 100%;
        background: linear-gradient(90deg, #c0392b, #8e44ad);
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    /* ── Nav links del sidebar ── */
    .l69-sidebar-nav {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .l69-sidebar-nav__item a,
    .l69-sidebar-nav__item button {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.6rem 0.75rem;
        border-radius: 9px;
        color: rgba(226,217,243,0.75);
        text-decoration: none;
        font-size: 0.855rem;
        font-weight: 500;
        transition: background 0.15s, color 0.15s;
        width: 100%;
        border: none;
        background: none;
        cursor: pointer;
        text-align: left;
    }
    .l69-sidebar-nav__item a:hover,
    .l69-sidebar-nav__item button:hover {
        background: rgba(180,60,120,0.12);
        color: #fff;
    }
    .l69-sidebar-nav__item a.is-active {
        background: rgba(180,60,120,0.18);
        color: #e056a0;
    }
    .l69-sidebar-nav__item i {
        width: 16px;
        text-align: center;
        font-size: 0.82rem;
        opacity: 0.8;
    }
    .l69-sidebar-nav__sep {
        height: 1px;
        background: rgba(180,60,120,0.12);
        margin: 0.4rem 0;
    }
    .l69-sidebar-nav__item--danger a,
    .l69-sidebar-nav__item--danger button { color: #f87171; }
    .l69-sidebar-nav__item--danger a:hover,
    .l69-sidebar-nav__item--danger button:hover {
        background: rgba(239,68,68,0.1);
        color: #fca5a5;
    }

    /* ── Stat cards del sidebar derecho ── */
    .l69-stat-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.6rem;
        margin-bottom: 0.75rem;
    }
    .l69-stat {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(180,60,120,0.12);
        border-radius: 10px;
        padding: 0.65rem 0.5rem;
        text-align: center;
    }
    .l69-stat__value {
        font-size: 1.3rem;
        font-weight: 800;
        color: #e056a0;
        line-height: 1;
        margin-bottom: 0.2rem;
    }
    .l69-stat__label {
        font-size: 0.67rem;
        color: rgba(226,217,243,0.5);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* ── Quick action button ── */
    .l69-quick-btn {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem 0.75rem;
        border-radius: 9px;
        background: rgba(180,60,120,0.1);
        border: 1px solid rgba(180,60,120,0.2);
        color: rgba(226,217,243,0.85);
        text-decoration: none;
        font-size: 0.83rem;
        font-weight: 500;
        transition: all 0.15s;
        width: 100%;
        margin-bottom: 0.4rem;
    }
    .l69-quick-btn:hover {
        background: rgba(180,60,120,0.2);
        color: #fff;
        border-color: rgba(180,60,120,0.4);
        transform: translateX(2px);
    }
    .l69-quick-btn i { width: 16px; text-align: center; font-size: 0.82rem; }

    /* ── Responsive ── */
    @media (max-width: 1199px) {
        :root {
            --sidebar-left-w: 220px;
            --sidebar-right-w: 200px;
        }
    }
    @media (max-width: 991px) {
        .l69-layout {
            grid-template-columns: var(--sidebar-left-w) 1fr;
        }
        .l69-sidebar--right { display: none; }
    }
    @media (max-width: 767px) {
        .l69-layout {
            grid-template-columns: 1fr;
            padding: 1rem 0.75rem;
            gap: 1rem;
        }
        .l69-sidebar--left { display: none; }
        .l69-sidebar--right { display: none; }
    }
    </style>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body>
    @include('components.navbar')

    <main>
        @if(session('success'))
            <div class="toast toast--success" id="toast-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="toast toast--error" id="toast-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @auth
        {{-- ── Layout 3 columnas para usuarios autenticados ── --}}
        @if(!in_array(request()->route()?->getName(), [
            'login', 'landing', 'invitation.show', 'invitation.request',
            'password.request', 'password.reset', 'password.change',
            'verification.show', 'verification.pending',
            'admin.invitations.index', 'admin.invitations.show',
            'admin.verifications.index', 'admin.verifications.show',
            'admin.photos.index',
        ]))
        <div class="l69-layout">

            {{-- ── SIDEBAR IZQUIERDO ── --}}
            <aside class="l69-sidebar l69-sidebar--left">
                @include('layouts.sidebar-left')
            </aside>

            {{-- ── CONTENIDO CENTRAL ── --}}
            <div class="l69-layout__content">
                @yield('content')
            </div>

            {{-- ── SIDEBAR DERECHO (contextual) ── --}}
            <aside class="l69-sidebar l69-sidebar--right">
                @include('layouts.sidebar-right')
            </aside>

        </div>
        @else
            @yield('content')
        @endif

        @else
        {{-- ── Layout simple para guests ── --}}
        @yield('content')
        @endauth
    </main>

    @include('components.footer')
    @include('components.legal-modals')

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')

    <script>
    (function () {
        function openModal(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.style.display = "flex";
            document.body.style.overflow = "hidden";
        }
        function closeModal(id) {
            var modal = document.getElementById(id);
            if (!modal) return;
            modal.style.display = "none";
            document.body.style.overflow = "";
        }
        window.openModal  = openModal;
        window.closeModal = closeModal;
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll("[data-modal]").forEach(function (el) {
                el.addEventListener("click", function (e) {
                    e.preventDefault();
                    openModal(el.getAttribute("data-modal"));
                });
            });
            document.querySelectorAll("[data-close]").forEach(function (el) {
                el.addEventListener("click", function (e) {
                    e.preventDefault();
                    closeModal(el.getAttribute("data-close"));
                });
            });
            document.querySelectorAll(".modal-overlay").forEach(function (overlay) {
                overlay.addEventListener("click", function (e) {
                    if (e.target === overlay) {
                        overlay.style.display = "none";
                        document.body.style.overflow = "";
                    }
                });
            });
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape") {
                    document.querySelectorAll(".modal-overlay").forEach(function (m) {
                        if (m.style.display === "flex") {
                            m.style.display = "none";
                        }
                    });
                    document.body.style.overflow = "";
                }
            });
            ["toast-success", "toast-error"].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    setTimeout(function () {
                        el.style.transition = "opacity 0.5s";
                        el.style.opacity = "0";
                        setTimeout(function () { el.remove(); }, 500);
                    }, 5000);
                }
            });
        });
    }());
    </script>
</body>
</html>
BLADE;

// ============================================================
// 3. SIDEBAR IZQUIERDO — Mini perfil + Navegación
// ============================================================
$sidebarLeft = <<<'BLADE'
@php
    $sUser    = auth()->user();
    $sProfile = $sUser->profile ?? null;
    $sAvatar  = $sProfile?->avatar_url ?? asset('img/default-avatar.svg');
    $sNick    = $sProfile?->nickname ?? $sUser->name ?? 'Usuario';
    $sMembership = $sUser->membership_type ?? 'trial';
    $sVerified   = $sUser->identity_verified ?? false;
    $sRoute      = request()->route()?->getName() ?? '';
    $sActive     = fn(string $r) => str_starts_with($sRoute, $r) ? 'is-active' : '';

    // Calcular progreso del perfil
    $sProgress = 0;
    if ($sProfile) {
        $sFields = ['nickname','bio','profile_type','age','gender','location_country'];
        $sFilled = collect($sFields)->filter(fn($f) => !empty($sProfile->$f))->count();
        $sProgress = (int)(($sFilled / count($sFields)) * 100);
        if ($sProfile->avatar_url) $sProgress = min(100, $sProgress + 10);
    }

    $sMembershipLabels = [
        'trial'      => ['label' => 'Trial',      'icon' => 'fa-clock'],
        'explorer'   => ['label' => 'Explorer',   'icon' => 'fa-compass'],
        'connectors' => ['label' => 'Connectors', 'icon' => 'fa-link'],
        'influencer' => ['label' => 'Influencer', 'icon' => 'fa-star'],
        'vip_elite'  => ['label' => 'VIP Elite',  'icon' => 'fa-gem'],
        'Fundador'  => ['label' => 'Fundador',  'icon' => 'fa-crown'],
    ];
    $sMembershipInfo = $sMembershipLabels[$sMembership] ?? $sMembershipLabels['trial'];
@endphp

{{-- ── Mini Perfil ── --}}
<div class="l69-sidebar-card" style="padding-top:1.5rem;">

    <div class="l69-mini-profile">
        <div class="l69-mini-profile__avatar-wrap">
            <img src="{{ $sAvatar }}"
                 alt="{{ $sNick }}"
                 class="l69-mini-profile__avatar"
                 onerror="this.src='{{ asset('img/default-avatar.svg') }}'">
            @if($sVerified)
            <div class="l69-mini-profile__verified" title="Verificado">
                <i class="fas fa-check"></i>
            </div>
            @endif
        </div>
        <div class="l69-mini-profile__nick">{{ $sNick }}</div>
        <div class="l69-mini-profile__type">
            @if($sProfile?->profile_type === 'pareja')
                <i class="fas fa-heart"></i> Pareja
            @elseif($sProfile?->profile_type === 'unicornio')
                <i class="fas fa-star"></i> Unicornio
            @else
                <i class="fas fa-user"></i> Single
            @endif
        </div>
        <span class="l69-membership-badge l69-membership-badge--{{ $sMembership }}">
            <i class="fas {{ $sMembershipInfo['icon'] }}"></i>
            {{ $sMembershipInfo['label'] }}
        </span>

        {{-- Progreso del perfil --}}
        @if($sProgress < 100)
        <div class="l69-profile-progress" style="width:100%;">
            <div class="l69-profile-progress__label">
                <span>Perfil completado</span>
                <span>{{ $sProgress }}%</span>
            </div>
            <div class="l69-profile-progress__bar">
                <div class="l69-profile-progress__fill" style="width:{{ $sProgress }}%"></div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Navegación principal ── --}}
    <ul class="l69-sidebar-nav">
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('dashboard') }}" class="{{ $sActive('dashboard') }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('explore') }}" class="{{ $sActive('explore') }}">
                <i class="fas fa-compass"></i> Explorar
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('photos.index') }}" class="{{ $sActive('photos') }}">
                <i class="fas fa-images"></i> Mis Fotos
            </a>
        </li>

        <li class="l69-sidebar-nav__sep"></li>

        <li class="l69-sidebar-nav__item">
            <a href="{{ route('profile.edit') }}" class="{{ $sActive('profile.edit') }}">
                <i class="fas fa-user-edit"></i> Editar Perfil
            </a>
        </li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('profile.setup') }}" class="{{ $sActive('profile.setup') }}">
                <i class="fas fa-sliders-h"></i> Configuración
            </a>
        </li>

        {{-- Próximas fases --}}
        <li class="l69-sidebar-nav__item" style="opacity:.45;pointer-events:none;">
            <a href="#">
                <i class="fas fa-calendar-day"></i> Disponible HOY
                <span style="font-size:.65rem;background:rgba(180,60,120,.3);padding:.1rem .4rem;border-radius:10px;margin-left:auto;">Pronto</span>
            </a>
        </li>
        <li class="l69-sidebar-nav__item" style="opacity:.45;pointer-events:none;">
            <a href="#">
                <i class="fas fa-book-open"></i> Historias
                <span style="font-size:.65rem;background:rgba(180,60,120,.3);padding:.1rem .4rem;border-radius:10px;margin-left:auto;">Pronto</span>
            </a>
        </li>

        @if($sUser->isAdmin())
        <li class="l69-sidebar-nav__sep"></li>
        <li class="l69-sidebar-nav__item">
            <a href="{{ route('admin.invitations.index') }}" style="color:#fbbf24;">
                <i class="fas fa-shield-alt"></i> Panel Admin
            </a>
        </li>
        @endif

        <li class="l69-sidebar-nav__sep"></li>
        <li class="l69-sidebar-nav__item l69-sidebar-nav__item--danger">
            <form method="POST" action="{{ route('logout') }}" style="margin:0;width:100%;">
                @csrf
                <button type="submit">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </button>
            </form>
        </li>
    </ul>
</div>
BLADE;

// ============================================================
// 4. SIDEBAR DERECHO — Contextual por página
// ============================================================
$sidebarRight = <<<'BLADE'
@php
    $rUser    = auth()->user();
    $rProfile = $rUser->profile ?? null;
    $rRoute   = request()->route()?->getName() ?? '';

    // Fotos pendientes del usuario
    $rPendingPhotos = 0;
    try {
        $rPendingPhotos = \App\Models\Photo::where('user_id', $rUser->id)
                            ->where('status', 'pending')->count();
    } catch(\Exception $e) {}

    // Fotos aprobadas
    $rApprovedPhotos = 0;
    try {
        $rApprovedPhotos = \App\Models\Photo::where('user_id', $rUser->id)
                             ->where('status', 'approved')->count();
    } catch(\Exception $e) {}
@endphp

{{-- ── Stats generales ── --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-chart-bar"></i> Mi Actividad
    </div>
    <div class="l69-stat-grid">
        <div class="l69-stat">
            <div class="l69-stat__value">{{ $rApprovedPhotos }}</div>
            <div class="l69-stat__label">Fotos</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value">
                @if($rPendingPhotos > 0)
                    <span style="color:#f59e0b;">{{ $rPendingPhotos }}</span>
                @else
                    0
                @endif
            </div>
            <div class="l69-stat__label">Pendientes</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value" style="font-size:1rem;">
                @if($rUser->identity_verified ?? false)
                    <i class="fas fa-check-circle" style="color:#27ae60;"></i>
                @else
                    <i class="fas fa-clock" style="color:#f59e0b;"></i>
                @endif
            </div>
            <div class="l69-stat__label">Verificación</div>
        </div>
        <div class="l69-stat">
            <div class="l69-stat__value" style="font-size:.85rem;color:#a78bfa;">
                {{ ucfirst($rUser->membership_type ?? 'trial') }}
            </div>
            <div class="l69-stat__label">Plan</div>
        </div>
    </div>
</div>

{{-- ── Contexto por página ── --}}
@if(str_starts_with($rRoute, 'photos'))
{{-- Contexto: Mis Fotos --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-lightbulb"></i> Consejos
    </div>
    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.6rem;">
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Sube fotos nítidas con buena iluminación
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            El álbum público es visible para todos los verificados
        </li>
        <li style="font-size:.8rem;color:rgba(226,217,243,.7);display:flex;gap:.5rem;align-items:flex-start;">
            <i class="fas fa-check-circle" style="color:#27ae60;margin-top:.15rem;flex-shrink:0;"></i>
            Las fotos se revisan antes de publicarse (24h)
        </li>
    </ul>
    @if($rPendingPhotos > 0)
    <div style="margin-top:1rem;padding:.65rem;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);border-radius:9px;">
        <p style="font-size:.8rem;color:#fbbf24;margin:0;">
            <i class="fas fa-hourglass-half"></i>
            {{ $rPendingPhotos }} foto(s) en revisión
        </p>
    </div>
    @endif
</div>

@elseif(str_starts_with($rRoute, 'profile.edit') || str_starts_with($rRoute, 'profile.setup'))
{{-- Contexto: Editar/Setup perfil --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-tasks"></i> Checklist
    </div>
    @php
        $checks = [
            ['label' => 'Nick definido',       'done' => !empty($rProfile?->nickname)],
            ['label' => 'Foto de perfil',       'done' => !empty($rProfile?->avatar_url)],
            ['label' => 'Descripción (50+ car)','done' => strlen($rProfile?->bio ?? '') >= 50],
            ['label' => 'Ubicación',            'done' => !empty($rProfile?->location_country)],
            ['label' => 'Qué buscas',           'done' => !empty($rProfile?->looking_for)],
        ];
    @endphp
    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.5rem;">
        @foreach($checks as $check)
        <li style="display:flex;align-items:center;gap:.55rem;font-size:.82rem;">
            @if($check['done'])
                <i class="fas fa-check-circle" style="color:#27ae60;flex-shrink:0;"></i>
                <span style="color:rgba(226,217,243,.6);text-decoration:line-through;">{{ $check['label'] }}</span>
            @else
                <i class="far fa-circle" style="color:rgba(226,217,243,.3);flex-shrink:0;"></i>
                <span style="color:rgba(226,217,243,.85);">{{ $check['label'] }}</span>
            @endif
        </li>
        @endforeach
    </ul>
</div>

@elseif(str_starts_with($rRoute, 'explore'))
{{-- Contexto: Explorar --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-filter"></i> Filtros Rápidos
    </div>
    <div style="display:flex;flex-direction:column;gap:.4rem;">
        <a href="{{ route('explore') }}?type=single" class="l69-quick-btn">
            <i class="fas fa-user"></i> Singles
        </a>
        <a href="{{ route('explore') }}?type=pareja" class="l69-quick-btn">
            <i class="fas fa-heart"></i> Parejas
        </a>
        <a href="{{ route('explore') }}?type=unicornio" class="l69-quick-btn">
            <i class="fas fa-star"></i> Unicornios
        </a>
    </div>
</div>

@else
{{-- Contexto: Dashboard y resto --}}
<div class="l69-sidebar-card">
    <div class="l69-sidebar-card__title">
        <i class="fas fa-bolt"></i> Accesos Rápidos
    </div>
    <div style="display:flex;flex-direction:column;gap:.4rem;">
        @if(!($rUser->identity_verified ?? false))
        <a href="{{ route('verification.show') }}" class="l69-quick-btn"
           style="border-color:rgba(245,158,11,.35);color:#fbbf24;">
            <i class="fas fa-id-card"></i> Verificar identidad
        </a>
        @endif
        <a href="{{ route('photos.index') }}" class="l69-quick-btn">
            <i class="fas fa-camera"></i> Subir fotos
        </a>
        <a href="{{ route('explore') }}" class="l69-quick-btn">
            <i class="fas fa-compass"></i> Explorar perfiles
        </a>
        @if($rProfile?->nickname)
        <a href="{{ route('profile.public', $rProfile->nickname) }}" class="l69-quick-btn">
            <i class="fas fa-eye"></i> Ver mi perfil público
        </a>
        @endif
    </div>
</div>
@endif

{{-- ── Verificación pendiente ── --}}
@if(!($rUser->identity_verified ?? false))
<div class="l69-sidebar-card" style="border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.05);">
    <div style="display:flex;align-items:flex-start;gap:.6rem;">
        <i class="fas fa-exclamation-triangle" style="color:#f59e0b;margin-top:.1rem;flex-shrink:0;"></i>
        <div>
            <p style="font-size:.8rem;font-weight:600;color:#fbbf24;margin:0 0 .3rem;">
                Verificación pendiente
            </p>
            <p style="font-size:.75rem;color:rgba(226,217,243,.6);margin:0 0 .65rem;">
                Verifica tu identidad para acceder a todos los perfiles.
            </p>
            <a href="{{ route('verification.show') }}"
               style="font-size:.78rem;color:#f59e0b;font-weight:600;text-decoration:none;">
                Verificar ahora →
            </a>
        </div>
    </div>
</div>
@endif
BLADE;

// ============================================================
// 5. DASHBOARD — Simplificado (sin sidebar duplicado)
// ============================================================
// Leer el dashboard actual y quitar el aside izquierdo redundante
$dashboardPath = $base . '/resources/views/dashboard/index.blade.php';
$dashboardContent = file_get_contents($dashboardPath);

// Verificar si ya tiene el nuevo layout
if (!str_contains($dashboardContent, 'l69-layout')) {
    // Quitar el aside sidebar--left antiguo del dashboard
    // (el nuevo viene desde app.blade.php automáticamente)
    $dashboardContent = preg_replace(
        '/\{\{--\s*──\s*SIDEBAR IZQUIERDO\s*──.*?<\/aside>/s',
        '',
        $dashboardContent
    );
    // Quitar también el wrapper .dashboard si existe (para usar solo el contenido)
    $dashboardContent = str_replace(
        '<section class="dashboard">',
        '<section class="l69-page-content">',
        $dashboardContent
    );
    file_put_contents($dashboardPath, $dashboardContent);
    echo "✓ Dashboard actualizado (sidebar duplicado eliminado)\n";
} else {
    echo "  Dashboard ya tiene nuevo layout\n";
}

// ============================================================
// ESCRITURA DE ARCHIVOS
// ============================================================
$files = [
    'resources/views/components/navbar.blade.php'    => $navbar,
    'resources/views/layouts/app.blade.php'          => $appLayout,
    'resources/views/layouts/sidebar-left.blade.php' => $sidebarLeft,
    'resources/views/layouts/sidebar-right.blade.php'=> $sidebarRight,
];

foreach ($files as $path => $content) {
    $fullPath = $base . '/' . $path;
    $dir = dirname($fullPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "  Creado directorio: $dir\n";
    }
    file_put_contents($fullPath, $content);
    echo "✓ Escrito: $path\n";
}

echo "\n";
echo "════════════════════════════════════════════\n";
echo "  Layout Global listo. Ejecuta:\n";
echo "  C:\\php\\php.exe artisan view:clear\n";
echo "  C:\\php\\php.exe artisan route:clear\n";
echo "  C:\\php\\php.exe artisan serve\n";
echo "════════════════════════════════════════════\n";

