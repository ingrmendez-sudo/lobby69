<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = DB::table('users')
    ->whereIn('username', ['parejacdmx2', 'parejamty', 'daniela_bcn', 'admin_lobby69'])
    ->select('id', 'username', 'membership_type', 'email')
    ->get();

foreach ($users as $u) {
    echo $u->username . ' | ' . ($u->membership_type ?? 'NULL') . ' | id=' . $u->id . PHP_EOL;
}
