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
        $userId = Auth::id() ?? $request->ip();
        $rateLimiterKey = ($key ?? 'general') . ':' . $userId;

        // Convertir les minutes en secondes pour le decay rate
        $decaySeconds = $decayMinutes * 60;

        if (RateLimiter::tooManyAttempts($rateLimiterKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);

            return response()->json([
                'message' => 'Trop de tentatives. Veuillez patienter ' . ceil($seconds / 60) . ' minute(s).',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::increment($rateLimiterKey, $decaySeconds);

        return $next($request);
    }
}
