<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SettingService;

class McpProxyController extends Controller
{
    protected $mcpBaseUrl;
    protected $settingService;

    // Constantes pour les modèles par défaut
    const DEFAULT_MODEL_FALLBACK = 'gemma3:4b';
    const MCP_CONNECTION_ERROR = 'Failed to connect to MCP server';

    public function __construct(SettingService $settingService)
    {
        // Get the MCP server URL from config (add to config/services.php)
        $this->mcpBaseUrl = config('services.mcp.url', 'http://localhost:3000');
        $this->settingService = $settingService;
    }

    /**
     * Récupère le modèle par défaut depuis les settings pour un type d'action donné
     *
     * @param string $actionType Le type d'action (summary, keywords, analysis)
     * @return string Le nom du modèle
     */
    private function getDefaultModel(string $actionType): string
    {
        $settingKey = "model_{$actionType}";
        $model = $this->settingService->get($settingKey, self::DEFAULT_MODEL_FALLBACK);

        // Si la valeur est JSON encodée, la décoder
        if (is_string($model) && json_decode($model)) {
            $model = json_decode($model, true);
        }

        return is_string($model) ? $model : self::DEFAULT_MODEL_FALLBACK;
    }

    /**
     * Proxy a request to the MCP server for record enrichment
     *
     * @param Request $request
     * @param int $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function enrich(Request $request, $id)
    {
        try {
            // Récupérer le modèle par défaut pour l'analyse depuis les paramètres
            $defaultModel = $this->getDefaultModel('analysis');

            Log::info('MCP Enrich Request', [
                'record_id' => $id,
                'model' => $defaultModel
            ]);

            $response = Http::post("{$this->mcpBaseUrl}/api/enrich/{$id}", [
                'model' => $defaultModel
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Enrichment Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => self::MCP_CONNECTION_ERROR], 500);
        }
    }

    /**
     * Proxy a request to the MCP server for extracting keywords
     *
     * @param Request $request
     * @param int $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function extractKeywords(Request $request, $id)
    {
        try {
            // Récupérer le modèle par défaut pour l'extraction de mots-clés depuis les paramètres
            $defaultModel = $this->getDefaultModel('keywords');

            Log::info('MCP Extract Keywords Request', [
                'record_id' => $id,
                'model' => $defaultModel
            ]);

            $response = Http::post("{$this->mcpBaseUrl}/api/extract-keywords/{$id}", [
                'model' => $defaultModel
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Extract Keywords Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => self::MCP_CONNECTION_ERROR], 500);
        }
    }

    /**
     * Proxy a request to the MCP server for assigning terms
     *
     * @param Request $request
     * @param int $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignTerms(Request $request, $id)
    {
        try {
            // Récupérer le modèle par défaut pour l'analyse depuis les paramètres
            $defaultModel = $this->getDefaultModel('analysis');

            Log::info('MCP Assign Terms Request', [
                'record_id' => $id,
                'model' => $defaultModel
            ]);

            $response = Http::post("{$this->mcpBaseUrl}/api/assign-terms/{$id}", [
                'model' => $defaultModel
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Assign Terms Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => self::MCP_CONNECTION_ERROR], 500);
        }
    }

    /**
     * Proxy a request to the MCP server for record validation
     *
     * @param Request $request
     * @param int $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateRecord(Request $request, $id)
    {
        try {
            // Récupérer le modèle par défaut pour l'analyse depuis les paramètres
            $defaultModel = $this->getDefaultModel('analysis');

            Log::info('MCP Validate Record Request', [
                'record_id' => $id,
                'model' => $defaultModel
            ]);

            $response = Http::post("{$this->mcpBaseUrl}/api/validate/{$id}", [
                'model' => $defaultModel
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Validation Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => self::MCP_CONNECTION_ERROR], 500);
        }
    }

    /**
     * Proxy a request to the MCP server for classification
     *
     * @param Request $request
     * @param int $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function classify(Request $request, $id)
    {
        try {
            // Récupérer le modèle par défaut pour l'analyse depuis les paramètres
            $defaultModel = $this->getDefaultModel('analysis');

            Log::info('MCP Classify Request', [
                'record_id' => $id,
                'model' => $defaultModel
            ]);

            $response = Http::post("{$this->mcpBaseUrl}/api/classify/{$id}", [
                'model' => $defaultModel
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Classification Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => self::MCP_CONNECTION_ERROR], 500);
        }
    }

    /**
     * Proxy a request to the MCP server for report generation
     *
     * @param Request $request
     * @param int $id Record ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function report(Request $request, $id)
    {
        try {
            // Récupérer le modèle par défaut pour la génération de résumés depuis les paramètres
            $defaultModel = $this->getDefaultModel('summary');

            Log::info('MCP Report Request', [
                'record_id' => $id,
                'model' => $defaultModel
            ]);

            $response = Http::post("{$this->mcpBaseUrl}/api/report/{$id}", [
                'model' => $defaultModel
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Report Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => self::MCP_CONNECTION_ERROR], 500);
        }
    }

    /**
     * Endpoint pour récupérer les modèles par défaut configurés
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDefaultModels()
    {
        try {
            $models = [
                'summary' => $this->getDefaultModel('summary'),
                'keywords' => $this->getDefaultModel('keywords'),
                'analysis' => $this->getDefaultModel('analysis'),
            ];

            return response()->json([
                'success' => true,
                'models' => $models
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving default models', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to retrieve default models'], 500);
        }
    }

    /**
     * Créer automatiquement un record via MCP
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRecord(Request $request)
    {
        try {
            // Récupérer le modèle par défaut pour l'analyse depuis les paramètres
            $defaultModel = $this->getDefaultModel('analysis');

            $data = [
                'model' => $defaultModel,
                'action' => 'create_record',
                'attachments' => $request->input('attachments', []),
                'user_id' => $request->input('user_id'),
                'organisation_id' => $request->input('organisation_id')
            ];

            Log::info('MCP Create Record Request', [
                'attachments_count' => count($data['attachments']),
                'model' => $defaultModel,
                'user_id' => $data['user_id']
            ]);

            $response = Http::post("{$this->mcpBaseUrl}/api/create-record", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('MCP Create Record Success', ['record_id' => $responseData['record_id'] ?? 'unknown']);
                return response()->json($responseData, $response->status());
            } else {
                Log::error('MCP Create Record Failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return response()->json(['error' => 'Failed to create record via MCP'], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('MCP Create Record Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => self::MCP_CONNECTION_ERROR], 500);
        }
    }
}
