<?php
/**
 * fix_theme_system.php
 * Sistema día/noche + colores corregidos + tarjeta compacta
 * Ejecutar: C:\php\php.exe fix_theme_system.php
 */

$base = __DIR__;

function writeFile(string $path, string $content): void {
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($path, $content);
    echo "✓ $path\n";
}

// ============================================================
// 1. VARIABLES DE TEMA — Añadir al final de vivid-nights.css
// ============================================================
$themeVars = <<<'CSS'

/* ============================================================
   LOBBY69 — SISTEMA DE TEMAS DÍA / NOCHE
   ============================================================ */

/* ── MODO DÍA (default) ── */
:root,
[data-theme="light"] {
    --theme-bg:           #faf9f7;
    --theme-bg-2:         #f3f0f7;
    --theme-surface:      #ffffff;
    --theme-surface-2:    #f8f5fc;
    --theme-surface-3:    #f0ebf8;
    --theme-border:       rgba(180, 60, 120, 0.15);
    --theme-border-soft:  rgba(0, 0, 0, 0.07);
    --theme-text:         #1a1523;
    --theme-text-2:       #4a3f5c;
    --theme-text-3:       #7c6e8a;
    --theme-text-muted:   #a394b0;
    --theme-accent:       #c0392b;
    --theme-accent-2:     #8e44ad;
    --theme-accent-soft:  rgba(192, 57, 43, 0.1);
    --theme-pink:         #d63080;
    --theme-pink-soft:    rgba(214, 48, 128, 0.1);
    --theme-pink-border:  rgba(214, 48, 128, 0.25);
    --theme-success:      #16a34a;
    --theme-warning:      #d97706;
    --theme-danger:       #dc2626;
    --theme-online:       #16a34a;
    --theme-shadow:       0 4px 20px rgba(100, 50, 120, 0.1);
    --theme-shadow-md:    0 8px 32px rgba(100, 50, 120, 0.14);
    --theme-shadow-lg:    0 16px 48px rgba(100, 50, 120, 0.18);
    --theme-navbar-bg:    rgba(255, 255, 255, 0.97);
    --theme-navbar-text:  #1a1523;
    --theme-navbar-border:rgba(180, 60, 120, 0.2);
    --theme-dropdown-bg:  #ffffff;
    --theme-gradient:     linear-gradient(135deg, #c0392b, #8e44ad);
    --theme-ring-opacity: 0.6;
    --theme-icon-opacity: 0.7;
}

/* ── MODO NOCHE ── */
[data-theme="dark"] {
    --theme-bg:           #0f0a1a;
    --theme-bg-2:         #150d26;
    --theme-surface:      #1e1232;
    --theme-surface-2:    #251840;
    --theme-surface-3:    #2d1e4e;
    --theme-border:       rgba(180, 60, 120, 0.22);
    --theme-border-soft:  rgba(255, 255, 255, 0.06);
    --theme-text:         #f0eaf8;
    --theme-text-2:       #d4c8e8;
    --theme-text-3:       #a992c4;
    --theme-text-muted:   #7a6690;
    --theme-accent:       #e05060;
    --theme-accent-2:     #a855f7;
    --theme-accent-soft:  rgba(224, 80, 96, 0.15);
    --theme-pink:         #e056a0;
    --theme-pink-soft:    rgba(224, 86, 160, 0.12);
    --theme-pink-border:  rgba(224, 86, 160, 0.25);
    --theme-success:      #22c55e;
    --theme-warning:      #f59e0b;
    --theme-danger:       #ef4444;
    --theme-online:       #22c55e;
    --theme-shadow:       0 4px 20px rgba(0, 0, 0, 0.4);
    --theme-shadow-md:    0 8px 32px rgba(0, 0, 0, 0.5);
    --theme-shadow-lg:    0 16px 48px rgba(0, 0, 0, 0.6);
    --theme-navbar-bg:    rgba(15, 10, 26, 0.97);
    --theme-navbar-text:  #f0eaf8;
    --theme-navbar-border:rgba(180, 60, 120, 0.25);
    --theme-dropdown-bg:  #1e1232;
    --theme-gradient:     linear-gradient(135deg, #c0392b, #8e44ad);
    --theme-ring-opacity: 0.5;
    --theme-icon-opacity: 0.85;
}

/* ── Transición suave al cambiar tema ── */
*, *::before, *::after {
    transition: background-color 0.25s ease,
                border-color 0.25s ease,
                color 0.2s ease,
                box-shadow 0.25s ease;
}
/* Excluir elementos donde la transición genera glitch */
img, svg, video, canvas,
[class*="fa-"],
.dsb-progress-fill,
.l69-nav__search-wrap { transition: none !important; }

/* ── Body con variables de tema ── */
body {
    background-color: var(--theme-bg) !important;
    color: var(--theme-text) !important;
}

CSS;

$cssPath = $base . '/public/css/00-vivid-nights.css';
$cssContent = file_get_contents($cssPath);
if (!str_contains($cssContent, 'SISTEMA DE TEMAS')) {
    file_put_contents($cssPath, $cssContent . $themeVars);
    echo "✓ public/css/00-vivid-nights.css (variables de tema añadidas)\n";
} else {
    echo "  vivid-nights.css ya tiene variables de tema\n";
}

// ============================================================
// 2. NAVBAR — Toggle de tema + variables de color corregidas
// ============================================================
$navbarPath = $base . '/resources/views/components/navbar.blade.php';
$navbarContent = file_get_contents($navbarPath);

// Reemplazar las variables CSS del navbar para usar --theme-*
$oldNavVars = <<<'CSS'
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
CSS;

$newNavVars = <<<'CSS'
/* ── Variables del navbar (usan tema global) ── */
:root {
    --nav-h: 64px;
}
.l69-nav {
    --nav-bg:          var(--theme-navbar-bg);
    --nav-border:      var(--theme-navbar-border);
    --nav-text:        var(--theme-navbar-text);
    --nav-text-muted:  var(--theme-text-muted);
    --nav-accent:      var(--theme-accent);
    --nav-accent-2:    var(--theme-accent-2);
    --nav-hover-bg:    var(--theme-pink-soft);
    --nav-active-color:var(--theme-pink);
    --nav-dropdown-bg: var(--theme-dropdown-bg);
    --nav-shadow:      var(--theme-shadow-md);
}
CSS;

$navbarContent = str_replace($oldNavVars, $newNavVars, $navbarContent);

// Agregar botón de toggle después del spacer (antes de los links)
if (!str_contains($navbarContent, 'themeToggleBtn')) {
    $toggleBtn = <<<'HTML'

        {{-- ── Toggle Día/Noche ── --}}
        <button class="l69-nav__theme-toggle" id="themeToggleBtn"
                title="Cambiar tema" aria-label="Cambiar tema">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>

HTML;
    $navbarContent = str_replace(
        "{{-- ── Barra de búsqueda expandible ── --}}",
        $toggleBtn . "        {{-- ── Barra de búsqueda expandible ── --}}",
        $navbarContent
    );
}

// Agregar CSS del toggle
$toggleCSS = <<<'CSS'

/* ── Toggle tema ── */
.l69-nav__theme-toggle {
    width: 34px; height: 34px;
    background: var(--theme-pink-soft);
    border: 1px solid var(--theme-pink-border);
    border-radius: 8px;
    color: var(--theme-pink);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .88rem;
    transition: background .18s, color .18s !important;
    flex-shrink: 0;
}
.l69-nav__theme-toggle:hover {
    background: var(--theme-pink-soft);
    color: var(--theme-text);
}
@media (max-width: 767px) {
    .l69-nav__theme-toggle { display: none; }
}

CSS;

$navbarContent = str_replace('/* ── Search navbar ──', $toggleCSS . '/* ── Search navbar ──', $navbarContent);

// Agregar JS del toggle al final
if (!str_contains($navbarContent, 'themeToggleBtn')) {
    $toggleJS = <<<'JS'

@auth
<script>
(function(){
    var btn  = document.getElementById('themeToggleBtn');
    var icon = document.getElementById('themeIcon');
    var html = document.documentElement;

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('lobby69-theme', theme);
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    // Aplicar tema guardado o detectar sistema
    var saved = localStorage.getItem('lobby69-theme');
    if (saved) {
        applyTheme(saved);
    } else {
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(prefersDark ? 'dark' : 'light');
    }

    if (btn) {
        btn.addEventListener('click', function() {
            var current = html.getAttribute('data-theme') || 'light';
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }
})();
</script>
@endauth
JS;
    $navbarContent .= $toggleJS;
}

// También aplicar tema antes de que cargue la página (evita flash)
$themeInitScript = <<<'SCRIPT'
<script>
(function(){
    var t = localStorage.getItem('lobby69-theme');
    if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', t);
})();
</script>
SCRIPT;

// Añadir en app.blade.php justo después de <html lang="es">
$appPath = $base . '/resources/views/layouts/app.blade.php';
$appContent = file_get_contents($appPath);
if (!str_contains($appContent, 'lobby69-theme')) {
    $appContent = str_replace(
        '<html lang="es">',
        '<html lang="es">' . "\n" . $themeInitScript,
        $appContent
    );
    file_put_contents($appPath, $appContent);
    echo "✓ app.blade.php (script anti-flash añadido)\n";
}

file_put_contents($navbarPath, $navbarContent);
echo "✓ navbar.blade.php (toggle + variables de tema)\n";

// ============================================================
// 3. DASHBOARD — Tarjeta compacta + colores con variables tema
// ============================================================
$dashPath = $base . '/resources/views/dashboard/index.blade.php';
$dashContent = file_get_contents($dashPath);

// Reemplazar SOLO el bloque @push('styles') con versión corregida
$oldStyles = '@push(\'styles\')' . "\n" . '<style>';
$newStylesBlock = <<<'STYLES'
@push('styles')
<style>
/* ══════════════════════════════════════════════════
   DASHBOARD — Variables de tema aplicadas
   ══════════════════════════════════════════════════ */

/* ── Tarjeta principal de perfil — COMPACTA ── */
.dsb-profile-card {
    background: var(--theme-surface);
    border: 1px solid var(--theme-border);
    border-radius: 16px;
    padding: 1.1rem 1rem 1rem;
    margin-bottom: .85rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: .35rem;
    box-shadow: var(--theme-shadow);
}
.dsb-avatar-ring {
    position: relative;
    width: 68px; height: 68px;
    border-radius: 50%;
    padding: 3px;
    background: linear-gradient(135deg, var(--ring-color, #e056a0), rgba(142,68,173,.5));
    margin-bottom: .15rem;
}
.dsb-avatar-img {
    width: 100%; height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--theme-surface);
}
.dsb-avatar-verified {
    position: absolute; bottom: 1px; right: 1px;
    width: 18px; height: 18px;
    background: var(--theme-success);
    border-radius: 50%;
    border: 2px solid var(--theme-surface);
    display: flex; align-items: center; justify-content: center;
    font-size: .55rem; color: #fff;
}
.dsb-profile-nick {
    font-size: 1rem; font-weight: 800;
    color: var(--theme-text); margin: 0;
}
.dsb-profile-location {
    font-size: .75rem; color: var(--theme-text-muted);
    display: flex; align-items: center; gap: .3rem;
    margin: 0;
}
.dsb-membership-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .18rem .65rem; border-radius: 20px;
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    border: 1px solid;
}
.dsb-profile-type {
    font-size: .77rem; color: var(--theme-text-3);
    margin: 0;
}

/* ── Última vez — una línea ── */
.dsb-lastseen-row {
    display: flex; align-items: center; justify-content: center;
    gap: .35rem;
    font-size: .72rem; color: var(--theme-text-muted);
    background: var(--theme-surface-2);
    border: 1px solid var(--theme-border-soft);
    border-radius: 20px;
    padding: .2rem .7rem;
    width: 100%;
}
.dsb-lastseen-row i { font-size: .65rem; opacity: .7; }

/* ── Stats grid — compacto ── */
.dsb-stats-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: .4rem; width: 100%; margin: .3rem 0;
}
.dsb-stat-box {
    background: var(--theme-surface-2);
    border: 1px solid var(--theme-border-soft);
    border-radius: 9px;
    padding: .45rem .2rem;
    display: flex; flex-direction: column;
    align-items: center; gap: .15rem;
}
.dsb-stat-box i { font-size: .8rem; opacity: var(--theme-icon-opacity); }
.dsb-stat-num {
    font-size: .85rem; font-weight: 800;
    color: var(--theme-text); line-height: 1;
}
.dsb-stat-lbl {
    font-size: .6rem; color: var(--theme-text-muted);
    text-transform: uppercase; letter-spacing: .04em;
}

/* ── Progreso ── */
.dsb-progress-wrap { width: 100%; margin: .2rem 0; }
.dsb-progress-label {
    display: flex; justify-content: space-between;
    font-size: .72rem; color: var(--theme-text-3);
    margin-bottom: .3rem;
}
.dsb-progress-label span:last-child {
    color: var(--theme-pink); font-weight: 700;
}
.dsb-progress-bar {
    height: 5px;
    background: var(--theme-surface-3);
    border-radius: 4px; overflow: hidden;
}
.dsb-progress-fill {
    height: 100%;
    background: var(--theme-gradient);
    border-radius: 4px;
}

/* ── Acciones rápidas ── */
.dsb-quick-actions {
    display: flex; gap: .4rem; width: 100%; margin-top: .2rem;
}
.dsb-action-btn {
    flex: 1; display: inline-flex; align-items: center;
    justify-content: center; gap: .35rem;
    padding: .5rem .6rem; border-radius: 9px;
    font-size: .79rem; font-weight: 600;
    text-decoration: none; cursor: pointer;
    border: none; transition: all .18s !important;
}
.dsb-action-btn--primary {
    background: var(--theme-gradient);
    color: #fff;
}
.dsb-action-btn--primary:hover { opacity: .88; transform: translateY(-1px); }
.dsb-action-btn--ghost {
    background: var(--theme-surface-2);
    border: 1px solid var(--theme-border);
    color: var(--theme-text-2);
}
.dsb-action-btn--ghost:hover {
    background: var(--theme-pink-soft);
    border-color: var(--theme-pink-border);
    color: var(--theme-pink);
}

/* ── Sección card ── */
.dsb-section-card {
    background: var(--theme-surface);
    border: 1px solid var(--theme-border);
    border-radius: 14px;
    padding: .9rem;
    margin-bottom: .75rem;
    box-shadow: var(--theme-shadow);
}
.dsb-section-header {
    display: flex; align-items: center;
    justify-content: space-between;
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .07em;
    color: var(--theme-pink);
    margin-bottom: .7rem;
    padding-bottom: .55rem;
    border-bottom: 1px solid var(--theme-border-soft);
}
.dsb-section-badge {
    background: var(--theme-pink-soft);
    color: var(--theme-pink);
    border-radius: 10px;
    padding: .1rem .45rem;
    font-size: .7rem; font-weight: 700;
}

/* ── Fila de usuario ── */
.dsb-user-row {
    display: flex; align-items: center; gap: .55rem;
    padding: .45rem .4rem;
    border-bottom: 1px solid var(--theme-border-soft);
    text-decoration: none;
    border-radius: 8px;
    transition: background .15s !important;
}
.dsb-user-row:last-of-type { border-bottom: none; }
.dsb-user-row:hover { background: var(--theme-pink-soft); }
.dsb-user-avatar-wrap { position: relative; flex-shrink: 0; }
.dsb-user-avatar-wrap img {
    width: 34px; height: 34px;
    border-radius: 50%; object-fit: cover;
    border: 2px solid var(--theme-border);
}
.dsb-online-indicator {
    position: absolute; bottom: 1px; right: 1px;
    width: 9px; height: 9px;
    background: var(--theme-online); border-radius: 50%;
    border: 2px solid var(--theme-surface);
}
.dsb-user-info {
    flex: 1; min-width: 0;
    display: flex; flex-direction: column;
}
.dsb-user-nick {
    font-size: .82rem; font-weight: 600;
    color: var(--theme-text-2);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.dsb-user-row:hover .dsb-user-nick { color: var(--theme-pink); }
.dsb-user-time {
    font-size: .7rem; color: var(--theme-text-muted);
    display: flex; align-items: center; gap: .25rem;
}
.dsb-online-dot {
    display: inline-block;
    width: 7px; height: 7px;
    background: var(--theme-online); border-radius: 50%;
}
.dsb-ver-btn {
    display: inline-flex; align-items: center;
    padding: .22rem .6rem;
    background: var(--theme-pink-soft);
    border: 1px solid var(--theme-pink-border);
    border-radius: 20px;
    color: var(--theme-pink); font-size: .72rem; font-weight: 600;
    text-decoration: none; white-space: nowrap;
    transition: all .15s !important; flex-shrink: 0;
}
.dsb-ver-btn:hover {
    background: var(--theme-pink);
    color: #fff;
    border-color: var(--theme-pink);
}
.dsb-empty-state {
    display: flex; flex-direction: column;
    align-items: center; gap: .4rem;
    padding: 1rem;
    color: var(--theme-text-muted);
    font-size: .8rem; text-align: center;
}
.dsb-empty-state i { font-size: 1.3rem; opacity: .4; }
.dsb-see-more {
    display: block; text-align: right;
    font-size: .76rem; color: var(--theme-pink);
    text-decoration: none; margin-top: .5rem;
    font-weight: 600;
}

/* ── Feed tabs ── */
.dsb-feed-tabs {
    display: flex; gap: .5rem;
    margin-bottom: 1.1rem;
}
.dsb-feed-tab {
    display: flex; align-items: center; gap: .4rem;
    padding: .48rem 1rem; border-radius: 9px;
    color: var(--theme-text-3);
    text-decoration: none; font-size: .86rem; font-weight: 600;
    border: 1px solid var(--theme-border-soft);
    transition: all .18s !important;
    background: var(--theme-surface);
}
.dsb-feed-tab:hover { color: var(--theme-text); background: var(--theme-surface-2); }
.dsb-feed-tab.is-active {
    color: var(--theme-pink);
    background: var(--theme-pink-soft);
    border-color: var(--theme-pink-border);
}

/* ── Grid de fotos ── */
.dsb-feed-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: .85rem;
}
.dsb-feed-empty {
    grid-column: 1/-1;
    display: flex; flex-direction: column;
    align-items: center; gap: 1rem;
    padding: 3rem;
    color: var(--theme-text-muted);
    font-size: .9rem;
}
.dsb-feed-empty i { font-size: 2.5rem; opacity: .3; }

/* ── Tarjeta de foto ── */
.dsb-photo-card {
    background: var(--theme-surface);
    border: 1px solid var(--theme-border-soft);
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    box-shadow: var(--theme-shadow);
    transition: transform .2s, box-shadow .2s, border-color .2s !important;
}
.dsb-photo-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--theme-shadow-md);
    border-color: var(--theme-pink-border);
}
.dsb-photo-card__header {
    padding: .6rem .75rem;
    border-bottom: 1px solid var(--theme-border-soft);
}
.dsb-photo-card__owner {
    display: flex; align-items: center; gap: .45rem;
    text-decoration: none;
}
.dsb-photo-card__owner img {
    width: 26px; height: 26px;
    border-radius: 50%; object-fit: cover;
    border: 1px solid var(--theme-border);
    flex-shrink: 0;
}
.dsb-photo-card__owner-nick {
    font-size: .8rem; font-weight: 700;
    color: var(--theme-text-2); display: block;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.dsb-photo-card__owner:hover .dsb-photo-card__owner-nick { color: var(--theme-pink); }
.dsb-photo-card__owner-loc {
    font-size: .68rem; color: var(--theme-text-muted);
    display: flex; align-items: center; gap: .2rem;
}
.dsb-photo-card__img-wrap {
    aspect-ratio: 4/3;
    overflow: hidden;
    background: var(--theme-surface-3);
}
.dsb-photo-card__img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .3s !important;
}
.dsb-photo-card:hover .dsb-photo-card__img { transform: scale(1.04); }
.dsb-photo-card__footer { padding: .6rem .75rem; }
.dsb-photo-card__caption {
    font-size: .76rem; color: var(--theme-text-muted);
    margin: 0 0 .45rem;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.dsb-photo-card__actions {
    display: flex; align-items: center; gap: .45rem;
}

/* ── Botones de acción en fotos ── */
.dsb-like-btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .27rem .65rem;
    background: rgba(220,38,38,.08);
    border: 1px solid rgba(220,38,38,.2);
    border-radius: 20px;
    color: var(--theme-text-3); font-size: .8rem; font-weight: 600;
    cursor: pointer; transition: all .15s !important;
}
.dsb-like-btn:hover { background: rgba(220,38,38,.15); color: #dc2626; }
.dsb-like-btn.is-liked {
    background: rgba(220,38,38,.18);
    border-color: rgba(220,38,38,.4);
    color: #dc2626;
}
.dsb-like-btn.is-liked i { color: #ef4444; }
.dsb-comment-btn {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .27rem .65rem;
    background: var(--theme-surface-2);
    border: 1px solid var(--theme-border-soft);
    border-radius: 20px;
    color: var(--theme-text-3); font-size: .8rem; font-weight: 600;
    cursor: pointer; transition: all .15s !important;
}
.dsb-comment-btn:hover { background: var(--theme-surface-3); color: var(--theme-text); }
.dsb-profile-btn {
    margin-left: auto;
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px;
    background: var(--theme-pink-soft);
    border: 1px solid var(--theme-pink-border);
    border-radius: 50%;
    color: var(--theme-pink); font-size: .75rem;
    text-decoration: none; transition: all .15s !important;
}
.dsb-profile-btn:hover { background: var(--theme-pink); color: #fff; }

/* ── Cargar más ── */
.dsb-load-more-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 2rem;
    background: var(--theme-surface);
    border: 1px solid var(--theme-border);
    border-radius: 10px;
    color: var(--theme-text-2); font-size: .88rem; font-weight: 600;
    cursor: pointer; transition: all .18s !important;
    box-shadow: var(--theme-shadow);
}
.dsb-load-more-btn:hover {
    background: var(--theme-pink-soft);
    border-color: var(--theme-pink-border);
    color: var(--theme-pink);
}

/* ── Modal ── */
.dsb-modal {
    position: fixed; inset: 0; z-index: 99990;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}
.dsb-modal__overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.75);
    backdrop-filter: blur(6px);
}
.dsb-modal__box {
    position: relative; z-index: 1;
    background: var(--theme-surface);
    border: 1px solid var(--theme-border);
    border-radius: 18px;
    max-width: 900px; width: 100%;
    max-height: 90vh; overflow: hidden;
    box-shadow: var(--theme-shadow-lg);
}
.dsb-modal__close {
    position: absolute; top: .7rem; right: .7rem;
    width: 30px; height: 30px;
    background: var(--theme-surface-2);
    border: 1px solid var(--theme-border-soft);
    border-radius: 50%;
    color: var(--theme-text-3); cursor: pointer; z-index: 10;
    display: flex; align-items: center; justify-content: center;
    font-size: .82rem; transition: all .15s !important;
}
.dsb-modal__close:hover { background: rgba(220,38,38,.15); color: #dc2626; }
.dsb-modal__layout {
    display: grid; grid-template-columns: 1fr 300px;
    max-height: 90vh;
}
.dsb-modal__photo-side {
    position: relative; background: #000;
    display: flex; align-items: center; justify-content: center;
    min-height: 360px;
}
.dsb-modal__photo { max-width: 100%; max-height: 78vh; object-fit: contain; }
.dsb-modal__photo-actions {
    position: absolute; bottom: .75rem; left: .75rem;
    display: flex; gap: .45rem; align-items: center;
}
.dsb-comment-count {
    display: flex; align-items: center; gap: .3rem;
    color: rgba(255,255,255,.85); font-size: .8rem;
    background: rgba(0,0,0,.45); padding: .28rem .6rem;
    border-radius: 20px;
}
.dsb-modal__panel {
    display: flex; flex-direction: column;
    border-left: 1px solid var(--theme-border-soft);
    max-height: 90vh; overflow: hidden;
    background: var(--theme-surface);
}
.dsb-modal__owner {
    display: flex; align-items: center; justify-content: space-between;
    padding: .85rem .95rem;
    border-bottom: 1px solid var(--theme-border-soft);
    gap: .5rem;
}
.dsb-modal__owner-link {
    display: flex; align-items: center; gap: .55rem;
    text-decoration: none;
}
.dsb-modal__owner-avatar {
    width: 36px; height: 36px;
    border-radius: 50%; object-fit: cover;
    border: 2px solid var(--theme-border);
    flex-shrink: 0;
}
.dsb-modal__owner-nick { font-weight: 700; font-size: .88rem; color: var(--theme-text); }
.dsb-modal__owner-meta { font-size: .73rem; color: var(--theme-text-muted); }
.dsb-modal__caption {
    padding: .6rem .95rem;
    font-size: .82rem; color: var(--theme-text-3);
    border-bottom: 1px solid var(--theme-border-soft); margin: 0;
}
.dsb-modal__comments {
    flex: 1; overflow-y: auto; padding: .7rem .95rem;
    scrollbar-width: thin;
    scrollbar-color: var(--theme-border) transparent;
}
.dsb-comment-item {
    display: flex; gap: .5rem; margin-bottom: .7rem;
}
.dsb-comment-item img {
    width: 26px; height: 26px;
    border-radius: 50%; object-fit: cover; flex-shrink: 0;
}
.dsb-comment-nick { font-size: .77rem; font-weight: 700; color: var(--theme-text-2); }
.dsb-comment-time { font-size: .69rem; color: var(--theme-text-muted); margin-left: .3rem; }
.dsb-comment-body {
    font-size: .8rem; color: var(--theme-text-3);
    margin: .12rem 0 0; line-height: 1.45;
}
.dsb-modal__comment-form {
    padding: .7rem .95rem;
    border-top: 1px solid var(--theme-border-soft);
    background: var(--theme-surface);
}
.dsb-modal__comment-row {
    display: flex; align-items: center; gap: .45rem;
}
.dsb-modal__comment-avatar {
    width: 26px; height: 26px;
    border-radius: 50%; object-fit: cover; flex-shrink: 0;
}
.dsb-modal__comment-input {
    flex: 1; padding: .4rem .7rem;
    background: var(--theme-surface-2);
    border: 1px solid var(--theme-border);
    border-radius: 20px; color: var(--theme-text);
    font-size: .82rem; outline: none;
    transition: border-color .2s !important;
}
.dsb-modal__comment-input:focus { border-color: var(--theme-pink-border); }
.dsb-modal__comment-input::placeholder { color: var(--theme-text-muted); }
.dsb-modal__comment-send {
    width: 30px; height: 30px;
    background: var(--theme-gradient);
    border: none; border-radius: 50%; color: #fff;
    cursor: pointer; font-size: .78rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: opacity .15s !important;
}
.dsb-modal__comment-send:hover { opacity: .85; }
.dsb-modal__comment-note {
    font-size: .7rem; color: var(--theme-text-muted);
    margin: .35rem 0 0; display: flex; gap: .3rem; align-items: center;
}
@media (max-width: 640px) {
    .dsb-modal__layout { grid-template-columns: 1fr; }
    .dsb-modal__panel { max-height: 45vh; border-left: none; border-top: 1px solid var(--theme-border-soft); }
    .dsb-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush
STYLES;

// Reemplazar el bloque @push('styles') completo
$dashContent = preg_replace(
    '/@push\(\'styles\'\)\s*<style>.*?<\/style>\s*@endpush/s',
    $newStylesBlock,
    $dashContent
);

// Reemplazar la tarjeta de last seen para que sea una línea
$dashContent = str_replace(
    '<div class="dsb-lastseen-row">
            <i class="fas fa-clock"></i> {{ $lastSeen }}
        </div>',
    '<div class="dsb-lastseen-row">
        <i class="fas fa-clock"></i>
        <span>Última vez: {{ $lastSeen }}</span>
    </div>',
    $dashContent
);

// Actualizar la sección de stats para quitar el campo de última vez del grid
// y ponerlo como línea separada
$oldStats = <<<'STATS'
    <div class="dsb-stats-grid">
        <div class="dsb-stat-box">
            <i class="fas fa-eye" style="color:#e056a0;"></i>
            <span class="dsb-stat-num">{{ $statsViews }}</span>
            <span class="dsb-stat-lbl">Vistas</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-heart" style="color:#ef4444;"></i>
            <span class="dsb-stat-num">{{ $statsLikes }}</span>
            <span class="dsb-stat-lbl">Likes</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-images" style="color:#60a5fa;"></i>
            <span class="dsb-stat-num">{{ $statsPhotos }}</span>
            <span class="dsb-stat-lbl">Fotos</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-clock" style="color:#fbbf24;"></i>
            <span class="dsb-stat-num" style="font-size:.68rem;line-height:1.2;">
                {{ \Carbon\Carbon::parse($sbUser->last_seen_at ?? now())->format('d/m H:i') }}
            </span>
            <span class="dsb-stat-lbl">Última vez</span>
        </div>
    </div>
STATS;

$newStats = <<<'STATS'
    <div class="dsb-stats-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="dsb-stat-box">
            <i class="fas fa-eye" style="color:var(--theme-pink);"></i>
            <span class="dsb-stat-num">{{ $statsViews }}</span>
            <span class="dsb-stat-lbl">Vistas</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-heart" style="color:#dc2626;"></i>
            <span class="dsb-stat-num">{{ $statsLikes }}</span>
            <span class="dsb-stat-lbl">Likes</span>
        </div>
        <div class="dsb-stat-box">
            <i class="fas fa-images" style="color:var(--theme-accent-2);"></i>
            <span class="dsb-stat-num">{{ $statsPhotos }}</span>
            <span class="dsb-stat-lbl">Fotos</span>
        </div>
    </div>
    {{-- Última vez — una sola línea --}}
    <div class="dsb-lastseen-row">
        <i class="fas fa-clock"></i>
        <span>Última vez: <strong>{{ $lastSeen }}</strong></span>
    </div>
STATS;

$dashContent = str_replace($oldStats, $newStats, $dashContent);

file_put_contents($dashPath, $dashContent);
echo "✓ dashboard/index.blade.php (tema + tarjeta compacta)\n";

// ============================================================
// 4. APP.BLADE.PHP — Variables de tema en el layout global
// ============================================================
$appContent = file_get_contents($appPath);

// Reemplazar los colores hardcodeados del layout por variables
$appContent = str_replace('background: rgba(255,255,255,0.04);', 'background: var(--theme-surface-2);', $appContent);
$appContent = str_replace('background: rgba(255,255,255,.04);', 'background: var(--theme-surface-2);', $appContent);
$appContent = str_replace('background: rgba(255,255,255,.05);', 'background: var(--theme-surface);', $appContent);
$appContent = str_replace('border: 1px solid rgba(180,60,120,.15);', 'border: 1px solid var(--theme-border);', $appContent);
$appContent = str_replace('border: 1px solid rgba(180,60,120,.18);', 'border: 1px solid var(--theme-border);', $appContent);
$appContent = str_replace('color: rgba(226,217,243,.75);', 'color: var(--theme-text-2);', $appContent);
$appContent = str_replace('color: rgba(226,217,243,.85);', 'color: var(--theme-text-2);', $appContent);
$appContent = str_replace('color: #fff;', 'color: var(--theme-text);', $appContent);
$appContent = str_replace("background: rgba(10, 6, 20, 0.97);", 'background: var(--theme-navbar-bg);', $appContent);

// Actualizar sidebar cards para usar variables de tema
$appContent = str_replace(
    '.l69-sidebar-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(180,60,120,0.15);',
    '.l69-sidebar-card {
        background: var(--theme-surface);
        border: 1px solid var(--theme-border);',
    $appContent
);

file_put_contents($appPath, $appContent);
echo "✓ app.blade.php (colores con variables de tema)\n";

echo "\n════════════════════════════════════════════\n";
echo "  Ejecuta:\n";
echo "  C:\\php\\php.exe artisan view:clear\n";
echo "  C:\\php\\php.exe artisan serve\n";
echo "\n";
echo "  El toggle ☀️/🌙 aparece en el navbar.\n";
echo "  El tema se guarda en localStorage.\n";
echo "════════════════════════════════════════════\n";
