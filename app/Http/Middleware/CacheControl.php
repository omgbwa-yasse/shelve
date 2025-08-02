<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Ajouter des headers de cache pour les ressources statiques
        if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*')) {
            $response->header('Cache-Control', 'public, max-age=31536000'); // 1 année
        }

        // Cache pour les API endpoints
        if ($request->is('api/*')) {
            $response->header('Cache-Control', 'public, max-age=300'); // 5 minutes
        }

        return $response;
    }
}
