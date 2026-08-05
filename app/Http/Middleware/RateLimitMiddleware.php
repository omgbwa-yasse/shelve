<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key = null, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        // Désactivable uniquement pour la suite de conformité de l'API, qui éprouve
        // l'authentification et dépasse mécaniquement le quota `auth,5,60`.
        // Voir config/rate-limit.php.
        if (!config('rate-limit.enabled', true)) {
            return $next($request);
        }

        $userId = Auth::id() ?? $request->ip();
        $rateLimiterKey = ($key ?? 'general') . ':' . $userId;

        // Convertir les minutes en secondes pour le decay rate
        $decaySeconds = $decayMinutes * 60;

        if (RateLimiter::tooManyAttempts($rateLimiterKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);

            // TODO conformité : le corps devra suivre la RFC 7807 (CONVENTIONS §4).
            // Il n'est pas modifié ici car le portail public consomme la clé `message` ;
            // à traiter lors du portage de D15/D16.
            return response()->json([
                'message' => 'Trop de tentatives. Veuillez patienter ' . ceil($seconds / 60) . ' minute(s).',
                'retry_after' => $seconds
            ], 429, [
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => time() + $seconds,
            ]);
        }

        RateLimiter::increment($rateLimiterKey, $decaySeconds);

        $response = $next($request);

        // En-têtes de quota exigés par CONVENTIONS §11 : purement additifs.
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, RateLimiter::remaining($rateLimiterKey, $maxAttempts)),
        ]);

        return $response;
    }
}
