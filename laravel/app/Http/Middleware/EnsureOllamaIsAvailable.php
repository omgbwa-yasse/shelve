<?php

namespace App\Http\Middleware;

use App\Services\OllamaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureOllamaIsAvailable
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Handle an incoming request that requires Ollama to be available.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Vérifier l'état de santé d'Ollama
            $healthCheck = $this->ollamaService->healthCheck();

            if ($healthCheck['status'] !== 'healthy') {
                Log::warning('Tentative d\'accès à une route nécessitant Ollama alors que le service est indisponible', [
                    'health_check' => $healthCheck,
                    'route' => $request->route()->getName(),
                    'user_id' => $request->user() ? $request->user()->id : null
                ]);

                // Si c'est une requête AJAX, retourner une erreur JSON
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Le service Ollama n\'est pas disponible: ' . $healthCheck['message']
                    ], 503);
                }

                // Sinon, rediriger avec un message d'erreur
                return redirect()->back()->with('error', 'Le service Ollama n\'est pas disponible: ' . $healthCheck['message']);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de disponibilité d\'Ollama', [
                'exception' => $e->getMessage(),
                'route' => $request->route()->getName(),
                'user_id' => $request->user() ? $request->user()->id : null
            ]);

            // Si c'est une requête AJAX, retourner une erreur JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de la vérification de disponibilité d\'Ollama: ' . $e->getMessage()
                ], 503);
            }

            // Sinon, rediriger avec un message d'erreur
            return redirect()->back()->with('error', 'Erreur lors de la vérification de disponibilité d\'Ollama: ' . $e->getMessage());
        }
    }
}
