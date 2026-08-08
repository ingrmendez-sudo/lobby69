<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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

        $userId  = (string) auth()->id();
        $profile = Cache::remember("profile_completed_{$userId}", 120, function () use ($userId) {
            return DB::table('profiles')->where('user_id', $userId)->first();
        });

        if (!$profile || !$profile->profile_completed) {
            Cache::forget("profile_completed_{$userId}");
            return redirect()->route('profile.setup')
                ->with('warning', 'Completa tu perfil para acceder a LOBBY69.');
        }

        return $next($request);
    }
}