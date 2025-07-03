<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class McpProxyController extends Controller
{
    protected $mcpBaseUrl;

    public function __construct()
    {
        // Get the MCP server URL from config (add to config/services.php)
        $this->mcpBaseUrl = config('services.mcp.url', 'http://localhost:3000');
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
            $response = Http::post("{$this->mcpBaseUrl}/api/enrich/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Enrichment Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to MCP server'], 500);
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
            $response = Http::post("{$this->mcpBaseUrl}/api/extract-keywords/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Extract Keywords Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to MCP server'], 500);
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
            $response = Http::post("{$this->mcpBaseUrl}/api/assign-terms/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Assign Terms Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to MCP server'], 500);
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
            $response = Http::post("{$this->mcpBaseUrl}/api/validate/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Validation Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to MCP server'], 500);
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
            $response = Http::post("{$this->mcpBaseUrl}/api/classify/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Classification Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to MCP server'], 500);
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
            $response = Http::post("{$this->mcpBaseUrl}/api/report/{$id}");
            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('MCP Report Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to connect to MCP server'], 500);
        }
    }
}
