<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;

class ThrottleSensitiveRoutes
{
    /**
     * Limites por tipo de accion:
     * - announcements : 5 por hora por usuario
     * - messages      : 60 por minuto por usuario
     * - uploads       : 10 por minuto por usuario
     */
    private array $limits = [
        'announcements' => ['maxAttempts' => 5,  'decaySeconds' => 3600],
        'messages'      => ['maxAttempts' => 60, 'decaySeconds' => 60],
        'uploads'       => ['maxAttempts' => 10, 'decaySeconds' => 60],
    ];

    public function handle(Request $request, Closure $next, string $type = 'messages')
    {
        // Admins exentos de rate limiting
        if (Gate::allows('admin')) {
            return $next($request);
        }

        $limit = $this->limits[$type] ?? $this->limits['messages'];
        $key   = $type . '|' . $request->user()?->id . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, $limit['maxAttempts'])) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'ok'      => false,
                'message' => "Demasiadas solicitudes. Intenta en {$seconds} segundos.",
            ], 429);
        }

        RateLimiter::hit($key, $limit['decaySeconds']);

        return $next($request);
    }
}
