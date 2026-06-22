<?php
// FIX 1: Reescribir AdminOnly.php con trim()
$middleware = <<<'PHP'
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = trim((string) $user->role);

        if ($role !== 'admin') {
            abort(403, 'Acceso restringido a administradores.');
        }

        return $next($request);
    }
}
PHP;

$path = __DIR__ . '/app/Http/Middleware/AdminOnly.php';
file_put_contents($path, $middleware);
$bytes = array_values(unpack('C*', substr(file_get_contents($path), 0, 3)));
echo "[OK] AdminOnly.php — Bytes: {$bytes[0]} {$bytes[1]} {$bytes[2]}" . PHP_EOL;

// FIX 2: Mover /debug-auth FUERA del grupo admin en routes/web.php
$routes = file_get_contents(__DIR__ . '/routes/web.php');

// Eliminar la ruta debug de dentro del grupo admin
$old = <<<'ROUTE'

Route::get('/debug-auth', function() {
    if (!auth()->check()) return response()->json(['auth' => false]);
    $user = auth()->user();
    return response()->json([
        'id'    => $user->id,
        'email' => $user->email,
        'role'  => $user->role,
        'role_type' => gettype($user->role),
        'is_admin_check' => ($user->role === 'admin'),
        'active' => $user->active,
    ]);
})->middleware('auth');

ROUTE;

$new = '';
$routes = str_replace($old, $new, $routes);

// Agregar debug-auth al final, fuera de cualquier grupo
$debugRoute = <<<'ROUTE'

// Debug temporal
Route::get('/debug-auth', function() {
    if (!auth()->check()) return response()->json(['auth' => false]);
    $user = auth()->user();
    return response()->json([
        'id'         => $user->id,
        'email'      => $user->email,
        'role'       => $user->role,
        'role_raw'   => DB::table('users')->where('email',$user->email)->value('role'),
        'role_type'  => gettype($user->role),
        'is_admin'   => ($user->role === 'admin'),
        'trim_check' => (trim((string)$user->role) === 'admin'),
        'active'     => $user->active,
    ]);
})->middleware('auth');
ROUTE;

$routes .= $debugRoute;
file_put_contents(__DIR__ . '/routes/web.php', $routes);
echo "[OK] routes/web.php — debug-auth movido fuera del grupo admin" . PHP_EOL;

echo PHP_EOL . "Ejecutar ahora:" . PHP_EOL;
echo "  php artisan route:clear && php artisan serve" . PHP_EOL;
echo "  Visitar: http://localhost:8000/debug-auth" . PHP_EOL;
?>
