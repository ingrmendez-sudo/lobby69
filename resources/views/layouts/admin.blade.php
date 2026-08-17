<!DOCTYPE html>
<html lang="es" data-theme="dark" id="adminRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — LOBBY69</title>
    <link rel="stylesheet" href="{{ asset('css/00-vivid-nights.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    /* ── Admin Layout ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--theme-bg, #0f0a1a);
        color: var(--theme-text, #f0e8ff);
        min-height: 100vh;
        display: flex;
    }

    /* Sidebar */
    .adm-sidebar {
        width: 240px;
        min-height: 100vh;
        height: 100vh;
        background: var(--theme-card, #1a1028);
        border-right: 1px solid var(--theme-border, rgba(108,63,197,.2));
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0;
        z-index: 100;
        overflow-y: auto;
    }

    .adm-sidebar__logo {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--theme-border, rgba(108,63,197,.2));
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .adm-sidebar__logo span {
        font-size: 1.1rem;
        font-weight: 800;
        background: linear-gradient(135deg, #e056a0, #6C3FC5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .adm-sidebar__logo small {
        display: block;
        font-size: .65rem;
        color: var(--theme-muted);
        font-weight: 400;
        -webkit-text-fill-color: var(--theme-muted);
    }

    .adm-nav { padding: 1rem 0; flex: 1; }

    .adm-nav__section {
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .08em;
        color: var(--theme-muted);
        padding: .75rem 1.5rem .35rem;
        text-transform: uppercase;
    }

    .adm-nav__item {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .6rem 1.5rem;
        color: var(--theme-muted);
        text-decoration: none;
        font-size: .875rem;
        font-weight: 500;
        transition: .15s;
        border-left: 3px solid transparent;
        position: relative;
    }

    .adm-nav__item:hover {
        color: var(--theme-text);
        background: rgba(108,63,197,.08);
    }

    .adm-nav__item.active {
        color: #b08df0;
        background: rgba(108,63,197,.12);
        border-left-color: #6C3FC5;
    }

    .adm-nav__item i { width: 16px; text-align: center; font-size: .85rem; }

    .adm-nav__badge {
        margin-left: auto;
        background: #e056a0;
        color: #fff;
        font-size: .65rem;
        font-weight: 700;
        padding: .1rem .45rem;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }

    .adm-nav__badge.yellow { background: #f0c040; color: #1a1000; }
    .adm-nav__badge.green  { background: #28a745; }
    .adm-nav__badge.gray   { background: var(--theme-muted); }

    .adm-sidebar__footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--theme-border);
        font-size: .8rem;
        color: var(--theme-muted);
    }

    .adm-sidebar__footer a {
        color: var(--theme-muted);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .4rem 0;
        transition: .15s;
    }

    .adm-sidebar__footer a:hover { color: var(--theme-text); }

    /* Main content */
    .adm-main {
        margin-left: 240px;
        flex: 1;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Topbar */
    .adm-topbar {
        height: 56px;
        background: var(--theme-card);
        border-bottom: 1px solid var(--theme-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 1.5rem;
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .adm-topbar__title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--theme-text);
    }

    .adm-topbar__actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .adm-topbar__user {
        font-size: .82rem;
        color: var(--theme-muted);
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .adm-topbar__user strong { color: var(--theme-text); }

    /* Page content */
    .adm-content {
        flex: 1;
        padding: 1.5rem;
        max-width: 1400px;
        width: 100%;
        margin: 0 auto;
    }

    
    /* ══ ADMIN MOBILE ══ */
    @media (max-width: 767px) {
        body { display: block; }

        .adm-sidebar {
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(.4,0,.2,1);
            z-index: 9999 !important;
        }
        .adm-sidebar.is-open { transform: translateX(0); z-index: 9999 !important; }

        .adm-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 199;
            background: rgba(0,0,0,0.55);
        }
        .adm-sidebar-overlay.is-visible { display: block; }

        .adm-main { margin-left: 0 !important; }

        .adm-topbar { padding: 0 1rem; }
        .adm-topbar__title { font-size: .9rem; }

        .adm-hamburger {
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: rgba(108,63,197,.15);
            border: 1px solid rgba(108,63,197,.3);
            border-radius: 8px;
            color: var(--theme-text);
            cursor: pointer;
            font-size: 1rem;
            margin-right: .75rem;
        }

        .adm-content { padding: 1rem .75rem; }

        .adm-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: .6rem !important;
        }
    }

    @media (min-width: 768px) {
    @media (min-width: 768px) { .adm-hamburger { display: none !important; } }
        .adm-sidebar-overlay { display: none !important; }
    }
    /* Toast */
    .adm-toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        background: #28a745;
        color: #fff;
        padding: .75rem 1.25rem;
        border-radius: 10px;
        font-size: .875rem;
        font-weight: 600;
        box-shadow: 0 4px 16px rgba(0,0,0,.3);
            z-index: 9999 !important;
        display: none;
        align-items: center;
        gap: .5rem;
        animation: slideUp .25s ease;
    }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    /* ── Modo día admin ── */
    [data-theme="light"] .adm-sidebar {
        width: 240px;
        min-height: 100vh;
        height: 100vh;
        background: var(--theme-card, #1a1028);
        border-right: 1px solid var(--theme-border, rgba(108,63,197,.2));
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0;
        z-index: 100;
        overflow-y: auto;
    }
    [data-theme="light"] .adm-topbar {
        background: #ffffff;
        border-bottom-color: #e5e7eb;
    }
    [data-theme="light"] .adm-card {
        background: #ffffff;
        border-color: #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    [data-theme="light"] body {
        background: #f3f4f6;
    }
    [data-theme="light"] .adm-nav__item {
        color: #374151;
    }
    [data-theme="light"] .adm-nav__item:hover,
    [data-theme="light"] .adm-nav__item.active {
        background: #f3f4f6;
        color: #6C3FC5;
    }
    [data-theme="light"] .adm-nav__section {
        color: #9ca3af;
    }
    [data-theme="light"] table tr {
        border-bottom-color: #e5e7eb;
    }
    [data-theme="light"] input,
    [data-theme="light"] select {
        background: #f9fafb !important;
        border-color: #d1d5db !important;
        color: #111827 !important;
    }

    @stack('styles')
    </style>
    @stack('styles')
</head>
<body>

{{-- ── Sidebar ── --}}
<div class="adm-sidebar-overlay" id="admOverlay"></div>

<aside class="adm-sidebar">
    <div class="adm-sidebar__logo">
        <div>
            <span>LOBBY69</span>
            <small>Panel de Administración</small>
        </div>
    </div>

    <nav class="adm-nav">
        <div class="adm-nav__section">General</div>
        <a href="{{ route('admin.dashboard') }}"
           class="adm-nav__item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>

        <div class="adm-nav__section">Moderación</div>
        <a href="{{ route('admin.photos.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.photos.*') ? 'active' : '' }}">
            <i class="fas fa-images"></i> Fotos
            @if(($pendingPhotos ?? 0) > 0)
                <span class="adm-nav__badge">{{ $pendingPhotos }}</span>
            @endif
        </a>
        <a href="{{ route('admin.videos.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
            <i class="fas fa-video"></i> Videos
            @if(($pendingVideos ?? 0) > 0)
                <span class="adm-nav__badge">{{ $pendingVideos }}</span>
            @endif
        </a>
        <a href="{{ route('admin.verifications.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}">
            <i class="fas fa-id-card"></i> Verificaciones
            @if(($pendingVerifications ?? 0) > 0)
                <span class="adm-nav__badge yellow">{{ $pendingVerifications }}</span>
            @endif
        </a>
        <a href="{{ route('admin.invitations.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.invitations.*') ? 'active' : '' }}">
            <i class="fas fa-envelope"></i> Invitaciones
            @if(($pendingInvitations ?? 0) > 0)
                <span class="adm-nav__badge yellow">{{ $pendingInvitations }}</span>
            @endif
        </a>
        <a href="{{ route('admin.admin.referral-codes.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.admin.referral-codes.*') ? 'active' : '' }}">
            <i class="fas fa-key" style="margin-right:.5rem;"></i> Codigos de Referido
        </a>
        <a href="{{ route('admin.boost.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.boost.*') ? 'active' : '' }}">
            <i class="fas fa-bolt" style="color:#f59e0b;"></i> Boost de perfiles
        </a>




        <div class="adm-nav__section">Usuarios</div>
        <a href="{{ route('admin.users.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Gestión de usuarios
        </a>


                <div class="adm-nav__section">Membresías</div>
        <a href="{{ route('admin.memberships.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.memberships.*') ? 'active' : '' }}">
            <i class="fas fa-crown"></i> Pagos y membresías
        </a>
            <a href="{{ route('admin.memberships.planes') }}"
               class="adm-nav__item {{ request()->routeIs('admin.memberships.planes*') ? 'active' : '' }}"
               style="padding-left:2.5rem;font-size:.82rem;">
                <i class="fas fa-tags"></i>
                <span>Planes y precios</span>
            </a>
<div class="adm-nav__section">Contenido</div>
        <a href="{{ route('admin.events.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Eventos
        </a>
        <a href="{{ route('admin.articles.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
            <i class="fas fa-newspaper"></i> Artículos
        </a>
        <a href="{{ route('admin.article-comments.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.article-comments.*') ? 'active' : '' }}">
            <i class="fas fa-comments"></i> Comentarios
            @if(($pendingArticleComments ?? 0) > 0)
                <span class="adm-nav__badge">{{ $pendingArticleComments }}</span>
            @endif
        </a>
<div class="adm-nav__section">Estadísticas</div>
        <a href="{{ route('admin.stats.index') }}"
           class="adm-nav__item {{ request()->routeIs('admin.stats.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Analíticas
        </a>
    </nav>

    <div class="adm-sidebar__footer">
        <a href="{{ route('login') }}" target="_blank"
        style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;
                color:var(--theme-muted);text-decoration:none;font-size:.8rem;">
            <i class="fas fa-external-link-alt"></i> Ver sitio
        </a>
        <a href="#" onclick="event.preventDefault();document.getElementById('frmLogout').submit();"
        style="display:flex;align-items:center;gap:.5rem;padding:.4rem 0;
                color:#ef4444;text-decoration:none;font-size:.8rem;cursor:pointer;">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
        <form id="frmLogout" method="POST" action="{{ route('logout') }}" style="display:none;">
            @csrf
        </form>
    </div>



</aside>

{{-- ── Main ── --}}
<main class="adm-main">
    <div class="adm-topbar">
        <button class="adm-hamburger" id="admHamburger" aria-label="Abrir menu">&#9776;</button>
        <span class="adm-topbar__title">@yield('page-title', 'Panel Admin')</span>
        <div class="adm-topbar__actions">
            {{-- Toggle dark/light --}}
            <button id="themeToggle" onclick="toggleAdminTheme()"
                    title="Cambiar tema"
                    style="background:none;border:1px solid var(--theme-border);color:var(--theme-muted);
                        border-radius:8px;padding:.3rem .7rem;cursor:pointer;font-size:.85rem;
                        display:flex;align-items:center;gap:.4rem;transition:.2s;">
                <i id="themeIcon" class="fas fa-moon"></i>
                <span id="themeLabel" style="font-size:.75rem;">Modo día</span>
            </button>

            <span class="adm-topbar__user">
                <i class="fas fa-shield-alt" style="color:#6C3FC5;"></i>
                <strong>{{ Auth::user()->username ?? 'Admin' }}</strong>
            </span>
        </div>
    </div>

    <div class="adm-content">
        @if(session('success'))
            <div style="background:#d4edda;color:#155724;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.875rem;">
                ✅ {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>
</main>

<div class="adm-toast" id="admToast"></div>

@stack('scripts')
<script>
function admShowToast(msg, type) {
    var t = document.getElementById('admToast');
    if (!t) return;
    t.textContent = msg;
    t.style.background = type === 'error' ? '#dc3545' : '#28a745';
    t.style.display = 'flex';
    setTimeout(function() { t.style.display = 'none'; }, 3500);
}

(function() {
    // Aplicar tema guardado antes del render para evitar flash
    var saved = localStorage.getItem('adminTheme') || 'dark';
    document.getElementById('adminRoot').setAttribute('data-theme', saved);
})();

function toggleAdminTheme() {
    var root    = document.getElementById('adminRoot');
    var icon    = document.getElementById('themeIcon');
    var label   = document.getElementById('themeLabel');
    var current = root.getAttribute('data-theme');
    var next    = current === 'dark' ? 'light' : 'dark';

    root.setAttribute('data-theme', next);
    localStorage.setItem('adminTheme', next);

    if (next === 'light') {
        icon.className  = 'fas fa-moon';
        label.textContent = 'Modo noche';
    } else {
        icon.className  = 'fas fa-sun';
        label.textContent = 'Modo día';
    }
}

// Sincronizar ícono al cargar según tema guardado
document.addEventListener('DOMContentLoaded', function() {
    var saved = localStorage.getItem('adminTheme') || 'dark';
    var icon  = document.getElementById('themeIcon');
    var label = document.getElementById('themeLabel');
    if (saved === 'light') {
        icon.className    = 'fas fa-moon';
        label.textContent = 'Modo noche';
    } else {
        icon.className    = 'fas fa-sun';
        label.textContent = 'Modo día';
    }
});

</script>
<script>
(function() {
    const sidebar  = document.querySelector('.adm-sidebar');
    const overlay  = document.getElementById('admOverlay');
    const hamburger = document.getElementById('admHamburger');

    function openSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');
    }
    function closeSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');
    }
    if (hamburger) hamburger.addEventListener('click', openSidebar);
    if (overlay)   overlay.addEventListener('click', closeSidebar);

    // Cerrar al seleccionar item de nav en mobile
    document.querySelectorAll('.adm-nav__item').forEach(function(el) {
        el.addEventListener('click', function() {
            if (window.innerWidth < 768) closeSidebar();
        });
    });
})();
</script>
</body>
</html>














