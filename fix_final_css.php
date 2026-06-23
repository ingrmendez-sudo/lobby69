<?php
$css = file_get_contents(__DIR__ . "/public/css/00-vivid-nights.css");

// Eliminar TODOS los bloques de tema agregados anteriormente
$css = preg_replace("/\/\* ══+\s*SISTEMA DE TEMAS[\s\S]*$/", "", $css);
$css = preg_replace("/\/\* ══+\s*TEMA[\s\S]*$/", "", $css);
$css = preg_replace("/\/\* Applying[\s\S]*$/", "", $css);
$css = rtrim($css);

$theme = "\n\n" . file_get_contents(__DIR__ . "/tema_lobby69.css");
file_put_contents(__DIR__ . "/public/css/00-vivid-nights.css", $css . $theme);
echo "[OK] CSS actualizado\n";
echo "Lineas: " . count(file(__DIR__ . "/public/css/00-vivid-nights.css")) . "\n";

