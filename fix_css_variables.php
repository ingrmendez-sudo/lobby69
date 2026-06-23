<?php
$css = file_get_contents(__DIR__ . "/public/css/00-vivid-nights.css");

// Eliminar bloques de tema que agregamos antes (pueden estar duplicados o rotos)
$css = preg_replace(
    "/\/\* ══+\s*SISTEMA DE TEMAS[\s\S]*?(?=\/\* ={3}|$)/",
    "",
    $css
);
$css = preg_replace(
    "/\/\* ══+\s*TEMA[\s\S]*?(?=\/\* ={3}|\/\* ══|$)/",
    "",
    $css
);

// Agregar sistema de temas completo y unificado al final
$theme = <<<ENDCSS

/* ══════════════════════════════════════════════════════
   SISTEMA DE TEMAS LOBBY69
   Soporta: --theme-*, --bg-*, --text-* (todos los alias)
   ══════════════════════════════════════════════════════ */

/* MODO DÍA (default) */
:root,
html:not([data-theme="dark"]) {
    /* Fondos */
    --bg-body:              #f5f0fa;
    --bg-card:              #ffffff;
    --bg-card-hover:        #faf7ff;
    --bg-sidebar:           #ffffff;
    --bg-input:             #f0eaf8;
    --bg-navbar:            rgba(255,255,255,0.97);

    /* Textos */
    --text-primary:         #1a1523;
    --text-secondary:       #5a5470;
    --text-muted:           #9590a8;
    --text-on-accent:       #ffffff;

    /* Bordes y sombras */
    --border-color:         rgba(26,21,35,0.10);
    --shadow-card:          0 2px 12px rgba(0,0,0,0.08);
    --shadow-navbar:        0 2px 20px rgba(0,0,0,0.08);

    /* Toggle */
    --toggle-bg:            #ede8f5;

    /* Aliases para compatibilidad con navbar/sidebar */
    --theme-navbar-bg:      rgba(255,255,255,0.97);
    --theme-navbar-border:  rgba(26,21,35,0.10);
    --theme-navbar-text:    #1a1523;
    --theme-text:           #1a1523;
    --theme-text-muted:     #9590a8;
    --theme-accent:         #e056a0;
    --theme-accent-2:       #9B59B6;
    --theme-pink:           #e056a0;
    --theme-pink-soft:      rgba(224,86,160,0.08);
    --theme-dropdown-bg:    #ffffff;
    --theme-surface-2:      #f5f0fa;
    --theme-shadow-md:      0 4px 20px rgba(0,0,0,0.10);
    --theme-border:         rgba(26,21,35,0.10);
}

/* MODO NOCHE */
html[data-theme="dark"] {
    /* Fondos */
    --bg-body:              #0f0a1e;
    --bg-card:              rgba(255,255,255,0.06);
    --bg-card-hover:        rgba(255,255,255,0.10);
    --bg-sidebar:           rgba(255,255,255,0.04);
    --bg-input:             rgba(255,255,255,0.08);
    --bg-navbar:            rgba(15,10,30,0.97);

    /* Textos */
    --text-primary:         #f0eaf8;
    --text-secondary:       #c4b8d8;
    --text-muted:           #8a7fa0;
    --text-on-accent:       #ffffff;

    /* Bordes y sombras */
    --border-color:         rgba(255,255,255,0.10);
    --shadow-card:          0 2px 12px rgba(0,0,0,0.40);
    --shadow-navbar:        0 2px 20px rgba(0,0,0,0.50);

    /* Toggle */
    --toggle-bg:            rgba(255,255,255,0.12);

    /* Aliases para compatibilidad */
    --theme-navbar-bg:      rgba(15,10,30,0.97);
    --theme-navbar-border:  rgba(255,255,255,0.10);
    --theme-navbar-text:    #f0eaf8;
    --theme-text:           #f0eaf8;
    --theme-text-muted:     #8a7fa0;
    --theme-accent:         #e056a0;
    --theme-accent-2:       #a855f7;
    --theme-pink:           #e056a0;
    --theme-pink-soft:      rgba(224,86,160,0.12);
    --theme-dropdown-bg:    #1e1530;
    --theme-surface-2:      rgba(255,255,255,0.06);
    --theme-shadow-md:      0 4px 20px rgba(0,0,0,0.40);
    --theme-border:         rgba(255,255,255,0.10);
}

/* ── Aplicar fondos y textos base ── */
html, body {
    background-color: var(--bg-body) !important;
    color:            var(--text-primary) !important;
    transition: background-color 0.25s ease, color 0.25s ease;
}

/* ── Cards y superficies ── */
.card, .sidebar-card, .profile-card, .l69-card,
.feed-card, .stat-card, .activity-card, .sidebar-section {
    background: var(--bg-card) !important;
    border-color: var(--border-color) !important;
    box-shadow: var(--shadow-card) !important;
    color: var(--text-primary) !important;
}

/* ── Navbar ── */
.l69-nav, nav.l69-nav {
    background: var(--bg-navbar) !important;
    border-color: var(--border-color) !important;
    box-shadow: var(--shadow-navbar) !important;
}

/* ── Inputs ── */
input:not([type=submit]):not([type=button]):not([type=checkbox]):not([type=radio]),
textarea, select, .l69-search-input {
    background: var(--bg-input) !important;
    color:       var(--text-primary) !important;
    border-color: var(--border-color) !important;
}
input::placeholder, textarea::placeholder {
    color: var(--text-muted) !important;
}

/* ── Textos secundarios ── */
.text-muted, .l69-muted, small {
    color: var(--text-muted) !important;
}

/* ── Toggle button ── */
#theme-toggle {
    display:         inline-flex;
    align-items:     center;
    justify-content: center;
    width:           36px;
    height:          36px;
    min-width:       36px;
    border-radius:   50%;
    background:      var(--toggle-bg) !important;
    border:          1px solid var(--border-color);
    cursor:          pointer;
    font-size:       18px;
    line-height:     1;
    transition:      transform 0.2s ease;
    flex-shrink:     0;
    position:        static !important;
}
#theme-toggle:hover { transform: scale(1.12); }

ENDCSS;

// Escribir
file_put_contents(__DIR__ . "/public/css/00-vivid-nights.css", $css . $theme);
echo "[OK] CSS actualizado con sistema de temas unificado\n";
echo "Lineas totales: " . count(file(__DIR__ . "/public/css/00-vivid-nights.css")) . "\n";

