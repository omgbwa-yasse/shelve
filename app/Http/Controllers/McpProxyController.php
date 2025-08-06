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

    public function __construct()
    {
        // L'authentification est gérée au niveau des routes
        // Pas besoin de middleware ici
        $this->mcpUrl = Config::get('mcp.base_url', 'http://localhost:3001');
        $this->timeout = Config::get('mcp.timeout', 120);
    }

    /**
     * Reformuler un enregistrement via le serveur MCP
     */
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

            $record = \App\Models\Record::find($recordId);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enregistrement non trouvé'
                ], 404);
            }

            $mcpEndpoint = "{$this->mcpUrl}/api/records/reformulate";

            // Préparer les données de l'enregistrement
            $recordData = [
                'record_id' => $record->id,
                'name' => $record->name,
                'level' => $record->level->name ?? null,
                'content' => $record->content,
                'date' => $record->date_exact ?? ($record->date_start . ' - ' . $record->date_end),
                'author' => $record->authors->first()->name ?? null,
            ];

            // Ajouter les enfants s'ils existent
            if ($record->children && $record->children->count() > 0) {
                $recordData['children'] = [];
                foreach ($record->children as $child) {
                    $recordData['children'][] = [
                        'id' => $child->id,
                        'name' => $child->name,
                        'content' => $child->content,
                        'date' => $child->date_exact ?? ($child->date_start . ' - ' . $child->date_end),
                        'author' => $child->authors->first()->name ?? null,
                    ];
                }
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => self::CONTENT_TYPE_JSON,
                    'Accept' => self::CONTENT_TYPE_JSON
                ])
                ->post($mcpEndpoint, $recordData);

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'message' => 'Reformulation réussie',
                    'data' => $data
                ]);
            } else {
                Log::error('Erreur MCP reformulation', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'record_id' => $recordId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur du serveur MCP',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de la reformulation', [
                'message' => $e->getMessage(),
                'record_id' => $request->input('record_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur interne du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier l'état de la connexion au serveur MCP
     */
    public function checkMcpStatus()
    {
        Log::info('checkMcpStatus called', ['url' => $this->mcpUrl]);

        try {
            $healthEndpoint = $this->mcpUrl . self::MCP_HEALTH_ENDPOINT;
            $response = Http::timeout(10)->get($healthEndpoint);

            if (!$response->successful()) {
                return $this->jsonResponse([
                    'success' => false,
                    'status' => 'disconnected',
                    'error' => 'Serveur MCP non accessible',
                    'http_status' => $response->status()
                ], 503);
            }

            $result = [
                'success' => true,
                'status' => 'connected',
                'mcp_response' => $this->parseJsonResponse($response, 'health'),
                'http_status' => $response->status(),
                'timestamp' => now()->toISOString()
            ];

            if (config('app.debug')) {
                $result['mcp_url'] = $this->mcpUrl;
            }

            Log::info('MCP Status Success');
            return $this->jsonResponse($result);

        } catch (\Exception $e) {
            Log::error('Exception statut MCP', [
                'message' => $e->getMessage(),
                'url' => $this->mcpUrl
            ]);

            return $this->jsonResponse([
                'success' => false,
                'status' => 'disconnected',
                'error' => 'Erreur de connexion au serveur MCP',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
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
     * Obtenir les informations de configuration MCP
     */
    public function getMcpInfo()
    {
        return response()->json([
            'mcp_config' => [
                'server_url' => $this->mcpUrl,
                'timeout' => $this->timeout,
                'endpoints' => [
                    'reformulate' => "{$this->mcpUrl}/api/records/reformulate",
                    'health' => "{$this->mcpUrl}" . self::MCP_HEALTH_ENDPOINT,
                    'tags' => "{$this->mcpUrl}" . self::MCP_TAGS_ENDPOINT
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
