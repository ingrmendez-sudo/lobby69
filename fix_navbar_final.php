<?php
$file = __DIR__ . "/resources/views/components/navbar.blade.php";
$content = file_get_contents($file);

// Encontrar la posicion del SEGUNDO @auth
$first = strpos($content, "@auth");
$second = strpos($content, "@auth", $first + 1);

if ($second !== false) {
    // Cortar todo desde el segundo @auth en adelante
    $content = substr($content, 0, $second);
    echo "Segundo @auth encontrado en posicion: $second\n";
} else {
    echo "No se encontro segundo @auth\n";
}

// Agregar JS de dark mode al final (sin @auth wrapper)
$content .= PHP_EOL . PHP_EOL;
$content .= "<script>" . PHP_EOL;
$content .= "(function() {" . PHP_EOL;
$content .= "    var saved = localStorage.getItem(\"lobby69-theme\") || \"light\";" . PHP_EOL;
$content .= "    document.documentElement.setAttribute(\"data-theme\", saved);" . PHP_EOL;
$content .= "    function setIcon(t) {" . PHP_EOL;
$content .= "        var b = document.getElementById(\"theme-toggle\");" . PHP_EOL;
$content .= "        if (!b) return;" . PHP_EOL;
$content .= "        b.textContent = t === \"dark\" ? \"\u2600\ufe0f\" : \"\ud83c\udf19\";" . PHP_EOL;
$content .= "    }" . PHP_EOL;
$content .= "    document.addEventListener(\"DOMContentLoaded\", function() {" . PHP_EOL;
$content .= "        setIcon(document.documentElement.getAttribute(\"data-theme\") || \"light\");" . PHP_EOL;
$content .= "        var b = document.getElementById(\"theme-toggle\");" . PHP_EOL;
$content .= "        if (!b) return;" . PHP_EOL;
$content .= "        b.addEventListener(\"click\", function() {" . PHP_EOL;
$content .= "            var cur = document.documentElement.getAttribute(\"data-theme\") || \"light\";" . PHP_EOL;
$content .= "            var next = cur === \"dark\" ? \"light\" : \"dark\";" . PHP_EOL;
$content .= "            document.documentElement.setAttribute(\"data-theme\", next);" . PHP_EOL;
$content .= "            localStorage.setItem(\"lobby69-theme\", next);" . PHP_EOL;
$content .= "            setIcon(next);" . PHP_EOL;
$content .= "        });" . PHP_EOL;
$content .= "    });" . PHP_EOL;
$content .= "})();" . PHP_EOL;
$content .= "</script>" . PHP_EOL;

file_put_contents($file, $content);
echo "OK - navbar corregido\n";

// Verificar resultado
$lines = explode(PHP_EOL, $content);
$total = count($lines);
echo "Total lineas: $total\n";

// Contar @auth y @endauth
$authCount    = substr_count($content, "@auth");
$endauthCount = substr_count($content, "@endauth");
echo "@auth: $authCount | @endauth: $endauthCount\n";

if ($authCount === $endauthCount) {
    echo "BALANCEADO - OK\n";
} else {
    echo "DESBALANCEADO - revisar manualmente\n";
}

