<?php

namespace App\Http\Controllers;

use App\Models\AiInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use App\Services\OllamaService;
use App\Services\SettingService;

class AiInteractionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $interactions = AiInteraction::with(['user', 'aiModel', 'actions', 'feedback'])->paginate(15);
        $models = \App\Models\AiModel::where('is_active', true)->get();
        return view('ai.interactions.index', compact('interactions', 'models'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.interactions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ai_model_id' => 'nullable|exists:ai_models,id', // Maintenant optionnel
            'input' => 'required|string',
            'output' => 'nullable|string',
            'parameters' => 'nullable|json',
            'tokens_used' => 'nullable|numeric',
            'module_type' => 'nullable|string',
            'module_id' => 'nullable|integer',
            'status' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        // Si aucun modèle AI n'est spécifié, utiliser le modèle par défaut
        if (empty($validated['ai_model_id'])) {
            $defaultModelId = $this->getDefaultAiModelId('analysis');
            if ($defaultModelId) {
                $validated['ai_model_id'] = $defaultModelId;
            } else {
                return redirect()->back()->withErrors(['ai_model_id' => 'Aucun modèle AI par défaut configuré.']);
            }
        }

        AiInteraction::create($validated);

        return redirect()->route('ai.interactions.index')->with('success', 'AI Interaction created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function show(AiInteraction $aiInteraction)
    {
        return view('ai.interactions.show', compact('aiInteraction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function edit(AiInteraction $aiInteraction)
    {
        return view('ai.interactions.edit', compact('aiInteraction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiInteraction $aiInteraction)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ai_model_id' => 'required|exists:ai_models,id',
            'input' => 'required|string',
            'output' => 'nullable|string',
            'parameters' => 'nullable|json',
            'tokens_used' => 'nullable|numeric',
            'module_type' => 'nullable|string',
            'module_id' => 'nullable|integer',
            'status' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $aiInteraction->update($validated);

        return redirect()->route('ai.interactions.index')->with('success', 'AI Interaction updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiInteraction $aiInteraction)
    {
        $aiInteraction->delete();

        return redirect()->route('ai.interactions.index')->with('success', 'AI Interaction deleted successfully');
    }



    protected OllamaService $ollamaService;
    protected SettingService $settingService;

    public function __construct(OllamaService $ollamaService, SettingService $settingService)
    {
        $this->ollamaService = $ollamaService;
        $this->settingService = $settingService;
    }

    /**
     * Récupère le modèle AI par défaut basé sur le type d'action
     */
    private function getDefaultAiModelId(string $actionType = 'analysis'): ?int
    {
        $modelName = $this->settingService->get("model_{$actionType}", 'gemma3:4b');

        // Si la valeur est JSON encodée, la décoder
        if (is_string($modelName) && json_decode($modelName)) {
            $modelName = json_decode($modelName, true);
        }

        // Chercher le modèle AI correspondant par nom
        $aiModel = \App\Models\AiModel::where('name', $modelName)
            ->where('is_active', true)
            ->first();

        return $aiModel ? $aiModel->id : null;
    }

    /**
     * Créer et traiter une nouvelle interaction
     */
    public function createAndProcess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ai_model_id' => 'required|exists:ai_models,id',
            'input' => 'required|string',
            'parameters' => 'nullable|array',
            'module_type' => 'nullable|string',
            'module_id' => 'nullable|integer',
            'session_id' => 'nullable|string',
            'async' => 'boolean'
        ]);

        try {
            // Créer l'interaction
            $interaction = $this->ollamaService->createInteraction(
                Auth::id(),
                $validated['ai_model_id'],
                $validated['input'],
                $validated['parameters'] ?? [],
                $validated['module_type'] ?? null,
                $validated['module_id'] ?? null,
                $validated['session_id'] ?? null
            );

            // Traitement asynchrone ou synchrone
            if ($validated['async'] ?? false) {
                // Traitement en arrière-plan
                Queue::push(function () use ($interaction) {
                    $this->ollamaService->processInteraction($interaction);
                });

                return response()->json([
                    'success' => true,
                    'interaction_id' => $interaction->id,
                    'status' => 'queued',
                    'message' => 'Interaction queued for processing'
                ]);
            } else {
                // Traitement immédiat
                $processedInteraction = $this->ollamaService->processInteraction($interaction);

                return response()->json([
                    'success' => true,
                    'interaction' => $processedInteraction,
                    'status' => $processedInteraction->status
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Streaming pour les interactions en temps réel
     */
    public function stream(Request $request)
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'prompt' => 'required|string',
            'options' => 'nullable|array'
        ]);

        $response = response()->stream(function () use ($validated) {
            foreach ($this->ollamaService->generateStream(
                $validated['model'],
                $validated['prompt'],
                $validated['options'] ?? []
            ) as $chunk) {
                echo "data: " . json_encode($chunk) . "\n\n";
                ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);

        return $response;
    }

    /**
     * Chat avec contexte
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'messages' => 'required|array',
            'messages.*.role' => 'required|in:system,user,assistant',
            'messages.*.content' => 'required|string',
            'options' => 'nullable|array'
        ]);

        try {
            $result = $this->ollamaService->chat(
                $validated['model'],
                $validated['messages'],
                $validated['options'] ?? []
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
