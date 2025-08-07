<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

class McpProxyController extends Controller
{
    private string $mcpUrl;
    private int $timeout;

    // Constantes pour éviter la duplication
    private const CONTENT_TYPE_JSON = 'application/json';
    private const MCP_HEALTH_ENDPOINT = '/api/health';
    private const MCP_TAGS_ENDPOINT = '/api/tags';
    private const DEFAULT_OLLAMA_URL = 'http://localhost:11434';

    public function __construct()
    {
        // L'authentification est gérée au niveau des routes
        // Pas besoin de middleware ici
        $this->mcpUrl = Config::get('mcp.base_url', 'http://localhost:3001');
        $this->timeout = Config::get('mcp.timeout', 120);
    }


    /**
     * Vérifier l'état de la connexion au serveur Ollama
     */
    public function checkOllamaStatus()
    {
        $ollamaUrl = Config::get('ollama.base_url', self::DEFAULT_OLLAMA_URL);

        Log::info('checkOllamaStatus called', ['url' => $ollamaUrl]);

        try {
            $healthEndpoint = "{$ollamaUrl}/api/tags";
            $response = Http::timeout(10)->get($healthEndpoint);

            if (!$response->successful()) {
                return $this->jsonResponse([
                    'success' => false,
                    'status' => 'disconnected',
                    'error' => 'Serveur Ollama non accessible',
                    'http_status' => $response->status()
                ], 503);
            }

            $ollamaData = $this->parseJsonResponse($response, 'ollama_tags');
            $models = $ollamaData['models'] ?? [];

            $result = [
                'success' => true,
                'status' => 'connected',
                'models_count' => count($models),
                'available_models' => array_slice(array_column($models, 'name'), 0, 10), // Limiter à 10 modèles
                'has_gemma3' => $this->hasModel($models, 'gemma3'),
                'http_status' => $response->status(),
                'timestamp' => now()->toISOString()
            ];

            if (config('app.debug')) {
                $result['ollama_url'] = $ollamaUrl;
                $result['all_models'] = $models;
            }

            Log::info('Ollama Status Success', [
                'models_count' => count($models),
                'has_gemma3' => $result['has_gemma3']
            ]);

            return $this->jsonResponse($result);

        } catch (\Exception $e) {
            Log::error('Exception statut Ollama', [
                'message' => $e->getMessage(),
                'url' => $ollamaUrl
            ]);

            return $this->jsonResponse([
                'success' => false,
                'status' => 'disconnected',
                'error' => 'Erreur de connexion au serveur Ollama',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Vérifier si un modèle spécifique est disponible
     */
    private function hasModel(array $models, string $modelName): bool
    {
        foreach ($models as $model) {
            if (isset($model['name']) && str_contains(strtolower($model['name']), strtolower($modelName))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Récupérer les tags MCP
     */
    public function getMcpTags()
    {
        Log::info('getMcpTags called', ['url' => $this->mcpUrl]);

        try {
            $tagsEndpoint = $this->mcpUrl . self::MCP_TAGS_ENDPOINT;
            $response = Http::timeout(5)->get($tagsEndpoint);

            if (!$response->successful()) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Endpoint tags non accessible',
                    'http_status' => $response->status()
                ], $response->status());
            }

            return $this->jsonResponse([
                'success' => true,
                'tags' => $this->parseJsonResponse($response, 'tags'),
                'http_status' => $response->status(),
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Exception tags MCP', [
                'message' => $e->getMessage(),
                'url' => $this->mcpUrl
            ]);

            return $this->jsonResponse([
                'success' => false,
                'error' => 'Erreur lors de la récupération des tags',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Méthode utilitaire pour parser les réponses JSON
     */
    private function parseJsonResponse($response, $context = '')
    {
        try {
            return $response->json();
        } catch (\Exception $e) {
            Log::warning("Erreur lors du parsing JSON pour $context", [
                'error' => $e->getMessage(),
                'body' => $response->body()
            ]);
            return ['error' => 'Invalid JSON response'];
        }
    }

    /**
     * Méthode utilitaire pour les réponses JSON standardisées
     */
    private function jsonResponse(array $data, int $statusCode = 200)
    {
        return response()->json($data, $statusCode)
            ->header('Content-Type', self::CONTENT_TYPE_JSON)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Obtenir les informations de configuration MCP et Ollama
     */
    public function getMcpInfo()
    {
        return response()->json([
            'mcp_config' => [
                'server_url' => $this->mcpUrl,
                'timeout' => $this->timeout,
                'endpoints' => [
                    'health' => "{$this->mcpUrl}" . self::MCP_HEALTH_ENDPOINT,
                    'tags' => "{$this->mcpUrl}" . self::MCP_TAGS_ENDPOINT
                ]
            ],
            'ollama_config' => [
                'server_url' => Config::get('ollama.base_url', self::DEFAULT_OLLAMA_URL),
                'timeout' => Config::get('ollama.timeout', 120),
                'default_model' => 'gemma3:4b',
                'endpoints' => [
                    'generate' => Config::get('ollama.base_url', self::DEFAULT_OLLAMA_URL) . '/api/generate',
                    'models' => Config::get('ollama.base_url', self::DEFAULT_OLLAMA_URL) . '/api/tags',
                    'health' => Config::get('ollama.base_url', self::DEFAULT_OLLAMA_URL) . '/api/tags'
                ],
                'default_options' => Config::get('ollama.default_options', [])
            ],
            'proxy_info' => [
                'controller' => 'McpProxyController',
                'version' => '2.3.0',
                'purpose' => 'Proxy between Shelve, MCP Server and Ollama for AI reformulation',
                'features' => [
                    'mcp_health_check',
                    'ollama_health_check',
                    'mcp_tags_retrieval',
                    'ollama_direct_integration',
                    'ai_title_reformulation'
                ],
                'available_endpoints' => [
                    '/mcp/status' => 'Check MCP server status',
                    '/ollama/status' => 'Check Ollama server status',
                    '/mcp/tags' => 'Get MCP tags',
                    '/mcp/reformulate' => 'Reformulate record title with AI',
                    '/mcp/info' => 'Get configuration info'
                ]
            ]
        ]);
    }





    public function reformulateRecord(Request $request)
    {
        // Log pour vérifier l'authentification
        Log::info('reformulateRecord called', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'Anonymous',
            'is_authenticated' => Auth::check()
        ]);

        try {
            $recordId = $request->input('record_id') ?? $request->input('id');

            if (!$recordId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID d\'enregistrement manquant'
                ], 400);
            }

            $record = \App\Models\Record::with(['level', 'authors', 'children.authors'])->find($recordId);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enregistrement non trouvé'
                ], 404);
            }

            // Configuration Ollama
            $ollamaUrl = Config::get('ollama.base_url', self::DEFAULT_OLLAMA_URL);
            $ollamaTimeout = Config::get('ollama.timeout', 120);
            $ollamaModel = $request->input('model', 'gemma3:4b'); // Modèle par défaut

            // Créer un prompt descriptif pour l'IA
            $prompt = $this->buildReformulationPrompt($record);

            // Préparer les données pour Ollama
            $ollamaData = [
                'model' => $ollamaModel,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => Config::get('ollama.default_options.temperature', 0.7),
                    'top_p' => Config::get('ollama.default_options.top_p', 0.9),
                    'top_k' => Config::get('ollama.default_options.top_k', 40),
                    'repeat_penalty' => Config::get('ollama.default_options.repeat_penalty', 1.1),
                ]
            ];

            Log::info('Envoi du prompt de reformulation à Ollama', [
                'record_id' => $record->id,
                'original_title' => $record->name,
                'model' => $ollamaModel,
                'prompt_length' => strlen($prompt),
                'ollama_url' => $ollamaUrl
            ]);

            // Appel direct à Ollama
            $response = Http::timeout($ollamaTimeout)
                ->withHeaders([
                    'Content-Type' => self::CONTENT_TYPE_JSON,
                    'Accept' => self::CONTENT_TYPE_JSON
                ])
                ->post("{$ollamaUrl}/api/generate", $ollamaData);

            if ($response->successful()) {
                $ollamaResponse = $response->json();
                $reformulatedTitle = trim($ollamaResponse['response'] ?? '');

                // Nettoyer la réponse (enlever les markdown, guillemets, etc.)
                $reformulatedTitle = $this->cleanAiResponse($reformulatedTitle);

                // Extraire l'ID et le titre reformulé de la réponse
                $reformulatedData = [
                    'record_id' => $record->id,
                    'original_title' => $record->name,
                    'reformulated_title' => $reformulatedTitle,
                    'model_used' => $ollamaModel,
                    'generation_time' => $ollamaResponse['total_duration'] ?? null,
                    'tokens_evaluated' => $ollamaResponse['eval_count'] ?? null,
                    'timestamp' => now()->toISOString()
                ];

                Log::info('Reformulation Ollama réussie', [
                    'record_id' => $record->id,
                    'reformulated_title' => $reformulatedTitle,
                    'model' => $ollamaModel,
                    'generation_time' => $ollamaResponse['total_duration'] ?? 'N/A'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Reformulation réussie via Ollama',
                    'data' => $reformulatedData
                ]);
            } else {
                Log::error('Erreur Ollama reformulation', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'record_id' => $recordId,
                    'model' => $ollamaModel
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur du serveur Ollama',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de la reformulation Ollama', [
                'message' => $e->getMessage(),
                'record_id' => $request->input('record_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur interne du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Construire un prompt descriptif pour la reformulation par l'IA
     */
    private function buildReformulationPrompt(\App\Models\Record $record): string
    {
        $prompt = "Veuillez reformuler le titre suivant en français de manière plus claire et descriptive :\n\n";
        $prompt .= "**Titre actuel :** {$record->name}\n\n";
        $prompt .= "**Contexte de l'enregistrement :**\n";

        $prompt .= $this->addLevelToPrompt($record);
        $prompt .= $this->addDateToPrompt($record);
        $prompt .= $this->addAuthorsToPrompt($record);
        $prompt .= $this->addContentToPrompt($record);
        $prompt .= $this->addBiographicalHistoryToPrompt($record);
        $prompt .= $this->addChildrenToPrompt($record);

        $prompt .= $this->getReformulationInstructions();

        return $prompt;
    }

    private function addLevelToPrompt(\App\Models\Record $record): string
    {
        return $record->level ? "- Niveau : {$record->level->name}\n" : '';
    }

    private function addDateToPrompt(\App\Models\Record $record): string
    {
        if ($record->date_exact) {
            return "- Date : {$record->date_exact}\n";
        }

        if ($record->date_start || $record->date_end) {
            $dateRange = trim(($record->date_start ?? '') . ' - ' . ($record->date_end ?? ''), ' -');
            return $dateRange !== '-' ? "- Période : {$dateRange}\n" : '';
        }

        return '';
    }

    private function addAuthorsToPrompt(\App\Models\Record $record): string
    {
        if ($record->authors && $record->authors->count() > 0) {
            $authors = $record->authors->pluck('name')->join(', ');
            return "- Auteur(s) : {$authors}\n";
        }
        return '';
    }

    private function addContentToPrompt(\App\Models\Record $record): string
    {
        if (!$record->content || strlen(trim($record->content)) === 0) {
            return '';
        }

        $content = substr(strip_tags($record->content), 0, 300);
        if (strlen($record->content) > 300) {
            $content .= '...';
        }
        return "- Contenu : {$content}\n";
    }

    private function addBiographicalHistoryToPrompt(\App\Models\Record $record): string
    {
        if (!$record->biographical_history || strlen(trim($record->biographical_history)) === 0) {
            return '';
        }

        $history = substr(strip_tags($record->biographical_history), 0, 200);
        if (strlen($record->biographical_history) > 200) {
            $history .= '...';
        }
        return "- Historique biographique : {$history}\n";
    }

    private function addChildrenToPrompt(\App\Models\Record $record): string
    {
        if (!$record->children || $record->children->count() === 0) {
            return '';
        }

        $prompt = "- Nombre d'enfants : {$record->children->count()}\n";
        $childTitles = $record->children->take(3)->pluck('name')->join(', ');
        $prompt .= "- Exemples d'enfants : {$childTitles}\n";

        return $prompt;
    }

    private function getReformulationInstructions(): string
    {
        return "\n**Instructions :**\n" .
               "- Proposez un titre plus descriptif et informatif\n" .
               "- Conservez l'essence du document\n" .
               "- Utilisez un français clair et professionnel\n" .
               "- Limitez-vous à 100 caractères maximum\n" .
               "- Retournez uniquement le titre reformulé sans explication supplémentaire\n";
    }

    /**
     * Nettoyer la réponse de l'IA (enlever markdown, guillemets, etc.)
     */
    private function cleanAiResponse(string $response): string
    {
        // Enlever les caractères de formatage markdown
        $cleaned = preg_replace('/[*_`#]+/', '', $response);

        // Enlever les guillemets en début et fin
        $cleaned = trim($cleaned, '"\'');

        // Enlever les retours à la ligne multiples
        $cleaned = preg_replace('/\n+/', ' ', $cleaned);

        // Enlever les espaces multiples
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        // Nettoyer les expressions courantes de l'IA
        $patterns = [
            '/^(Titre reformulé|Nouveau titre|Proposition|Voici le titre reformulé)\s*:\s*/i',
            '/^(Le titre reformulé serait|Je propose)\s*:\s*/i',
            '/\.$/' // Point final
        ];

        foreach ($patterns as $pattern) {
            $cleaned = preg_replace($pattern, '', $cleaned);
        }

        return trim($cleaned);
    }




}
