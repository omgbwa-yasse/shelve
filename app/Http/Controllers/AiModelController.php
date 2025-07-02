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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $models = AiModel::orderBy('created_at', 'desc')->paginate(15);

        // Si c'est une requête AJAX, retourner JSON
        if ($request->ajax() || $request->has('ajax')) {
            return response()->json([
                'success' => true,
                'models' => $models->items(),
                'pagination' => [
                    'total' => $models->total(),
                    'per_page' => $models->perPage(),
                    'current_page' => $models->currentPage(),
                    'last_page' => $models->lastPage(),
                ]
            ]);
        }

        // Sinon, retourner la vue normale
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
     * Display the specified resource by name.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function showByName($name)
    {
        // Récupérer le modèle par son nom depuis la base de données
        $aiModel = AiModel::where('name', $name)->firstOrFail();

        // Si le modèle n'existe pas dans la base mais existe dans Ollama,
        // on peut essayer de le récupérer via l'API d'Ollama
        if (!$aiModel && $this->ollamaService->modelExists($name)) {
            $modelData = $this->ollamaService->getModelDetails($name);
            // Créer un objet temporaire avec les données du modèle
            $aiModel = new AiModel([
                'name' => $name,
                'provider' => 'Ollama',
                'type' => $modelData['type'] ?? 'text',
                'version' => $modelData['version'] ?? '1.0',
                'status' => 'active',
                // Autres propriétés pertinentes...
            ]);
        }

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

    /**
     * Afficher le formulaire de synchronisation des modèles Ollama
     */
    public function syncModelsForm()
    {
        $models = AiModel::where('provider', 'ollama')->get();

        try {
            $ollamaModels = $this->ollamaService->getAvailableModels();
            $health = $this->ollamaService->healthCheck();
            $isConnected = $health['status'] === 'healthy';
        } catch (\Exception $e) {
            $ollamaModels = [];
            $isConnected = false;
        }

        return view('ai.models.sync', [
            'models' => $models,
            'ollamaModels' => $ollamaModels,
            'isConnected' => $isConnected
        ]);
    }

    /**
     * Train the specified AI Model.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiModel  $model
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function trainModel(Request $request, AiModel $model)
    {
        try {
            // Mark the model as training
            $model->status = 'training';
            $model->save();

            // In a real implementation, you might want to dispatch a job here
            // For now, we'll just simulate training by updating the last trained timestamp
            $model->last_trained_at = now();
            $model->save();

            if ($request->ajax() || $request->has('ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => __('training_started_for_model', ['model' => $model->name]),
                    'model' => $model
                ]);
            }

            return redirect()->route('ai.models.show', ['model' => $model->id])
                ->with('success', __('training_started_for_model', ['model' => $model->name]));
        } catch (\Exception $e) {
            if ($request->ajax() || $request->has('ajax')) {
                return response()->json([
                    'success' => false,
                    'message' => __('training_error') . ': ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('ai.models.show', ['model' => $model->id])
                ->with('error', __('training_error') . ': ' . $e->getMessage());
        }
    }
}
