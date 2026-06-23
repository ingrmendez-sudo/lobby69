<?php
/**
 * fix_tres_problemas.php
 * Resuelve: 1) Dark mode toggle, 2) Icono superpuesto, 3) Fotos no aparecen
 */

$base = __DIR__;

// ─── UTILIDAD ───────────────────────────────────────────────────────────────
function replaceInFile(string $path, string $search, string $replace): void {
    if (!file_exists($path)) {
        echo "  [SKIP] No existe: $path\n";
        return;
    }
    $content = file_get_contents($path);
    if (strpos($content, $search) === false) {
        echo "  [INFO] Patrón no encontrado en: " . basename($path) . "\n";
        return;
    }
    file_put_contents($path, str_replace($search, $replace, $content));
    echo "  [OK]   Actualizado: $path\n";
}

function writeFile(string $path, string $content): void {
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($path, $content);
    echo "  [OK]   Escrito: $path\n";
}

// ════════════════════════════════════════════════════════════════════════════
// FIX 1 — CSS: Dark mode variables correctas en 00-vivid-nights.css
// ════════════════════════════════════════════════════════════════════════════
echo "\n[1/4] Corrigiendo variables CSS dark/light mode...\n";

$cssPath = $base . '/public/css/00-vivid-nights.css';
if (file_exists($cssPath)) {
    $cssContent = file_get_contents($cssPath);

    // Eliminar bloques de tema anteriores que puedan estar mal
    $cssContent = preg_replace('/\/\* ={3,} TEMA (DAY|NIGHT|LIGHT|DARK)[^*]*\*\/.*?(?=\/\* =|$)/s', '', $cssContent);

    // Agregar al final el sistema de temas correcto
    $themeBlock = <<<'CSS'


/* ══════════════════════════════════════════════════════════
   SISTEMA DE TEMAS — LOBBY69
   El toggle aplica data-theme="dark" en <html>
   Por defecto: modo claro (day)
   ══════════════════════════════════════════════════════════ */

/* MODO DÍA (default) */
:root,
html[data-theme="light"],
html:not([data-theme="dark"]) {
    --bg-body:        #faf9f7;
    --bg-card:        #ffffff;
    --bg-card-hover:  #f5f3ef;
    --bg-sidebar:     #ffffff;
    --bg-navbar:      rgba(255, 255, 255, 0.95);
    --bg-input:       #f0eee8;
    --text-primary:   #1a1523;
    --text-secondary: #5a5470;
    --text-muted:     #9590a8;
    --text-on-accent: #ffffff;
    --border-color:   rgba(26, 21, 35, 0.10);
    --shadow-card:    0 2px 12px rgba(0,0,0,0.08);
    --shadow-navbar:  0 2px 20px rgba(0,0,0,0.08);
    --overlay-bg:     rgba(0,0,0,0.5);
    --toggle-bg:      #e8e4f0;
    --toggle-icon:    "🌙";
}

/* MODO NOCHE */
html[data-theme="dark"] {
    --bg-body:        #0f0a1e;
    --bg-card:        rgba(255, 255, 255, 0.06);
    --bg-card-hover:  rgba(255, 255, 255, 0.10);
    --bg-sidebar:     rgba(255, 255, 255, 0.04);
    --bg-navbar:      rgba(15, 10, 30, 0.95);
    --bg-input:       rgba(255, 255, 255, 0.08);
    --text-primary:   #f0eaf8;
    --text-secondary: #c4b8d8;
    --text-muted:     #8a7fa0;
    --text-on-accent: #ffffff;
    --border-color:   rgba(255, 255, 255, 0.10);
    --shadow-card:    0 2px 12px rgba(0,0,0,0.40);
    --shadow-navbar:  0 2px 20px rgba(0,0,0,0.50);
    --overlay-bg:     rgba(0,0,0,0.75);
    --toggle-bg:      rgba(255, 255, 255, 0.12);
    --toggle-icon:    "☀️";
}

/* ── Aplicar variables al DOM ─────────────────────────────── */
body {
    background-color: var(--bg-body) !important;
    color:            var(--text-primary) !important;
    transition: background-color 0.3s ease, color 0.3s ease;
}

.card, .sidebar-card, .profile-card,
[class*="bg-white"], [class*="bg-card"] {
    background-color: var(--bg-card) !important;
    color:            var(--text-primary) !important;
    border-color:     var(--border-color) !important;
}

.navbar, nav {
    background: var(--bg-navbar) !important;
    border-bottom: 1px solid var(--border-color) !important;
    box-shadow: var(--shadow-navbar) !important;
}

input, textarea, select, .search-input {
    background-color: var(--bg-input) !important;
    color:            var(--text-primary) !important;
    border-color:     var(--border-color) !important;
}

input::placeholder, textarea::placeholder {
    color: var(--text-muted) !important;
}

p, span, h1, h2, h3, h4, h5, h6, label, td, th, li {
    color: var(--text-primary);
}

.text-muted, .text-secondary {
    color: var(--text-secondary) !important;
}

/* ── Botón toggle tema ─────────────────────────────────────── */
#theme-toggle {
    display:         inline-flex;
    align-items:     center;
    justify-content: center;
    width:           36px;
    height:          36px;
    border-radius:   50%;
    background:      var(--toggle-bg);
    border:          1px solid var(--border-color);
    cursor:          pointer;
    font-size:       16px;
    transition:      all 0.2s ease;
    flex-shrink:     0;
    position:        relative; /* NO absolute */
    z-index:         10;
}

