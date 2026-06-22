<?php
// FIX 1: Crear el SVG
$svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">
  <circle cx="50" cy="50" r="50" fill="#1a1a2e"/>
  <circle cx="50" cy="38" r="18" fill="#8b5cf6"/>
  <ellipse cx="50" cy="82" rx="28" ry="20" fill="#8b5cf6"/>
</svg>';

$dir  = __DIR__ . '/public/img';
$path = $dir . '/default-avatar.svg';
if (!is_dir($dir)) { mkdir($dir, 0755, true); }
file_put_contents($path, $svg);
echo "[OK] SVG creado: " . filesize($path) . " bytes" . PHP_EOL;

// FIX 2: Corregir onerror en la vista
$viewPath = __DIR__ . '/resources/views/dashboard/index.blade.php';
$content  = file_get_contents($viewPath);

$old = "onerror=\"this.src='{{ asset('img/default-avatar.svg') }}'\"";
$new = "onerror=\"this.onerror=null; this.src='{{ asset('img/default-avatar.svg') }}'\"";

if (strpos($content, $old) !== false) {
    file_put_contents($viewPath, str_replace($old, $new, $content));
    echo "[OK] onerror corregido - loop infinito eliminado." . PHP_EOL;
} else {
    // Buscar variante sin comillas dobles internas
    $old2 = 'onerror="this.src=\'{{ asset(\'img/default-avatar.svg\') }}\'"';
    if (strpos($content, $old2) !== false) {
        $new2 = 'onerror="this.onerror=null; this.src=\'{{ asset(\'img/default-avatar.svg\') }}\'"';
        file_put_contents($viewPath, str_replace($old2, $new2, $content));
        echo "[OK] onerror corregido (variante 2)." . PHP_EOL;
    } else {
        echo "[INFO] Mostrando todas las lineas con onerror:" . PHP_EOL;
        foreach (explode("\n", $content) as $i => $line) {
            if (stripos($line, 'onerror') !== false) {
                echo "Linea " . ($i+1) . ": " . trim($line) . PHP_EOL;
            }
        }
    }
}
?>
