<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class McpProxyController extends Controller
{
    protected $baseUrl;
    protected $apiToken;
    protected $timeout;
    protected $defaultModel;

    public function __construct()
    {
        $this->baseUrl = Config::get('mcp.base_url');
        $this->apiToken = Config::get('mcp.api_token');
        $this->timeout = Config::get('mcp.timeout');
        $this->defaultModel = Config::get('mcp.default_model');

        // Journaliser les informations de configuration au démarrage (sans exposer le token)
        Log::info('McpProxyController initialisé avec la configuration:', [
            'baseUrl' => $this->baseUrl,
            'timeout' => $this->timeout,
            'defaultModel' => $this->defaultModel,
            'hasApiToken' => !empty($this->apiToken)
        ]);
    }

    /**
     * Vérifier si la configuration MCP est valide
     *
     * @return array
     */
    private function checkConfiguration()
    {
        $issues = [];

        if (empty($this->baseUrl)) {
            $issues[] = 'URL de base MCP manquante (MCP_BASE_URL)';
        } else if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            $issues[] = 'URL de base MCP invalide: ' . $this->baseUrl;
        }

        if (empty($this->apiToken)) {
            $issues[] = 'Token API MCP manquant (MCP_API_TOKEN)';
        }

        if (empty($this->defaultModel)) {
            $issues[] = 'Modèle par défaut manquant (MCP_DEFAULT_MODEL)';
        }

        return $issues;
    }

    /**
     * Reformulate a record title using AI
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reformulateTitle(Request $request)
    {
        try {
            // Vérifier la configuration MCP
            $configIssues = $this->checkConfiguration();
            if (!empty($configIssues)) {
                Log::error('Problèmes de configuration MCP détectés', ['issues' => $configIssues]);
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration MCP invalide: ' . implode(', ', $configIssues),
                ], 500);
            }

            // Journaliser la requête entrante pour le débogage
            Log::info('Demande de reformulation de titre reçue', [
                'request_data' => $request->all()
            ]);

            $validated = $request->validate([
                'title' => 'required|string',
                'recordId' => 'required|integer',
                'model' => 'nullable|string',
            ]);

            $title = $validated['title'];
            $model = $validated['model'] ?? $this->defaultModel;

            // Journaliser les paramètres validés
            Log::info('Paramètres validés pour la reformulation', [
                'title' => $title,
                'model' => $model,
                'baseUrl' => $this->baseUrl
            ]);

            $prompt = "Reformule le titre suivant pour qu'il soit plus précis et descriptif, tout en restant concis. " .
                    "Conserve les termes techniques essentiels. " .
                    "Titre original: \"$title\"";

            // Vérification de la configuration avant l'appel API
            if (empty($this->baseUrl)) {
                Log::error('MCP URL manquante dans la configuration');
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration MCP incomplète: URL manquante',
                ], 500);
            }

            // Préparer les données pour la requête API
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un assistant spécialisé dans l\'archivage et la documentation. Tu reformules les titres de documents pour les rendre plus précis et descriptifs.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 150,
            ];

            Log::info('Envoi de requête à l\'API MCP', [
                'url' => "{$this->baseUrl}/v1/chat/completions",
                'payload' => $payload
            ]);

            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->apiToken}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$this->baseUrl}/v1/chat/completions", $payload);
            } catch (\Exception $httpException) {
                Log::error('Erreur de connexion à l\'API MCP', [
                    'message' => $httpException->getMessage(),
                    'trace' => $httpException->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion au service MCP',
                    'error' => $httpException->getMessage()
                ], 500);
            }

            if ($response->successful()) {
                $result = $response->json();
                Log::info('Réponse API réussie', ['response' => $result]);

                // Vérifier si la structure de la réponse est correcte
                if (!isset($result['choices']) ||
                    !is_array($result['choices']) ||
                    empty($result['choices']) ||
                    !isset($result['choices'][0]['message']['content'])) {

                    Log::error('Structure de réponse API invalide', ['response' => $result]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Format de réponse de l\'API invalide',
                        'response' => $result
                    ], 500);
                }

                $reformulatedTitle = $result['choices'][0]['message']['content'];

                // Nettoyage du titre reformulé
                $reformulatedTitle = trim($reformulatedTitle);
                $reformulatedTitle = preg_replace('/(^["\']+)|(["\']+$)/', '', $reformulatedTitle);
                $reformulatedTitle = preg_replace('/^Titre reformulé\s*:\s*/i', '', $reformulatedTitle);

                Log::info('Titre reformulé avec succès', [
                    'original' => $title,
                    'reformulated' => $reformulatedTitle
                ]);

                return response()->json([
                    'success' => true,
                    'original' => $title,
                    'reformulated' => $reformulatedTitle,
                ]);
            } else {
                $statusCode = $response->status();
                $responseBody = $response->body();

                try {
                    $jsonResponse = $response->json();
                } catch (\Exception $e) {
                    $jsonResponse = null;
                }

                Log::error('Échec de la requête API MCP', [
                    'status' => $statusCode,
                    'body' => $responseBody,
                    'json' => $jsonResponse
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la communication avec le service d\'IA (code: ' . $statusCode . ')',
                    'error' => $jsonResponse ?? $responseBody
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $ve) {
            // Erreur de validation des données d'entrée
            Log::warning('Erreur de validation pour la reformulation de titre', [
                'errors' => $ve->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Les données fournies sont invalides',
                'errors' => $ve->errors(),
            ], 422); // 422 Unprocessable Entity

        } catch (\Exception $e) {
            // Erreur générale non spécifique
            Log::error('Erreur générale lors de la reformulation du titre', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e)
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur inattendue est survenue lors de la reformulation du titre',
                'error' => $e->getMessage(),
            ], 500);
        }

    /**
     * Vérifier l'état de la connexion MCP
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus()
    {
        try {
            $configIssues = $this->checkConfiguration();
            $status = [
                'config_valid' => empty($configIssues),
                'config_issues' => $configIssues,
                'server_reachable' => false,
                'models' => []
            ];

            // Si la configuration est valide, tenter de se connecter
            if (empty($configIssues)) {
                try {
                    // Essayer d'obtenir les modèles disponibles ou une autre info simple
                    $response = Http::timeout($this->timeout / 2)
                        ->withHeaders([
                            'Authorization' => "Bearer {$this->apiToken}",
                            'Content-Type' => 'application/json',
                        ])
                        ->get("{$this->baseUrl}/v1/models");

                    if ($response->successful()) {
                        $result = $response->json();
                        $status['server_reachable'] = true;
                        $status['models'] = $result['data'] ?? [];
                        $status['api_version'] = $result['api_version'] ?? 'inconnu';
                    } else {
                        $status['error_code'] = $response->status();
                        $status['error_response'] = $response->body();
                    }
                } catch (\Exception $e) {
                    $status['connection_error'] = $e->getMessage();
                }
            }

            return response()->json([
                'success' => $status['server_reachable'],
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du statut MCP', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut: ' . $e->getMessage()
            ], 500);
        }
    }
    }
}
