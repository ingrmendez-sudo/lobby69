<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = DB::table('users')
    ->whereIn('username', ['Luna_MX', 'ParejaCDMX2', 'Single_uno'])
    ->select('id', 'username', 'membership_type', 'email')
    ->get();

if ($users->isEmpty()) {
    echo 'NINGUNO DE ESOS USUARIOS EXISTE - verificar usernames exactos' . PHP_EOL;
    $sample = DB::table('users')->select('id','username','membership_type')->limit(5)->get();
    foreach ($sample as $u) {
        echo 'MUESTRA: ' . $u->username . ' | ' . ($u->membership_type ?? 'NULL') . PHP_EOL;
    }
} else {
    foreach ($users as $u) {
        echo $u->username . ' | ' . ($u->membership_type ?? 'NULL') . ' | id=' . $u->id . PHP_EOL;
    }
}
