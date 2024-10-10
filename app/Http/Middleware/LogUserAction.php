<?php

namespace App\Http\Middleware;

use Closure;

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserAction
{
<<<<<<< HEAD

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (auth()->check()) {
            Log::create([
                'user_id' => auth()->id(),
                'action' => $request->route()->getName(),
                'description' => auth()->user()->name .' - action performed: ' . $request->method() . ' ' . $request->path(),
=======
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // On exécute la requête
        $response = $next($request);

        // Enregistre un log uniquement si l'utilisateur est authentifié
        if (auth()->check()) {
            Log::create([
                'user_id' => auth()->id(),
                'action' => $request->route()->getName(), // Enregistre le nom de la route comme action
                'description' => 'Action performed: ' . $request->method() . ' ' . $request->path(),
>>>>>>> 8859924a56007ef392f95f52efcc9239e4de3630
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
        return $response;
    }
}
