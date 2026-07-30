<!DOCTYPE html>
<html lang="es">
<script>
(function(){
    var t = localStorage.getItem("lobby69-theme");
    if (!t) t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    document.documentElement.setAttribute("data-theme", t);
})();
</script>
<head>
    <script>
        // Anti-flash: aplicar tema ANTES del render
        (function(){
            var t = localStorage.getItem('lobby69-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LOBBY69') | LOBBY69</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    .toast--success { background: #27AE60; color: var(--theme-text); }
    .toast--error   { background: #E74C3C; color: var(--theme-text); }
    @keyframes toastIn {
        from { transform: translateX(120%); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }

    /* ══════════════════════════════════════════
       SISTEMA DE LAYOUT 3 COLUMNAS
       ══════════════════════════════════════════ */
    :root {
        --sidebar-left-w: 280px;
        --sidebar-right-w: 260px;
        --content-gap: 1.25rem;
        --sidebar-top: 64px;
        --nav-h: 64px;
        --bottom-nav-h: 62px;
    }

    @media (max-width: 1400px) {
        :root {
            --sidebar-left-w: 250px;
            --sidebar-right-w: 230px;
        }
    }

    @media (max-width: 1199px) {
        :root {
            --sidebar-left-w: 220px;
            --sidebar-right-w: 200px;
        }
    }

    /* Reset main */
    main {
        display: block;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    /* Wrapper principal del body con 3 columnas */
    .l69-layout {
        display: grid;
        grid-template-columns: var(--sidebar-left-w) 1fr var(--sidebar-right-w);
        gap: var(--content-gap);
        max-width: 100%;
        width: 100%;
        margin: 0 auto;
        padding: 1.5rem 2rem;
        align-items: start;
        box-sizing: border-box;
    }
    .l69-layout__content {
        min-width: 0;
        width: 100%;
        align-self: start;
    }
    .l69-sidebar--left,
    .l69-sidebar--right {
        align-self: start;
        min-width: 0;
    }
    /* Evitar que aside vacio empuje el layout */
    .l69-sidebar--left:empty,
    .l69-sidebar--right:empty {
        display: none;
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
        top: .25rem;
        max-height: calc(100vh - 2rem);
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
        background: var(--theme-surface-2);
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
        color: var(--theme-text);
    }
    .l69-mini-profile__nick {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--theme-text);
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
    .l69-membership-badge--vitalicio  { background: linear-gradient(135deg,rgba(192,57,43,0.2),rgba(142,68,173,0.2)); color: #e056a0; border: 1px solid rgba(192,57,43,0.3); }

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
        color: var(--theme-text);
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
        background: var(--theme-surface-2);
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
        color: var(--theme-text);
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
<body class="{{ request()->routeIs('messages.*') ? 'page-mensajes' : '' }}">
    @include('components.navbar')

    <main style="width:100%;min-width:0;display:block;">
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
        @php
            $noLayout = in_array(request()->route()?->getName(), [
                'login','landing','invitation.show','invitation.request',
                'password.request','password.reset','password.change',
                'verification.show','verification.pending',
                'admin.invitations.index','admin.invitations.show',
                'admin.verifications.index','admin.verifications.show',
                'admin.photos.index',
                'messages.index',
            ]);

        @endphp

        @if($noLayout)
            @yield('content')
        @else
        <div class="l69-layout">

            {{-- ── SIDEBAR IZQUIERDO ── --}}
            <aside class="l69-sidebar l69-sidebar--left">
                @stack('sidebar-left')
            </aside>

            {{-- ── CONTENIDO CENTRAL ── --}}
            <div class="l69-layout__content">
                @yield('content')
            </div>

            {{-- ── SIDEBAR DERECHO ── --}}
            <aside class="l69-sidebar l69-sidebar--right">
                @stack('sidebar-right')
            </aside>

        </div>
        @endif

        @else
        @yield('content')
        @endauth
    </main>

    @include('components.footer')
    @include('components.legal-modals')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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













