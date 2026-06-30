<?php
require __DIR__."/vendor/autoload.php";
$app = require __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$r = DB::table("profiles")->where("user_id","e59f7bc8-c207-43bf-a976-6ed52d8be4fe")->first();
if(!$r){ 
    DB::table("profiles")->insert(["id"=>\Illuminate\Support\Str::uuid(),"user_id"=>"e59f7bc8-c207-43bf-a976-6ed52d8be4fe","nickname"=>"admin_lobby","profile_completed"=>true,"profile_completed_at"=>now(),"created_at"=>now(),"updated_at"=>now()]);
    echo "OK: perfil creado";
} else {
    DB::table("profiles")->where("user_id","e59f7bc8-c207-43bf-a976-6ed52d8be4fe")->update(["profile_completed"=>true,"profile_completed_at"=>now()]);
    echo "OK: perfil actualizado. nickname=" . $r->nickname;
}