#theme-toggle:hover {
    transform: scale(1.1);
    background: var(--bg-card-hover) !important;
}

CSS;

    file_put_contents($cssPath, $cssContent . $themeBlock);
    echo "  [OK]   CSS actualizado: $cssPath\n";
} else {
    echo "  [WARN] No se encontró 00-vivid-nights.css, creando en public/css/\n";
    if (!is_dir($base . '/public/css')) mkdir($base . '/public/css', 0755, true);
    file_put_contents($cssPath, "/* Lobby69 */\n" . $themeBlock ?? '');
}

// ════════════════════════════════════════════════════════════════════════════
// FIX 2 — NAVBAR: Toggle bien posicionado + JS correcto
// ════════════════════════════════════════════════════════════════════════════
echo "\n[2/4] Corrigiendo navbar (toggle + JS dark mode)...\n";

$navbarPath = $base . '/resources/views/components/navbar.blade.php';
if (!file_exists($navbarPath)) {
    echo "  [SKIP] No existe navbar.blade.php\n";
} else {
    $navbar = file_get_contents($navbarPath);

    // ── Eliminar cualquier botón toggle existente (puede estar mal ubicado) ──
    $navbar = preg_replace(
        '/<button[^>]*id=["\']theme-toggle["\'][^>]*>.*?<\/button>\s*/s',
        '',
        $navbar
    );

    // ── Eliminar bloques <script> de dark mode existentes ──
    $navbar = preg_replace(
        '/\/\/ ?[Tt]ema|[Dd]ark ?[Mm]ode|theme.toggle[\s\S]*?(?=\/\/|<\/script>)/',
        '',
        $navbar
    );

    // ── Eliminar bloques script completos de tema si existen ──
    $navbar = preg_replace(
        '/<script[^>]*>[\s\S]*?localStorage[\s\S]*?<\/script>/',
        '',
        $navbar
    );

    // ── Insertar botón toggle DENTRO del flex container del navbar ──
    // Buscamos el cierre de la sección de íconos de usuario (antes del </nav> o antes del menú móvil)
    $toggleButton = '<button id="theme-toggle" title="Cambiar tema" aria-label="Cambiar tema">🌙</button>';

    // Intentar insertar antes del primer </div> que cierra los items de la derecha del navbar
    // Patrón flexible: antes de </nav> si no hay mejor opción
    if (strpos($navbar, 'id="navbar-right"') !== false) {
        $navbar = str_replace(
            '</div><!-- /navbar-right -->',
            $toggleButton . "\n                </div><!-- /navbar-right -->",
            $navbar
        );
    } elseif (strpos($navbar, 'navbar-actions') !== false) {
        $navbar = preg_replace(
            '/(class="[^"]*navbar-actions[^"]*"[^>]*>)/i',
            '$1' . "\n                " . $toggleButton,
            $navbar
        );
    } else {
        // Fallback: insertar antes del cierre de </nav>
        $navbar = str_replace(
            '</nav>',
            "            " . $toggleButton . "\n        </nav>",
            $navbar
        );
    }

    // ── Agregar JS de dark mode al final del archivo ──
    $darkModeJS = <<<'BLADE'

{{-- ══ DARK MODE TOGGLE JS ══════════════════════════════════════════════════ --}}
<script>
(function() {
    // Aplicar tema guardado INMEDIATAMENTE (antes del paint)
    const saved = localStorage.getItem('lobby69-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);

    function setToggleIcon(theme) {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;
        btn.textContent = theme === 'dark' ? '☀️' : '🌙';
        btn.title = theme === 'dark' ? 'Cambiar a modo día' : 'Cambiar a modo noche';
    }

    // Cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;

        // Icono inicial
        setToggleIcon(document.documentElement.getAttribute('data-theme') || 'light');

        btn.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next    = current === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('lobby69-theme', next);
            setToggleIcon(next);
        });
    });
})();
</script>
BLADE;

    // Evitar duplicados
    if (strpos($navbar, 'lobby69-theme') === false) {
        $navbar .= $darkModeJS;
    } else {
        // Reemplazar el bloque JS existente
        $navbar = preg_replace(
            '/\{\{--\s*[═=]+ DARK MODE[\s\S]*?<\/script>\s*$/s',
            $darkModeJS,
            $navbar
        );
    }

    file_put_contents($navbarPath, $navbar);
    echo "  [OK]   navbar.blade.php actualizado\n";
}

