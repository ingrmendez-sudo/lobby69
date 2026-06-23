<?php
$path = __DIR__ . '/resources/views/components/navbar.blade.php';
$content = file_get_contents($path);

// Eliminar el bloque dark mode suelto (fuera de @auth) que empieza con el comentario
$content = preg_replace(
    '/\{\{--\s*══ DARK MODE TOGGLE JS[\s\S]*?<\/script>\s*$/s',
    '',
    $content
);

// El dark mode JS NO necesita @auth — funciona para todos (guests también ven el toggle)
// Agregar al final limpio, sin @auth wrapper
$darkJS = <<<'BLADE'

{{-- ══ DARK MODE TOGGLE JS (global, no requiere auth) ══ --}}
<script>
(function() {
    // Anti-flash: aplicar tema guardado antes del primer paint
    var saved = localStorage.getItem('lobby69-theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);

    function setIcon(theme) {
        var btn = document.getElementById('theme-toggle');
        if (!btn) return;
        btn.textContent = theme === 'dark' ? '☀️' : '🌙';
        btn.title = theme === 'dark' ? 'Modo día' : 'Modo noche';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Icono inicial
        setIcon(document.documentElement.getAttribute('data-theme') || 'light');

        var btn = document.getElementById('theme-toggle');
        if (!btn) return;

        btn.addEventListener('click', function() {
            var cur  = document.documentElement.getAttribute('data-theme') || 'light';
            var next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('lobby69-theme', next);
            setIcon(next);
        });
    });
})();
</script>
BLADE;

$content .= $darkJS;

file_put_contents($path, $content);
echo "[OK] navbar.blade.php corregido\n";
