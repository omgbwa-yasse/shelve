<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Record;
use App\Http\Controllers\Controller;

class McpProxyController extends Controller
{
    protected $mcpBaseUrl;

    public function __construct()
    {
        // Récupérer l'URL de base du serveur MCP depuis la configuration
        $this->mcpBaseUrl = rtrim(config('mcp.base_url', 'http://localhost:3001'), '/') . '/api';
    }

    /**
     * Génère un résumé pour un enregistrement spécifique
     */
    public function summarizeRecord(Request $request, $recordId)
    {
        try {
            $record = Record::findOrFail($recordId);

            $response = Http::post("{$this->mcpBaseUrl}/records/{$record->id}/summarize", []);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la génération du résumé',
                'details' => $response->body()
            ], 500);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la génération du résumé pour le record {$recordId}: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reformule le titre d'un enregistrement
     */
    public function reformatTitle(Request $request, $recordId)
    {
        try {
            $record = Record::findOrFail($recordId);

            $response = Http::post("{$this->mcpBaseUrl}/records/{$record->id}/reformat-title", []);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la reformulation du titre',
                'details' => $response->body()
            ], 500);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la reformulation du titre pour le record {$recordId}: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extrait les mots-clés d'un enregistrement
     */
    public function extractKeywords(Request $request, $recordId)
    {
        try {
            $record = Record::findOrFail($recordId);

            $response = Http::post("{$this->mcpBaseUrl}/records/{$record->id}/extract-keywords", []);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de l\'extraction des mots-clés',
                'details' => $response->body()
            ], 500);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'extraction des mots-clés pour le record {$recordId}: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyse le contenu d'un enregistrement
     */
    public function analyzeContent(Request $request, $recordId)
    {
        try {
            $record = Record::findOrFail($recordId);

            $response = Http::post("{$this->mcpBaseUrl}/records/{$record->id}/analyze", []);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de l\'analyse du contenu',
                'details' => $response->body()
            ], 500);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'analyse du contenu pour le record {$recordId}: " . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
