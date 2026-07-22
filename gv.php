<?php
$path = 'resources/views/videos/gallery.blade.php';
$content = file_get_contents($path);
$eol = strpos($content, "\r\n") !== false ? "\r\n" : "\n";
$lines = explode($eol, $content);

// L323 = index 322 — quitar backslash
$lines[322] = "        \$annAv    = \$ann->avatar_photo_id ? url('/fotos/'.\$ann->avatar_photo_id.'/ver') : null;";

file_put_contents($path, implode($eol, $lines));
echo "L323: ".$lines[322]."\n";
