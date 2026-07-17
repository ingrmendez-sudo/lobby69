{{-- NAVBAR LOBBY69 v2 --}}
<style>
:root {
    --nav-h: 64px;
    --bottom-nav-h: 62px;
    /* ── Mapeo navbar → sistema de diseño (global para dropdowns/submenús) ── */
    --bg-navbar:       var(--theme-navbar, #ffffff);
    --bg-card:         var(--theme-card, #ffffff);
    --bg-body:         var(--theme-bg, #FAF9F6);
    --bg-input:        var(--theme-input, #ffffff);
    --text-primary:    var(--theme-text, #2C3E50);
    --text-secondary:  var(--theme-text-soft, #4B5563);
    --text-muted:      var(--theme-muted, #9CA3AF);
    --border-color:    var(--theme-border, rgba(44,62,80,0.12));
    --theme-pink:      #e056a0;
    --theme-pink-soft: rgba(224,86,160,0.10);
    --nav-text:        var(--theme-text, #2C3E50);
    --toggle-bg:       rgba(128,128,128,0.12);
}

/* ── NAVBAR SUPERIOR ── */
.l69-nav {
    position: sticky; top: 0; z-index: 9000;
    height: var(--nav-h);
    background: var(--bg-navbar);
    border-bottom: 1px solid var(--border-color);
    backdrop-filter: blur(12px);
}
.l69-nav__inner {
    max-width: 1400px; margin: 0 auto;
    height: 100%; padding: 0 1.25rem;
    display: flex; align-items: center; gap: 0.75rem;
}
.l69-nav__brand {
    display: flex; align-items: center; gap: 0.5rem;
    text-decoration: none; flex-shrink: 0;
}
.l69-nav__spacer { flex: 1; }

/* Links desktop */
.l69-nav__links {
    display: flex; align-items: center; gap: 0.1rem;
    list-style: none; margin: 0; padding: 0;
}
.l69-nav__link {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.45rem 0.75rem; border-radius: 8px;
    color: var(--text-secondary); text-decoration: none;
    font-size: 0.84rem; font-weight: 500;
    transition: background 0.18s, color 0.18s;
    white-space: nowrap; position: relative;
    background: none; border: none; cursor: pointer;
}
.l69-nav__link:hover { background: var(--theme-pink-soft); color: var(--theme-pink); }
.l69-nav__link.is-active { color: var(--theme-pink); }
.l69-nav__link i { font-size: 0.85rem; }
.l69-nav__badge-dot {
    position: absolute; top: 4px; right: 4px;
    width: 8px; height: 8px; background: #e056a0;
    border-radius: 50%; border: 2px solid var(--bg-navbar);
}
.l69-nav__soon {
    font-size: .6rem; font-weight: 700; padding: .1rem .35rem;
    border-radius: 10px; background: rgba(224,86,160,.2);
    color: #e056a0; margin-left: .3rem;
}

/* Submenu Próximo */
.l69-nav__submenu-wrap { position: relative; }
.l69-nav__submenu {
    position: absolute; top: calc(100% + 6px); left: 0;
    min-width: 180px;
    background: var(--theme-card, #ffffff) !important;
    border: 1px solid var(--theme-border, rgba(44,62,80,0.12)); border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,.25);
    padding: .4rem; list-style: none; margin: 0;
    opacity: 0; visibility: hidden; transform: translateY(-6px);
    transition: all .18s; z-index: 9100;
}
.l69-nav__submenu-wrap:hover .l69-nav__submenu,
.l69-nav__submenu-wrap:focus-within .l69-nav__submenu {
    opacity: 1; visibility: visible; transform: translateY(0);
}
.l69-nav__submenu a {
    display: flex; align-items: center; gap: .6rem;
    padding: .5rem .75rem; border-radius: 8px;
    color: var(--text-secondary); text-decoration: none;
    font-size: .84rem; font-weight: 500; transition: background .15s;
}
.l69-nav__submenu a:hover { background: var(--theme-pink-soft); color: var(--theme-pink); }

/* Divider */
.l69-nav__divider { width: 1px; height: 28px; background: var(--border-color); margin: 0 .2rem; }
.l69-nav__actions { display: flex; align-items: center; gap: 0.5rem; }

/* Theme toggle */
#theme-toggle {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 50%;
    background: var(--toggle-bg, rgba(255,255,255,0.1));
    border: 1px solid var(--border-color);
    cursor: pointer; font-size: 16px; flex-shrink: 0;
    transition: transform 0.2s;
}
#theme-toggle:hover { transform: scale(1.1); }

/* User dropdown */
.l69-nav__user { position: relative; }
.l69-nav__user-btn {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.3rem 0.75rem 0.3rem 0.3rem;
    background: var(--theme-pink-soft);
    border: 1px solid var(--border-color);
    border-radius: 40px; cursor: pointer;
    color: var(--nav-text, var(--text-primary)); font-size: 0.88rem; font-weight: 500;
    transition: background 0.18s;
}
.l69-nav__user-btn:hover { background: rgba(180,60,120,0.2); }
.l69-nav__user-avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(180,60,120,0.4); }
.l69-nav__user-nick { max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.l69-nav__user-caret { font-size: 0.7rem; opacity: 0.6; transition: transform 0.2s; }
.l69-nav__user.is-open .l69-nav__user-caret { transform: rotate(180deg); }
.l69-nav__dropdown {
    position: absolute; top: calc(100% + 8px); right: 0;
    min-width: 210px;
    background: var(--theme-card, #ffffff) !important;
    border: 1px solid var(--theme-border, rgba(44,62,80,0.12)); border-radius: 14px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.3); padding: 0.5rem;
    opacity: 0; visibility: hidden; transform: translateY(-8px);
    transition: opacity 0.18s, transform 0.18s, visibility 0.18s; z-index: 9100;
}
.l69-nav__user.is-open .l69-nav__dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
.l69-nav__dropdown-header { padding: 0.6rem 0.75rem 0.5rem; border-bottom: 1px solid var(--border-color); margin-bottom: 0.4rem; }
.l69-nav__dropdown-name { font-weight: 700; font-size: 0.9rem; color: var(--text-primary); }
.l69-nav__dropdown-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; }
.l69-nav__dropdown-item {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.55rem 0.75rem; border-radius: 8px;
    color: var(--text-secondary); text-decoration: none;
    font-size: 0.86rem; font-weight: 500; transition: background 0.15s;
    width: 100%; border: none; background: none; cursor: pointer; text-align: left;
}
.l69-nav__dropdown-item:hover { background: var(--theme-pink-soft); color: var(--text-primary); }
.l69-nav__dropdown-item i { width: 16px; text-align: center; font-size: 0.82rem; }
.l69-nav__dropdown-item--danger { color: #f87171; }
.l69-nav__dropdown-item--danger:hover { background: rgba(239,68,68,0.12); color: #fca5a5; }
.l69-nav__dropdown-sep { height: 1px; background: var(--border-color); margin: 0.4rem 0; }

/* Búsqueda */
.l69-nav__search-wrap { position: relative; }
.l69-nav__search-input {
    height: 34px; padding: 0 0.85rem 0 2.2rem;
    border-radius: 20px; border: 1px solid var(--border-color);
    background: var(--bg-input); color: var(--text-primary);
    font-size: 0.85rem; width: 180px; transition: width 0.2s; outline: none;
}
.l69-nav__search-input:focus { width: 240px; border-color: rgba(180,60,120,0.5); }
.l69-nav__search-icon {
    position: absolute; left: 0.7rem; top: 50%;
    transform: translateY(-50%); color: var(--text-muted);
    font-size: 0.8rem; pointer-events: none;
}
.l69-nav__search-results {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0;
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    overflow: hidden; display: none; z-index: 9200;
}
.l69-nav__search-results.has-results { display: block; }
.l69-search-result-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.65rem 1rem; text-decoration: none; transition: background 0.15s;
}
.l69-search-result-item:hover { background: var(--theme-pink-soft); }
.l69-search-result-item img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
.l69-search-result-nick { font-size: 0.88rem; font-weight: 600; color: var(--text-primary); }
.l69-search-result-meta { font-size: 0.75rem; color: var(--text-muted); }
.l69-search-online-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; flex-shrink: 0; }
.l69-search-result-all {
    display: block; padding: 0.6rem 1rem; text-align: center;
    font-size: 0.82rem; color: var(--theme-pink);
    border-top: 1px solid var(--border-color); text-decoration: none;
}
.l69-search-result-all:hover { background: var(--theme-pink-soft); }

/* ── BOTTOM NAV (móvil) ── */
.l69-bottom-nav {
    display: none;
    position: fixed; bottom: 0; left: 0; right: 0;
    height: var(--bottom-nav-h);
    background: var(--bg-navbar);
    border-top: 1px solid var(--border-color);
    backdrop-filter: blur(12px);
    z-index: 8990;
    padding-bottom: env(safe-area-inset-bottom);
}
.l69-bottom-nav__inner {
    display: flex; height: 100%;
    align-items: center; justify-content: space-around;
}
.l69-bottom-nav__item {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 3px; flex: 1; height: 100%;
    color: var(--text-muted); text-decoration: none;
    font-size: .65rem; font-weight: 600;
    transition: color .15s; position: relative;
    background: none; border: none; cursor: pointer;
}
.l69-bottom-nav__item i { font-size: 1.2rem; }
.l69-bottom-nav__item.is-active { color: var(--theme-pink); }
.l69-bottom-nav__item:hover { color: var(--theme-pink); }
.l69-bottom-nav__plus {
    width: 48px; height: 48px; border-radius: 50%;
    background: linear-gradient(135deg, #e056a0, #8e44ad);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.4rem; box-shadow: 0 4px 16px rgba(224,86,160,.4);
    border: none; cursor: pointer; transition: transform .15s;
}
.l69-bottom-nav__plus:hover { transform: scale(1.08); }
.l69-bottom-nav__badge {
    position: absolute; top: 6px; right: calc(50% - 18px);
    min-width: 16px; height: 16px; background: #e056a0;
    border-radius: 8px; font-size: .6rem; font-weight: 700;
    color: #fff; display: none; align-items: center; justify-content: center;
    padding: 0 3px; border: 2px solid var(--bg-navbar);
}

/* Drawer móvil (hamburger) */
.l69-nav__hamburger {
    display: none; flex-direction: column; justify-content: space-between;
    width: 26px; height: 18px; background: none; border: none;
    cursor: pointer; padding: 0; flex-shrink: 0;
}
.l69-nav__hamburger span { display: block; height: 2px; background: var(--nav-text, var(--text-primary)); border-radius: 2px; transition: all 0.25s; }
.l69-nav__hamburger.is-open span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.l69-nav__hamburger.is-open span:nth-child(2) { opacity: 0; }
.l69-nav__hamburger.is-open span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
.l69-nav__mobile-drawer {
    position: fixed; top: var(--nav-h); left: 0; right: 0; bottom: var(--bottom-nav-h);
    background: var(--bg-body); z-index: 8999;
    padding: 1rem 1.25rem 1.5rem;
    display: flex; flex-direction: column; gap: 0.25rem;
    overflow-y: auto; transform: translateX(-100%);
    transition: transform 0.28s ease;
    border-top: 1px solid var(--border-color);
}
.l69-nav__mobile-drawer.is-open { transform: translateX(0); }
.l69-nav__mobile-link {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1rem; border-radius: 10px;
    color: var(--text-secondary); text-decoration: none;
    font-size: .95rem; font-weight: 500; transition: background 0.15s;
}
.l69-nav__mobile-link:hover { background: var(--theme-pink-soft); color: var(--text-primary); }
.l69-nav__mobile-section {
    font-size: .7rem; font-weight: 700; color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .06em;
    padding: .75rem 1rem .25rem;
}

/* Plus modal */
.l69-plus-modal {
    position: fixed; bottom: calc(var(--bottom-nav-h) + 12px); left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 16px; box-shadow: 0 16px 48px rgba(0,0,0,.3);
    padding: .75rem; min-width: 200px;
    opacity: 0; visibility: hidden;
    transition: all .2s; z-index: 9050;
    display: flex; flex-direction: column; gap: .25rem;
}
.l69-plus-modal.is-open { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
.l69-plus-modal a {
    display: flex; align-items: center; gap: .75rem;
    padding: .65rem .9rem; border-radius: 10px;
    color: var(--text-secondary); text-decoration: none;
    font-size: .9rem; font-weight: 500; transition: background .15s;
}
.l69-plus-modal a:hover { background: var(--theme-pink-soft); color: var(--theme-pink); }
.l69-plus-modal a i { width: 20px; text-align: center; color: var(--theme-pink); }

/* Notif badge */
.l69-notif-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 50%;
    background: var(--toggle-bg, rgba(255,255,255,0.1));
    border: 1px solid var(--border-color);
    color: var(--nav-text, var(--text-primary)); text-decoration: none;
    transition: transform .2s; position: relative;
}
.l69-notif-btn:hover { transform: scale(1.1); }

/* Responsive */
@media (max-width: 767px) {
    .l69-nav__hamburger { display: flex; }
    .l69-nav__links, .l69-nav__divider, .l69-nav__search-wrap { display: none !important; }
    .l69-bottom-nav { display: block; }
    body { padding-bottom: var(--bottom-nav-h); }
}
@media (min-width: 768px) {
    .l69-nav__mobile-drawer, .l69-bottom-nav { display: none !important; }
}
</style>

{{-- ══ NAVBAR SUPERIOR ══ --}}
<nav class="l69-nav" id="mainNav" role="navigation">
    <div class="l69-nav__inner">

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="l69-nav__brand">
            <img src="{{ asset('img/logo-lobby69_.png') }}" alt="Lobby69" style="height:36px;width:auto;object-fit:contain;">
        </a>

        {{-- Links principales desktop --}}
        @auth
        <ul class="l69-nav__links">
            <li><a href="{{ route('dashboard') }}" class="l69-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <i class="fas fa-home"></i> Inicio
            </a></li>
            <li><a href="/explorar" class="l69-nav__link {{ request()->is('explorar*') ? 'is-active' : '' }}">
                <i class="fas fa-compass"></i> Explorar
            </a></li>
            <li><a href="/videos" class="l69-nav__link {{ request()->is('videos*') ? 'is-active' : '' }}">
                <i class="fas fa-play-circle"></i> Videos
            </a></li>
            <li><a href="/mensajes" class="l69-nav__link {{ request()->is('mensajes*') ? 'is-active' : '' }}" style="position:relative;">
                <i class="fas fa-envelope"></i> Mensajes
                <span id="msgBadgeDesktop" class="l69-nav__badge-dot" style="display:none;"></span>
            </a></li>
            <li><a href="/eventos" class="l69-nav__link {{ request()->is('eventos*') ? 'is-active' : '' }}">
                <i class="fas fa-calendar-alt"></i> Eventos
            </a></li>
            <li><a href="/noticias" class="l69-nav__link {{ request()->is('noticias*') ? 'is-active' : '' }}">
                <i class="fas fa-newspaper"></i> Noticias
            </a></li>
            <li class="l69-nav__submenu-wrap">
                <button class="l69-nav__link">
                    <i class="fas fa-rocket"></i> Más <i class="fas fa-chevron-down" style="font-size:.6rem;margin-left:.2rem;"></i>
                </button>
                <ul class="l69-nav__submenu">
                    <li><a href="{{ route('photos.index') }}"><i class="fas fa-images"></i> Mis Fotos</a></li>
                    <li><a href="#"><i class="fas fa-film"></i> Historias <span class="l69-nav__soon">Pronto</span></a></li>
                    <li><a href="#"><i class="fas fa-video"></i> Videochat <span class="l69-nav__soon">Pronto</span></a></li>
                    <li><a href="#"><i class="fas fa-bullhorn"></i> Anuncios <span class="l69-nav__soon">Pronto</span></a></li>
                </ul>
            </li>
            @if(auth()->user()->role === 'admin')
            <li><a href="/admin" class="l69-nav__link">
                <i class="fas fa-shield-alt"></i> Admin
            </a></li>
            @endif
        </ul>
        @endauth

        <div class="l69-nav__spacer"></div>

        {{-- Búsqueda --}}
        @auth
        <div class="l69-nav__search-wrap">
            <i class="fas fa-search l69-nav__search-icon"></i>
            <input type="text" id="navSearchInput" class="l69-nav__search-input" placeholder="Buscar perfiles..." autocomplete="off"/>
            <div id="navSearchResults" class="l69-nav__search-results"></div>
        </div>
        @endauth

        {{-- Acciones --}}
        <div class="l69-nav__actions">
            <button id="theme-toggle" title="Cambiar tema" aria-label="Cambiar tema">🌙</button>

            @auth
            {{-- Notificaciones --}}
            <a href="{{ route('notifications.index') }}" class="l69-notif-btn" title="Notificaciones" id="notifBtn">
                <i class="fas fa-bell" style="font-size:.9rem;"></i>
                <span id="notifBadge" style="display:none;position:absolute;top:-3px;right:-3px;min-width:16px;height:16px;background:#e056a0;border-radius:8px;font-size:.6rem;font-weight:700;color:#fff;align-items:center;justify-content:center;padding:0 3px;">0</span>
            </a>
            <div class="l69-nav__divider"></div>

            {{-- Usuario --}}
            <div class="l69-nav__user" id="navUserDropdown">
                <button class="l69-nav__user-btn" id="navUserBtn" aria-expanded="false">
                    @php $np = auth()->user()->profile; @endphp
                    <img src="{{ $np?->avatar_url ? asset('storage/'.$np->avatar_url) : asset('img/default-avatar.svg') }}"
                         class="l69-nav__user-avatar"
                         onerror="this.src='{{ asset('img/default-avatar.svg') }}'"
                         alt="avatar">
                    <span class="l69-nav__user-nick">{{ $np?->nickname ?? auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down l69-nav__user-caret"></i>
                </button>
                <div class="l69-nav__dropdown">
                    <div class="l69-nav__dropdown-header">
                        <div class="l69-nav__dropdown-name">{{ $np?->nickname ?? auth()->user()->name }}</div>
                        <div class="l69-nav__dropdown-sub">{{ ucfirst(auth()->user()->membership_type ?? 'trial') }}</div>
                    </div>
                    <a href="{{ $np?->nickname ? route('profile.show', ['nickname' => $np->nickname]) : '#' }}" class="l69-nav__dropdown-item">
                        <i class="fas fa-user"></i> Mi Perfil
                    </a>
                    <a href="{{ route('photos.index') }}" class="l69-nav__dropdown-item">
                        <i class="fas fa-images"></i> Mis Fotos
                    </a>
                    <a href="{{ route('profile.edit') }}" class="l69-nav__dropdown-item">
                        <i class="fas fa-edit"></i> Editar Perfil
                    </a>
                    <div class="l69-nav__dropdown-sep"></div>
                    <a href="/mensajes" class="l69-nav__dropdown-item">
                        <i class="fas fa-envelope"></i> Mensajes
                    </a>
                    <a href="/eventos" class="l69-nav__dropdown-item">
                        <i class="fas fa-calendar-alt"></i> Eventos
                    </a>
                    <a href="/noticias" class="l69-nav__dropdown-item">
                        <i class="fas fa-newspaper"></i> Noticias
                    </a>
                    <div class="l69-nav__dropdown-sep"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="l69-nav__dropdown-item l69-nav__dropdown-item--danger">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>

            {{-- Hamburger móvil --}}
            <button class="l69-nav__hamburger" id="navHamburger" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
            @endauth

            @guest
            <a href="{{ route('login') }}" class="l69-nav__btn l69-nav__btn--ghost">Entrar</a>
            <a href="{{ route('invitation.show') }}" class="l69-nav__btn l69-nav__btn--primary">Solicitar acceso</a>
            @endguest
        </div>
    </div>
</nav>

{{-- ══ DRAWER MÓVIL ══ --}}
@auth
<div class="l69-nav__mobile-drawer" id="mobileDrawer">
    @php $np2 = auth()->user()->profile; @endphp
    <div class="l69-nav__mobile-section">Navegación</div>
    <a href="{{ route('dashboard') }}" class="l69-nav__mobile-link"><i class="fas fa-home"></i> Inicio</a>
    <a href="/explorar" class="l69-nav__mobile-link"><i class="fas fa-compass"></i> Explorar</a>
    <a href="/videos" class="l69-nav__mobile-link"><i class="fas fa-play-circle"></i> Videos</a>
    <a href="/eventos" class="l69-nav__mobile-link"><i class="fas fa-calendar-alt"></i> Eventos</a>
    <a href="/noticias" class="l69-nav__mobile-link"><i class="fas fa-newspaper"></i> Noticias</a>
    <div class="l69-nav__mobile-section">Mi cuenta</div>
    <a href="{{ $np2?->nickname ? route('profile.show', ['nickname' => $np2->nickname]) : '#' }}" class="l69-nav__mobile-link"><i class="fas fa-user"></i> Mi Perfil</a>
    <a href="{{ route('photos.index') }}" class="l69-nav__mobile-link"><i class="fas fa-images"></i> Mis Fotos</a>
    <a href="{{ route('profile.edit') }}" class="l69-nav__mobile-link"><i class="fas fa-edit"></i> Editar Perfil</a>
    <a href="/mensajes" class="l69-nav__mobile-link"><i class="fas fa-envelope"></i> Mensajes</a>
    <a href="{{ route('notifications.index') }}" class="l69-nav__mobile-link"><i class="fas fa-bell"></i> Notificaciones</a>
    <div class="l69-nav__mobile-section">Próximamente</div>
    <a href="#" class="l69-nav__mobile-link"><i class="fas fa-film"></i> Historias <span class="l69-nav__soon">Pronto</span></a>
    <a href="#" class="l69-nav__mobile-link"><i class="fas fa-video"></i> Videochat <span class="l69-nav__soon">Pronto</span></a>
    <a href="#" class="l69-nav__mobile-link"><i class="fas fa-bullhorn"></i> Anuncios <span class="l69-nav__soon">Pronto</span></a>
    @if(auth()->user()->role === 'admin')
    <div class="l69-nav__mobile-section">Admin</div>
    <a href="/admin" class="l69-nav__mobile-link"><i class="fas fa-shield-alt"></i> Panel Admin</a>
    @endif
    <div style="height:1px;background:var(--border-color);margin:.75rem 0;"></div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="l69-nav__mobile-link" style="width:100%;background:none;border:none;cursor:pointer;color:#f87171;">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </button>
    </form>
</div>

{{-- ══ BOTTOM NAV MÓVIL ══ --}}
<nav class="l69-bottom-nav" aria-label="Navegación principal">
    <div class="l69-bottom-nav__inner">
        <a href="{{ route('dashboard') }}" class="l69-bottom-nav__item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <i class="fas fa-home"></i><span>Inicio</span>
        </a>
        <a href="/explorar" class="l69-bottom-nav__item {{ request()->is('explorar*') ? 'is-active' : '' }}">
            <i class="fas fa-compass"></i><span>Explorar</span>
        </a>
        <button class="l69-bottom-nav__item" id="plusBtn" aria-label="Subir contenido">
            <span class="l69-bottom-nav__plus"><i class="fas fa-plus"></i></span>
        </button>
        <a href="/mensajes" class="l69-bottom-nav__item {{ request()->is('mensajes*') ? 'is-active' : '' }}" style="position:relative;">
            <span id="msgBadgeMobile" class="l69-bottom-nav__badge">0</span>
            <i class="fas fa-envelope"></i><span>Mensajes</span>
        </a>
        <a href="{{ $np2?->nickname ? route('profile.show', ['nickname' => $np2->nickname]) : '#' }}" class="l69-bottom-nav__item {{ request()->routeIs('profile.show') ? 'is-active' : '' }}">
            <i class="fas fa-user"></i><span>Perfil</span>
        </a>
    </div>
</nav>

{{-- Modal Plus --}}
<div class="l69-plus-modal" id="plusModal">
    <a href="{{ route('photos.index') }}"><i class="fas fa-camera"></i> Subir Foto</a>
    <a href="/videos"><i class="fas fa-video"></i> Subir Video</a>
</div>
@endauth

<script>
(function(){
    // ── Theme Toggle ──
    var btn = document.getElementById('theme-toggle');
    function applyTheme(t){
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('lobby69-theme', t);
        btn.textContent = t === 'dark' ? '☀️' : '🌙';
    }
    // Sincronizar ícono al cargar
    var current = localStorage.getItem('lobby69-theme') ||
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(current);

    btn.addEventListener('click', function(){
        var now = document.documentElement.getAttribute('data-theme');
        applyTheme(now === 'dark' ? 'light' : 'dark');
    });

    // ── Nav User Dropdown ──
    var userBtn  = document.getElementById('navUserBtn');
    var userDrop = document.getElementById('navUserDropdown');
    if(userBtn && userDrop){
        userBtn.addEventListener('click', function(e){
            e.stopPropagation();
            userDrop.classList.toggle('is-open');
            userBtn.setAttribute('aria-expanded', userDrop.classList.contains('is-open'));
        });
        document.addEventListener('click', function(){ userDrop.classList.remove('is-open'); });
    }

    // ── Hamburger + Drawer ──
    var ham    = document.getElementById('navHamburger');
    var drawer = document.getElementById('mobileDrawer');
    if(ham && drawer){
        ham.addEventListener('click', function(){
            ham.classList.toggle('is-open');
            drawer.classList.toggle('is-open');
        });
    }

    // ── Plus Modal ──
    var plusBtn   = document.getElementById('plusBtn');
    var plusModal = document.getElementById('plusModal');
    if(plusBtn && plusModal){
        plusBtn.addEventListener('click', function(e){
            e.stopPropagation();
            plusModal.classList.toggle('is-open');
        });
        document.addEventListener('click', function(){ plusModal.classList.remove('is-open'); });
    }

    // ── Búsqueda en vivo ──
    var searchInput   = document.getElementById('navSearchInput');
    var searchResults = document.getElementById('navSearchResults');
    if(searchInput && searchResults){
        var debounceTimer;
        searchInput.addEventListener('input', function(){
            clearTimeout(debounceTimer);
            var q = this.value.trim();
            if(q.length < 2){ searchResults.innerHTML=''; searchResults.classList.remove('has-results'); return; }
            debounceTimer = setTimeout(function(){
                fetch('/api/search/profiles?q=' + encodeURIComponent(q))
                    .then(function(r){ return r.json(); })
                    .then(function(data){
                        if(!data.length){ searchResults.innerHTML=''; searchResults.classList.remove('has-results'); return; }
                        var html = data.map(function(u){
                            return '<a href="/u/'+u.nickname+'" class="l69-search-result-item">'
                                + (u.is_online ? '<span class="l69-search-online-dot"></span>' : '')
                                + '<img src="'+(u.avatar||'/img/default-avatar.svg')+'" onerror="this.src=\'/img/default-avatar.svg\'">'
                                + '<div><div class="l69-search-result-nick">'+u.nickname+'</div>'
                                + '<div class="l69-search-result-meta">'+u.city+'</div></div></a>';
                        }).join('');
                        html += '<a href="/explorar?q='+encodeURIComponent(q)+'" class="l69-search-result-all">Ver todos los resultados</a>';
                        searchResults.innerHTML = html;
                        searchResults.classList.add('has-results');
                    })
                    .catch(function(){ searchResults.innerHTML=''; searchResults.classList.remove('has-results'); });
            }, 300);
        });
        document.addEventListener('click', function(e){
            if(!searchInput.contains(e.target) && !searchResults.contains(e.target)){
                searchResults.innerHTML=''; searchResults.classList.remove('has-results');
            }
        });
    }

    // ── Badge de mensajes no leídos ──
    function refreshBadges(){
        fetch('/api/messages/unread-count')
            .then(function(r){ return r.ok ? r.json() : {count:0}; })
            .then(function(d){
                var n = d.count || 0;
                var bdsk = document.getElementById('msgBadgeDesktop');
                var bmob = document.getElementById('msgBadgeMobile');
                if(bdsk) bdsk.style.display = n > 0 ? 'block' : 'none';
                if(bmob){ bmob.textContent = n > 0 ? (n > 99 ? '99+' : n) : ''; bmob.style.display = n > 0 ? 'flex' : 'none'; }
            }).catch(function(){});
    }
    refreshBadges();
    setInterval(refreshBadges, 60000);

    // ── Badge de notificaciones ──
    function refreshNotifBadge(){
        fetch('/api/notifications/unread-count')
            .then(function(r){ return r.ok ? r.json() : {count:0}; })
            .then(function(d){
                var n = d.count || 0;
                var badge = document.getElementById('notifBadge');
                if(badge){ badge.textContent = n > 0 ? (n > 99 ? '99+' : n) : '0'; badge.style.display = n > 0 ? 'flex' : 'none'; }
            }).catch(function(){});
    }
    refreshNotifBadge();
    setInterval(refreshNotifBadge, 60000);
})();
</script>


