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