<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $userId = Auth::id();
            // Actualizar solo cada 2 minutos para no saturar la BD
            $cacheKey = "last_seen_{$userId}";
            if (!Cache::has($cacheKey)) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['last_seen_at' => now()]);
                Cache::put($cacheKey, true, 120); // 2 minutos
            }
        }
        return $next($request);
    }
}