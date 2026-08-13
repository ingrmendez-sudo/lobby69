<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    private array $except = [
        'cambiar-password',
        'bienvenido',
        'perfil/configurar',
        'logout',
        'login',
        'debug-auth',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        foreach ($this->except as $path) {
            if ($request->is($path) || $request->is($path . '/*')) {
                return $next($request);
            }
        }

        if (!$user->password_changed) {
            return redirect()->route('profile.change-password')
                ->with('warning', 'Debes cambiar tu contraseña temporal antes de continuar.');
        }

        return $next($request);
    }
}
