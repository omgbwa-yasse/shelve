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

    public function __construct()
    {
        $this->mcpUrl = Config::get('mcp.base_url', 'http://localhost:3001');
        $this->timeout = Config::get('mcp.timeout', 120);
    }

    /**
     * Reformuler un enregistrement via le serveur MCP
     */
    public function reformulateRecord(Request $request)
    {
        try {
            // Récupérer l'ID depuis 'record_id' ou 'id'
            $recordId = $request->input('record_id') ?? $request->input('id');

            Log::info('Début de la reformulation d\'enregistrement', [
                'record_id' => $recordId,
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
                'mcp_url' => $this->mcpUrl
            ]);

            // Validation des données
            if (!$recordId) {
                Log::warning('ID d\'enregistrement manquant', [
                    'request_data' => $request->all()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'ID d\'enregistrement requis'
                ], 400);
            }

            // Construction de l'URL de l'endpoint MCP
            $mcpEndpoint = "{$this->mcpUrl}/api/records/reformulate";

            Log::info('Appel au serveur MCP', [
                'endpoint' => $mcpEndpoint,
                'record_id' => $recordId,
                'timeout' => $this->timeout
            ]);

            // Appel HTTP au serveur MCP
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($mcpEndpoint, [
                    'record_id' => $recordId,
                    'user_id' => Auth::id(),
                    'timestamp' => now()->toISOString(),
                    // Ajouter les données du record
                    'name' => $request->input('name'),
                    'content' => $request->input('content'),
                    'date' => $request->input('date'),
                    'author' => $request->input('author')
                ]);

            // Log de la réponse
            Log::info('Réponse du serveur MCP', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'headers' => $response->headers()
            ]);

            // Vérifier si la requête a réussi
            if ($response->successful()) {
                $data = $response->json();

                Log::info('Reformulation réussie', [
                    'record_id' => $recordId,
                    'response_data' => $data
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'message' => 'Reformulation terminée avec succès'
                ]);
            } else {
                Log::error('Erreur HTTP du serveur MCP', [
                    'status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'record_id' => $recordId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur du serveur MCP',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ], $response->status());
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erreur de connexion au serveur MCP', [
                'error' => $e->getMessage(),
                'mcp_url' => $this->mcpUrl,
                'timeout' => $this->timeout,
                'record_id' => $request->input('record_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de contacter le serveur MCP',
                'error' => 'Connexion timeout ou serveur inaccessible'
            ], 503);

        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Erreur de requête HTTP vers MCP', [
                'error' => $e->getMessage(),
                'response' => $e->response ? $e->response->body() : null,
                'status_code' => $e->response ? $e->response->status() : null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la communication avec le serveur MCP',
                'error' => $e->getMessage()
            ], 502);

        } catch (\Exception $e) {
            Log::error('Erreur inattendue lors de la reformulation', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e)
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur inattendue lors de la reformulation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifier l'état de la connexion au serveur MCP
     */
    public function checkMcpStatus()
    {
        try {
            $healthEndpoint = "{$this->mcpUrl}/api/health";

            Log::info('Vérification de l\'état du serveur MCP', [
                'endpoint' => $healthEndpoint
            ]);

            $response = Http::timeout(10)->get($healthEndpoint);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Serveur MCP accessible', [
                    'status' => $data['status'] ?? 'unknown',
                    'response' => $data
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'connected',
                    'mcp_response' => $data
                ]);
            } else {
                Log::warning('Serveur MCP inaccessible', [
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'status' => 'disconnected',
                    'error' => 'Serveur MCP non accessible'
                ], $response->status());
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du statut MCP', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
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
