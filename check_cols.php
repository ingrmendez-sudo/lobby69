<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$rows = DB::select("SELECT DISTINCT profile_type, COUNT(*) as total FROM profiles GROUP BY profile_type");
foreach($rows as $r) echo ($r->profile_type ?? 'NULL') . ' | ' . $r->total . PHP_EOL;
echo '---' . PHP_EOL;
$vids = DB::select("SELECT COUNT(*) as total FROM videos WHERE status = 'approved'");
echo 'Videos aprobados: ' . $vids[0]->total . PHP_EOL;