<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\MembershipPayment::whereNotNull('public_code')->latest()->first();
if (!$p) { exit("No hay ninguna orden SPEI todavia.\n"); }

$s = new App\Services\SpeiService;

file_put_contents('prueba-correo.html', view('emails.instrucciones-spei', [
    'payment'  => $p,
    'spei'     => $s->instructions($p),
    'planName' => 'Explorer',
    'userName' => 'Single_uno',
])->render());

echo "Listo. Vence: " . $p->expires_at . "\n";
