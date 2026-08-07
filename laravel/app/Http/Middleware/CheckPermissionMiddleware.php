<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Middleware pour vérifier les permissions avec Gate
     *
     * Usage dans les routes:
     * Route::get('/admin/users', [UserController::class, 'index'])
     *     ->middleware('permission:users.view');
     *
     * Route::group(['middleware' => ['permission:access-admin']], function () {
     *     // Routes d'administration
     * });
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Vérifier la permission avec Gate
        if (!Gate::allows($permission)) {
            // Pour les requêtes AJAX/API
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Insufficient permissions',
                    'required_permission' => $permission
                ], 403);
            }

            // Pour les requêtes web classiques
            abort(403, "Permission requise: {$permission}");
        }

        return $next($request);
    }
}
