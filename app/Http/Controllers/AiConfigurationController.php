<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\AiGlobalSetting;
use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AiConfigurationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:ai_configure'); // Décommenté si vous utilisez Spatie Permission
    }

    /**
     * Afficher la page de configuration AI
     */
    public function index()
    {
        $localModels = AiModel::localModels()->active()->get();
        $apiModels = AiModel::apiModels()->active()->get();
        $defaultModel = AiModel::getDefaultModel();

        $settings = [
            'default_model_id' => AiGlobalSetting::get('default_model_id'),
            'default_provider' => AiGlobalSetting::get('default_provider', 'ollama'),
            'auto_sync_ollama' => AiGlobalSetting::get('auto_sync_ollama', true),
            'max_retries' => AiGlobalSetting::get('max_retries', 3),
            'fallback_model_id' => AiGlobalSetting::get('fallback_model_id')
        ];

        return view('ai.configuration.index', compact('localModels', 'apiModels', 'defaultModel', 'settings'));
    }

    /**
     * Sauvegarder la configuration générale
     */
    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_model_id' => 'nullable|exists:ai_models,id',
            'default_provider' => 'required|in:ollama,openai,anthropic,grok',
            'auto_sync_ollama' => 'boolean',
            'max_retries' => 'required|integer|min:1|max:10',
            'fallback_model_id' => 'nullable|exists:ai_models,id'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Réinitialiser tous les modèles par défaut
            AiModel::where('is_default', true)->update(['is_default' => false]);

            // Sauvegarder les paramètres
            foreach ($request->only(['default_provider', 'auto_sync_ollama', 'max_retries']) as $key => $value) {
                AiGlobalSetting::set($key, $value);
            }

            if ($request->filled('default_model_id')) {
                AiGlobalSetting::set('default_model_id', $request->default_model_id, 'integer');
                AiModel::find($request->default_model_id)->update(['is_default' => true]);
            }

            if ($request->filled('fallback_model_id')) {
                AiGlobalSetting::set('fallback_model_id', $request->fallback_model_id, 'integer');
            }

            return back()->with('success', 'Configuration mise à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour configuration AI: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Ajouter un nouveau modèle API
     */
    public function storeApiModel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'provider' => 'required|in:openai,anthropic,grok',
            'api_endpoint' => 'required|url',
            'api_key' => 'required|string',
            'external_model_id' => 'required|string',
            'cost_per_token_input' => 'nullable|numeric|min:0',
            'cost_per_token_output' => 'nullable|numeric|min:0',
            'max_context_length' => 'nullable|integer|min:1',
            'default_temperature' => 'nullable|numeric|min:0|max:2'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {            $model = new AiModel();
            $model->fill($request->only([
                'name', 'provider', 'api_endpoint', 'external_model_id',
                'cost_per_token_input', 'cost_per_token_output',
                'max_context_length', 'default_temperature'
            ]));
            
            $model->model_type = 'api';
            $model->api_type = 'external';
            $model->is_active = true;
            $model->version = $request->get('version', '1.0.0'); // Valeur par défaut
            $model->capabilities = $request->get('capabilities', []); // Array vide par défaut
            $model->setApiKey($request->api_key);

            // Configuration spécifique par provider
            $model->api_headers = $this->getProviderHeaders($request->provider);
            $model->api_parameters = $this->getProviderDefaults($request->provider);

            $model->save();

            return back()->with('success', "Modèle {$model->name} ajouté avec succès.");
        } catch (\Exception $e) {
            Log::error('Erreur ajout modèle API: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'ajout: ' . $e->getMessage());
        }
    }

    /**
     * Synchroniser les modèles Ollama
     */
    public function syncOllamaModels()
    {
        try {
            $ollamaService = app(OllamaService::class);
            $synced = $ollamaService->syncModels();

            return back()->with('success', "{$synced} modèles Ollama synchronisés.");
        } catch (\Exception $e) {
            Log::error('Erreur sync Ollama: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Tester un modèle API
     */
    public function testApiModel(Request $request, AiModel $model)
    {
        if (!$model->isApiModel()) {
            return response()->json(['success' => false, 'message' => 'Ce n\'est pas un modèle API']);
        }

        try {
            // Créer un service de test selon le provider
            $testService = $this->createTestService($model);
            $response = $testService->testConnection();

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message'],
                'response_time' => $response['response_time'] ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur test modèle {$model->name}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de test: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Supprimer un modèle API
     */
    public function destroyApiModel(AiModel $model)
    {
        if (!$model->isApiModel()) {
            return back()->with('error', 'Seuls les modèles API peuvent être supprimés.');
        }

        try {
            $model->delete();
            return back()->with('success', "Modèle {$model->name} supprimé avec succès.");
        } catch (\Exception $e) {
            Log::error('Erreur suppression modèle: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Configuration des headers par provider
     */
    private function getProviderHeaders(string $provider): array
    {
        return match($provider) {
            'openai' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer {api_key}'
            ],
            'anthropic' => [
                'Content-Type' => 'application/json',
                'x-api-key' => '{api_key}',
                'anthropic-version' => '2023-06-01'
            ],
            'grok' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer {api_key}'
            ],
            default => []
        };
    }

    /**
     * Paramètres par défaut par provider
     */
    private function getProviderDefaults(string $provider): array
    {
        return match($provider) {
            'openai' => [
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0
            ],
            'anthropic' => [
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'top_p' => 1
            ],
            'grok' => [
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'top_p' => 1
            ],
            default => []
        };
    }

    /**
     * Créer un service de test pour le provider
     */
    private function createTestService(AiModel $model)
    {
        // Cette méthode sera implémentée selon les besoins spécifiques
        // Pour l'instant, retourne un mock
        return new class($model) {
            private $model;

            public function __construct($model) {
                $this->model = $model;
            }

            public function testConnection(): array {
                // Simulation d'un test simple
                return [
                    'success' => true,
                    'message' => 'Test de connexion simulé avec succès',
                    'response_time' => 0.5
                ];
            }
        };
    }
}
