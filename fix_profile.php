<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$updated = DB::table("profiles")
    ->where("user_id", "ae956f70-1755-4546-93e8-c394deaa6e78")
    ->update([
        "profile_completed"    => true,
        "profile_completed_at" => now(),
        "nickname"             => "juan_perez",
    ]);

echo $updated ? "OK: perfil actualizado" : "ERROR: no se actualizó";

