<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ver columnas de profiles
$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'profiles' ORDER BY column_name");
echo "=== COLUMNAS DE profiles ===\n";
foreach ($cols as $c) echo "  " . $c->column_name . "\n";

// Ver columnas de users
$cols2 = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = 'users' ORDER BY column_name");
echo "\n=== COLUMNAS DE users ===\n";
foreach ($cols2 as $c) echo "  " . $c->column_name . "\n";