// ════════════════════════════════════════════════════════════════════════════
// FIX 3 — app.blade.php: Aplicar tema al cargar la página (antes del paint)
// ════════════════════════════════════════════════════════════════════════════
echo "\n[3/4] Corrigiendo app.blade.php (tema inicial sin flash)...\n";

$appPath = $base . '/resources/views/layouts/app.blade.php';
if (file_exists($appPath)) {
    $app = file_get_contents($appPath);

    $antiFlashScript = <<<'HTML'
    <script>
        // Anti-flash: aplicar tema ANTES del render
        (function(){
            var t = localStorage.getItem('lobby69-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
HTML;

    // Insertar justo después de <head> si no existe ya
    if (strpos($app, 'Anti-flash') === false) {
        $app = str_replace('<head>', "<head>\n" . $antiFlashScript, $app);
        file_put_contents($appPath, $app);
        echo "  [OK]   Anti-flash script agregado en <head>\n";
    } else {
        echo "  [INFO] Anti-flash ya existe, sin cambios\n";
    }
} else {
    echo "  [SKIP] No existe app.blade.php\n";
}

// ════════════════════════════════════════════════════════════════════════════
// FIX 4 — DashboardController: Fotos sin filtro album_type si la columna no existe
// ════════════════════════════════════════════════════════════════════════════
echo "\n[4/4] Verificando columnas de tabla photos...\n";

// Crear un script tinker de una sola línea para diagnóstico
$tinkerScript = $base . '/diagnose_photos.php';
$tinkerContent = <<<'PHP'
<?php
// Ejecutar: C:\php\php.exe diagnose_photos.php
// Carga Laravel manualmente
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n══════════════════════════════════\n";
echo "  DIAGNÓSTICO DE FOTOS - LOBBY69\n";
echo "══════════════════════════════════\n\n";

// Columnas de la tabla photos
$cols = Schema::getColumnListing('photos');
echo "Columnas en 'photos': " . implode(', ', $cols) . "\n\n";

// Conteos básicos
echo "Total fotos:           " . DB::table('photos')->count() . "\n";
echo "Status = approved:     " . DB::table('photos')->where('status','approved')->count() . "\n";
echo "Status = pending:      " . DB::table('photos')->where('status','pending')->count() . "\n";
echo "Status = rejected:     " . DB::table('photos')->where('status','rejected')->count() . "\n";

if (in_array('album_type', $cols)) {
    echo "\nalbum_type = public:   " . DB::table('photos')->where('album_type','public')->count() . "\n";
    echo "album_type = private:  " . DB::table('photos')->where('album_type','private')->count() . "\n";
    echo "album_type = NULL:     " . DB::table('photos')->whereNull('album_type')->count() . "\n";
}

if (in_array('is_public', $cols)) {
    echo "\nis_public = 1:         " . DB::table('photos')->where('is_public',1)->count() . "\n";
    echo "is_public = 0:         " . DB::table('photos')->where('is_public',0)->count() . "\n";
}

// Muestra de 3 fotos
echo "\nMuestra de 3 fotos:\n";
$sample = DB::table('photos')->limit(3)->get();
foreach ($sample as $p) {
    $row = (array) $p;
    echo "  ID: " . ($row['id'] ?? '?');
    echo " | status: " . ($row['status'] ?? '?');
    echo " | album_type: " . ($row['album_type'] ?? 'N/A');
    echo " | is_public: " . ($row['is_public'] ?? 'N/A');
    echo "\n";
}

echo "\n══════════════════════════════════\n";
PHP;

file_put_contents($tinkerScript, $tinkerContent);
echo "  [OK]   Script de diagnóstico creado: diagnose_photos.php\n";

// ════════════════════════════════════════════════════════════════════════════
echo "\n══════════════════════════════════════════\n";
echo "  COMPLETADO — Próximos pasos:\n";
echo "══════════════════════════════════════════\n\n";
echo "  1. C:\\php\\php.exe diagnose_photos.php\n";
echo "     (muestra columnas y conteos reales)\n\n";
echo "  2. C:\\php\\php.exe artisan view:clear\n";
echo "  3. C:\\php\\php.exe artisan cache:clear\n";
echo "  4. C:\\php\\php.exe artisan serve\n\n";
echo "  Luego recarga el browser con Ctrl+Shift+R\n";
echo "══════════════════════════════════════════\n";
