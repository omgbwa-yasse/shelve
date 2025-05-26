<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use Illuminate\Http\Request;
use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;

class AiModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $models = AiModel::paginate(15);
        return view('ai.models.index', compact('models'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.models.create');
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
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'version' => 'required|string|max:50',
            'api_type' => 'required|string|max:50',
            'capabilities' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        AiModel::create($validated);

        return redirect()->route('ai.models.index')->with('success', 'AI Model created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function show(AiModel $aiModel)
    {
        return view('ai.models.show', compact('aiModel'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function edit(AiModel $aiModel)
    {
        return view('ai.models.edit', compact('aiModel'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiModel $aiModel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'version' => 'required|string|max:50',
            'api_type' => 'required|string|max:50',
            'capabilities' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $aiModel->update($validated);

        return redirect()->route('ai.models.index')->with('success', 'AI Model updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiModel $aiModel)
    {
        $aiModel->delete();

        return redirect()->route('ai.models.index')->with('success', 'AI Model deleted successfully');
    }





    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    /**
     * Synchroniser les modèles Ollama
     */
    public function syncOllamaModels(): JsonResponse
    {
        try {
            $synced = $this->ollamaService->syncModels();

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$synced} models from Ollama",
                'synced_count' => $synced
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync models: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier l'état de santé d'Ollama
     */
    public function healthCheck(): JsonResponse
    {
        $health = $this->ollamaService->healthCheck();

        return response()->json($health, $health['status'] === 'healthy' ? 200 : 503);
    }

    /**
     * Obtenir les modèles disponibles sur Ollama
     */
    public function getOllamaModels(): JsonResponse
    {
        try {
            $models = $this->ollamaService->getAvailableModels();

            return response()->json([
                'success' => true,
                'models' => $models
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
