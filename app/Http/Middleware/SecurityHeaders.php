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

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://kit.fontawesome.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
                "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com https://ka-f.fontawesome.com",
                "img-src 'self' data: https: blob:",
                "connect-src 'self' https://kjhaquimghhejqznleyn.supabase.co wss:",
                "media-src 'self' blob:",
                "object-src 'none'",
                "frame-ancestors 'self'",
            ]);
        } else {
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://127.0.0.1:5173 http://localhost:5173 https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://kit.fontawesome.com",
                "style-src 'self' 'unsafe-inline' http://127.0.0.1:5173 http://localhost:5173 https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
                "font-src 'self' http://127.0.0.1:5173 http://localhost:5173 https://fonts.gstatic.com https://cdnjs.cloudflare.com https://ka-f.fontawesome.com",
                "img-src 'self' data: https: http: blob:",
                "connect-src 'self' http://127.0.0.1:5173 http://localhost:5173 ws://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:8080 ws://localhost:8080 wss://localhost:8080 https://kjhaquimghhejqznleyn.supabase.co wss:",
                "media-src 'self' blob:",
                "object-src 'none'",
            ]);
        }

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
