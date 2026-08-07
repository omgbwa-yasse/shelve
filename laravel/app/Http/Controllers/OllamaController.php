<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\AiModel;
use App\Models\AiInteraction;

class OllamaController extends Controller
{
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Display the main Ollama interface
     */
    public function index()
    {
        $health = $this->ollamaService->healthCheck();
        $models = AiModel::where('provider', 'ollama')->get();

        return view('ai.ollama.index', [
            'health' => $health,
            'models' => $models
        ]);
    }

    public function chat()
    {
        return view('ai.ollama.chat');
    }

    /**
     * Interface de test simple pour Ollama
     */
    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'prompt' => 'required|string',
            'options' => 'nullable|array'
        ]);

        $result = $this->ollamaService->generate(
            $validated['model'],
            $validated['prompt'],
            $validated['options'] ?? []
        );

        return response()->json($result);
    }

    /**
     * Dashboard avec statistiques Ollama
     */
    public function dashboard(): JsonResponse
    {
        $health = $this->ollamaService->healthCheck();
        $models = AiModel::where('provider', 'ollama')->get();
        $recentInteractions = AiInteraction::whereHas('aiModel', function($q) {
            $q->where('provider', 'ollama');
        })->latest()->take(10)->get();

        return response()->json([
            'health' => $health,
            'models_count' => $models->count(),
            'active_models' => $models->where('is_active', true)->count(),
            'recent_interactions' => $recentInteractions,
            'stats' => [
                'total_interactions' => AiInteraction::whereHas('aiModel', function($q) {
                    $q->where('provider', 'ollama');
                })->count(),
                'successful_interactions' => AiInteraction::whereHas('aiModel', function($q) {
                    $q->where('provider', 'ollama');
                })->where('status', 'completed')->count(),
                'failed_interactions' => AiInteraction::whereHas('aiModel', function($q) {
                    $q->where('provider', 'ollama');
                })->where('status', 'failed')->count(),
            ]
        ]);
    }

    /**
     * Vérifier l'état de santé d'Ollama (endpoint API)
     */
    public function healthCheckApi(): JsonResponse
    {
        $health = $this->ollamaService->healthCheck();
        return response()->json($health);
    }
}
