<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Ver estructura real de la consulta que alimenta $conversations
$convos = DB::table('users as u')
    ->join('messages as m', function($j) {
        $j->whereRaw('(m.sender_id = u.id OR m.receiver_id = u.id)');
    })
    ->leftJoin('profiles as p', 'p.user_id', '=', 'u.id')
    ->leftJoin('photos as ph', function($j) {
        $j->on('ph.user_id', '=', 'u.id')
          ->where('ph.is_profile_photo', '=', true);
    })
    ->select('u.id', 'u.username', 'p.display_name', 'ph.id as avatar_photo_id', 'ph.file_path as avatar_path')
    ->limit(3)
    ->get();

foreach ($convos as $c) {
    echo 'user=' . $c->username . ' | photo_id=' . ($c->avatar_photo_id ?? 'NULL') . ' | path=' . ($c->avatar_path ?? 'NULL') . PHP_EOL;
}
