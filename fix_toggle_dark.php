<?php
$path = __DIR__ . '/resources/views/layouts/app.blade.php';
$content = file_get_contents($path);

// Reemplazar el script anti-flash por version completa con logica de icono
$old = '<script>
(function(){
    var t = localStorage.getItem(\'lobby69-theme\');
    if (!t) t = window.matchMedia(\'(prefers-color-scheme: dark)\').matches ? \'dark\' : \'light\';
    document.documentElement.setAttribute(\'data-theme\', t);
})();
</script>';

$new = '<script>
(function(){
    var t = localStorage.getItem("lobby69-theme");
    if (!t) t = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    document.documentElement.setAttribute("data-theme", t);
})();
</script>';

$content = str_replace($old, $new, $content);

// Asegurar que el CSS del navbar use las variables correctas para modo noche
$themeNavCSS = '
/* ── Navbar modo noche ── */
[data-theme="dark"] .l69-nav {
    background: var(--theme-navbar-bg) !important;
    border-bottom-color: var(--theme-navbar-border) !important;
}
[data-theme="dark"] .l69-nav__link,
[data-theme="dark"] .l69-nav__brand-name,
[data-theme="dark"] .l69-nav__user-btn,
[data-theme="dark"] .l69-nav__user-nick {
    color: var(--theme-navbar-text) !important;
}
[data-theme="dark"] body {
    background-color: var(--theme-bg) !important;
    color: var(--theme-text) !important;
}
[data-theme="dark"] .dsb-profile-card,
[data-theme="dark"] .dsb-section-card,
[data-theme="dark"] .dsb-photo-card,
[data-theme="dark"] .dsb-modal__box,
[data-theme="dark"] .dsb-modal__panel {
    background: var(--theme-surface) !important;
    border-color: var(--theme-border) !important;
}
[data-theme="dark"] .dsb-stat-box,
[data-theme="dark"] .dsb-lastseen-row,
[data-theme="dark"] .dsb-feed-tab,
[data-theme="dark"] .dsb-load-more-btn {
    background: var(--theme-surface-2) !important;
    border-color: var(--theme-border-soft) !important;
    color: var(--theme-text-2) !important;
}
[data-theme="dark"] .dsb-profile-nick,
[data-theme="dark"] .dsb-stat-num,
[data-theme="dark"] .dsb-user-nick,
[data-theme="dark"] .dsb-modal__owner-nick {
    color: var(--theme-text) !important;
}
[data-theme="dark"] .dsb-profile-location,
[data-theme="dark"] .dsb-user-time,
[data-theme="dark"] .dsb-stat-lbl,
[data-theme="dark"] .dsb-lastseen-row,
[data-theme="dark"] .dsb-empty-state {
    color: var(--theme-text-muted) !important;
}
[data-theme="dark"] .l69-sb-card,
[data-theme="dark"] .l69-sidebar-card {
    background: var(--theme-surface) !important;
    border-color: var(--theme-border) !important;
}
[data-theme="dark"] .l69-layout {
    background: var(--theme-bg) !important;
}
';

// Insertar CSS antes del cierre de </style> principal
$content = str_replace(
    "    </style>\n<style>[x-cloak]",
    $themeNavCSS . "    </style>\n<style>[x-cloak]",
    $content
);

file_put_contents($path, $content);
echo "OK app.blade.php actualizado con modo noche\n";

// Fix del JS del toggle en navbar — asegurar que el icono cambie correctamente
$navPath = __DIR__ . "/resources/views/components/navbar.blade.php";
$navContent = file_get_contents($navPath);

// Reemplazar la funcion applyTheme para que actualice el icono correctamente
$oldApply = "    function applyTheme(theme) {
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
    }";

$newApply = "    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('lobby69-theme', theme);
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            icon.style.color = theme === 'dark' ? '#fbbf24' : '#8e44ad';
        }
        if (btn) {
            btn.title = theme === 'dark' ? 'Modo día' : 'Modo noche';
        }
    }

    // Aplicar tema guardado o detectar sistema
    var saved = localStorage.getItem('lobby69-theme');
    var initial = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    applyTheme(initial);";

$navContent = str_replace($oldApply, $newApply, $navContent);
file_put_contents($navPath, $navContent);
echo "OK navbar.blade.php toggle corregido\n";
