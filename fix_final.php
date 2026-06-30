<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Marcar TODOS los perfiles como completados
$updated = DB::table("profiles")->update([
    "profile_completed" => true,
    "profile_completed_at" => now(),
]);
echo "Perfiles actualizados: " . $updated . "\n";

// Verificar el usuario logueado
$p = DB::table("profiles")->where("user_id","e59f7bc8-c207-43bf-a976-6ed52d8be4fe")->first();
echo "Usuario logueado - completed: " . ($p->profile_completed ? "SI" : "NO") . "\n";
echo "Nickname: " . ($p->nickname ?? "sin nickname") . "\n";

