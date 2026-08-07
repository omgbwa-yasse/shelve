<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class HandleOpacErrors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (AuthenticationException $e) {
            // Si nous sommes dans l'espace OPAC, rediriger vers la page de connexion OPAC
            if ($request->is('opac/*')) {
                return redirect()->route('opac.login')->with('error', 'Vous devez vous connecter pour accéder à cette page.');
            }

            // Sinon, laisser Laravel gérer l'erreur normalement
            throw $e;
        } catch (\Exception $e) {
            // En cas d'erreur dans l'OPAC, rester dans l'espace OPAC
            if ($request->is('opac/*')) {
                return redirect()->route('opac.index')->with('error', 'Une erreur est survenue. Veuillez réessayer.');
            }

            // Sinon, laisser Laravel gérer l'erreur normalement
            throw $e;
        }
    }
}
