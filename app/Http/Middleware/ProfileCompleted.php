<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileCompleted
{
    private array $except = [
        'perfil/configurar',
        'cambiar-password',
        'logout',
        'debug-auth',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) return $next($request);

        foreach ($this->except as $path) {
            if ($request->is($path)) return $next($request);
        }

        $profile = DB::table('profiles')->where('user_id', auth()->id())->first();

        if (!$profile || !$profile->profile_completed) {
            return redirect()->route('profile.setup')
                ->with('warning', 'Completa tu perfil para acceder a LOBBY69.');
        }

        return $next($request);
    }
}