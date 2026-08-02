<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$videos = DB::table('videos')->select('id','caption','thumbnail_path','views','status')->orderBy('views','desc')->limit(5)->get();
foreach($videos as $v) {
    echo $v->id.' | thumb='.($v->thumbnail_path ?? 'NULL').' | views='.(int)$v->views.' | status='.$v->status.PHP_EOL;
}
