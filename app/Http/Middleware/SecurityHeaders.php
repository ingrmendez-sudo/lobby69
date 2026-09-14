<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://kit.fontawesome.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; " .
            "font-src 'self' data: https://fonts.gstatic.com https://ka-f.fontawesome.com https://cdnjs.cloudflare.com; " .
            "img-src 'self' data: blob: https://*.supabase.co https://www.genspark.ai; " .
            "connect-src 'self' wss://localhost:8080 ws://localhost:8080 https://*.supabase.co https://cdn.jsdelivr.net; " .
            "frame-ancestors 'self';"
        );

        return $response;
    }
}
