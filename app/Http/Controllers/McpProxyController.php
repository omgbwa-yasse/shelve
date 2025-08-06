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

            $recordId = $request->input('record_id') ?? $request->input('id');

            $mcpEndpoint = "{$this->mcpUrl}/api/records/reformulate";

            if (Http::get("{$this->mcpUrl}/api/health")) {
                dd("Operationnel");
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($mcpEndpoint, [
                    'record_id' => $recordId,
                    'user_id' => Auth::id(),
                    'timestamp' => now()->toISOString(),
                    'name' => $request->input('name'),
                    'content' => $request->input('content'),
                    'date' => $request->input('date'),
                    'author' => $request->input('author')
                ]);


            if ($response->successful()) {
                $data = $response->json();

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur du serveur MCP',
                    'error' => $response->body(),
                    'status_code' => $response->status()
                ], $response->status());
            }
    }





    /**
     * Vérifier l'état de la connexion au serveur MCP
     */


    public function checkMcpStatus()
    {

            $healthEndpoint = "{$this->mcpUrl}/api/health";

            $response = Http::timeout(10)->get($healthEndpoint);

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'status' => 'connected',
                    'mcp_response' => $data
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'status' => 'disconnected',
                    'error' => 'Serveur MCP non accessible'
                ], $response->status());
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
