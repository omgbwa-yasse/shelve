<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Protection contre le clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Protection contre le sniffing de type MIME
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Protection XSS pour les anciens navigateurs
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Politique de référent plus stricte
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Politique de sécurité du contenu basique
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' localhost:* 127.0.0.1:* blob: https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' localhost:* 127.0.0.1:* https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' data: localhost:* 127.0.0.1:* https://cdnjs.cloudflare.com https://fonts.gstatic.com https://cdn.jsdelivr.net; " .
               "connect-src 'self' ws://localhost:* ws://127.0.0.1:* localhost:* 127.0.0.1:*; " .
               "worker-src 'self' blob:;";
        $response->headers->set('Content-Security-Policy', $csp);

        // Protection contre les attaques de permissions
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
