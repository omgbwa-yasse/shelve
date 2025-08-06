<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class McpProxyController extends Controller
{
    protected $mcpUrl;
    protected $timeout;

    public function __construct()
    {
        $this->mcpUrl = Config::get('mcp.base_url', 'http://localhost:3001');
        $this->timeout = Config::get('mcp.timeout', 30);

        Log::info('McpProxyController initialisé pour Shelve', [
            'mcpUrl' => $this->mcpUrl,
            'timeout' => $this->timeout
        ]);
    }

    /**
     * Reformuler un titre d'enregistrement d'archive via le serveur MCP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reformulateRecord(Request $request)
    {
        try {
            Log::info('Demande de reformulation d\'enregistrement reçue', [
                'request_data' => $request->all()
            ]);

            // Validation des données d'entrée selon le format MCP
            $validated = $request->validate([
                'id' => 'required|string',
                'name' => 'required|string|max:500',
                'date' => 'nullable|string',
                'content' => 'nullable|string|max:10000',
                'author' => 'nullable|array',
                'author.name' => 'nullable|string',
                'children' => 'nullable|array',
                'children.*.name' => 'nullable|string',
                'children.*.date' => 'nullable|string',
                'children.*.content' => 'nullable|string'
            ]);

            Log::info('Données validées pour MCP', [
                'id' => $validated['id'],
                'name' => $validated['name']
            ]);

            // Préparer les données pour le serveur MCP
            $mcpPayload = [
                'id' => $validated['id'],
                'name' => $validated['name'],
                'date' => $validated['date'] ?? null,
                'content' => $validated['content'] ?? null,
                'author' => isset($validated['author']) ? $validated['author'] : null,
                'children' => $validated['children'] ?? []
            ];

            // Appel au serveur MCP
            $mcpEndpoint = "{$this->mcpUrl}/api/records/reformulate";

            Log::info('Envoi vers le serveur MCP', [
                'endpoint' => $mcpEndpoint,
                'payload' => $mcpPayload
            ]);

            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])
                    ->post($mcpEndpoint, $mcpPayload);

            } catch (\Exception $httpException) {
                Log::error('Erreur de connexion au serveur MCP', [
                    'endpoint' => $mcpEndpoint,
                    'message' => $httpException->getMessage(),
                    'trace' => $httpException->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de contacter le serveur MCP',
                    'error' => $httpException->getMessage()
                ], 500);
            }

            if ($response->successful()) {
                $result = $response->json();

                Log::info('Reformulation MCP réussie', [
                    'original_name' => $validated['name'],
                    'reformulated_name' => $result['new_name'] ?? 'non fourni',
                    'response' => $result
                ]);

                // Vérifier le format de réponse MCP
                if (!isset($result['id']) || !isset($result['new_name'])) {
                    Log::error('Format de réponse MCP invalide', ['response' => $result]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Format de réponse du serveur MCP invalide',
                        'response' => $result
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $result['id'],
                        'original_name' => $validated['name'],
                        'new_name' => $result['new_name'],
                        'reformulated_by' => 'MCP Shelve Server'
                    ]
                ]);

            } else {
                $statusCode = $response->status();
                $responseBody = $response->body();

                try {
                    $jsonResponse = $response->json();
                } catch (\Exception $e) {
                    $jsonResponse = null;
                }

                Log::error('Échec de la requête vers le serveur MCP', [
                    'status' => $statusCode,
                    'body' => $responseBody,
                    'json' => $jsonResponse,
                    'endpoint' => $mcpEndpoint
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Erreur du serveur MCP (code: {$statusCode})",
                    'error' => $jsonResponse ?? $responseBody
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $ve) {
            Log::warning('Erreur de validation des données', [
                'errors' => $ve->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $ve->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Erreur générale lors de la reformulation', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e)
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur inattendue lors de la reformulation',
                'error' => $e->getMessage(),
            ], 500);
        }
    /**
     * Vérifier l'état de la connexion au serveur MCP
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMcpStatus()
    {
        try {
            $healthEndpoint = "{$this->mcpUrl}/api/health";

            Log::info('Vérification de l\'état du serveur MCP', [
                'endpoint' => $healthEndpoint
            ]);

            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Accept' => 'application/json'
                    ])
                    ->get($healthEndpoint);

                if ($response->successful()) {
                    $result = $response->json();

                    return response()->json([
                        'success' => true,
                        'mcp_status' => 'healthy',
                        'mcp_response' => $result,
                        'connection_test' => 'successful'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'mcp_status' => 'unhealthy',
                        'http_code' => $response->status(),
                        'connection_test' => 'failed'
                    ], 503);
                }

            } catch (\Exception $httpException) {
                Log::error('Impossible de contacter le serveur MCP', [
                    'endpoint' => $healthEndpoint,
                    'error' => $httpException->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'mcp_status' => 'unreachable',
                    'error' => $httpException->getMessage(),
                    'connection_test' => 'failed'
                ], 503);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du statut MCP', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les informations de configuration MCP (pour debug)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMcpInfo()
    {
        return response()->json([
            'mcp_config' => [
                'server_url' => $this->mcpUrl,
                'timeout' => $this->timeout,
                'endpoints' => [
                    'reformulate' => "{$this->mcpUrl}/api/records/reformulate",
                    'health' => "{$this->mcpUrl}/api/health"
                ]
            ],
            'proxy_info' => [
                'controller' => 'McpProxyController',
                'version' => '2.0.0',
                'purpose' => 'Proxy between Shelve and MCP Server'
            ]
        ]);
    }
}
