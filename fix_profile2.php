<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userId = "e59f7bc8-c207-43bf-a976-6ed52d8be4fe";

$profile = DB::table("profiles")->where("user_id", $userId)->first();

if (!$profile) {
    echo "ERROR: No existe perfil para este usuario\n";
} else {
    $updated = DB::table("profiles")
        ->where("user_id", $userId)
        ->update([
            "profile_completed"    => true,
            "profile_completed_at" => now(),
            "nickname"             => $profile->nickname ?? "usuario_" . substr($userId, 0, 8),
        ]);
    echo $updated ? "OK: perfil actualizado para " . $userId : "FAIL: no se actualizó";
}

