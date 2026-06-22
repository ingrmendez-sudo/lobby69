<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    private array $except = [
        'cambiar-password',
        'logout',
        'debug-auth',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Saltar rutas excluidas
        foreach ($this->except as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // Si no ha cambiado la contraseña, redirigir
        if (!$user->password_changed) {
            return redirect()->route('password.change')
                ->with('warning', 'Debes cambiar tu contraseña temporal antes de continuar.');
        }

        return $next($request);
    }
}