<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FirstLoginMiddleware
{
    private array $except = [
        'cambiar-password',
        'perfil/configurar',
        'logout',
        'login',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->password_changed) {
            return $next($request);
        }

        foreach ($this->except as $except) {
            if ($request->is($except) || $request->is($except . '/*')) {
                return $next($request);
            }
        }

        $profile = \Illuminate\Support\Facades\DB::table('profiles')
            ->where('user_id', $user->id)
            ->first();

        if ($profile && $profile->profile_completed) {
            return redirect()->route('profile.change-password')
                ->with('info', 'Por seguridad, debes cambiar tu contraseña antes de continuar.');
        }

        return redirect()->route('profile.setup')
            ->with('info', 'Bienvenido a LOBBY69. Completa tu perfil para comenzar.');
    }
}