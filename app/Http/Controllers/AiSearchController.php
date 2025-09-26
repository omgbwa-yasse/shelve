<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AI\QueryAnalyzerService;
use App\Services\AI\QueryExecutorService;
use App\Services\AI\ResponseFormatterService;
use Illuminate\Support\Facades\Log;

class AiSearchController extends Controller
{
    private QueryAnalyzerService $analyzer;
    private QueryExecutorService $executor;
    private ResponseFormatterService $formatter;

    public function __construct(
        QueryAnalyzerService $analyzer,
        QueryExecutorService $executor,
        ResponseFormatterService $formatter
    ) {
        $this->analyzer = $analyzer;
        $this->executor = $executor;
        $this->formatter = $formatter;
    }

    public function index()
    {
        return view('ai-search.index');
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $searchType = $request->input('search_type', 'records');
        
        // Auto-détection du type de recherche basé sur le contenu
        $searchType = $this->detectSearchType($message, $searchType);

        if (empty($message)) {
            return response()->json([
                'success' => false,
                'error' => 'Message is required'
            ]);
        }

        try {
            // Étape 1: Claude analyse la requête et retourne des instructions JSON
            Log::info("Analyzing user query", ['query' => $message, 'type' => $searchType]);
            $instructions = $this->analyzer->analyzeQuery($message, $searchType);

            if (!$instructions['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $instructions['error']
                ]);
            }

            Log::info("Query analyzed", ['instructions' => $instructions]);

            // Étape 2: Laravel exécute les instructions
            $executionResult = $this->executor->executeQuery($instructions);

            if (!$executionResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $executionResult['error']
                ]);
            }

            Log::info("Query executed", ['result_count' => $executionResult['count'] ?? 0]);

            // Étape 3: Formatter la réponse pour l'utilisateur
            $formattedResponse = $this->formatter->formatResponse($executionResult, $searchType);

            Log::info("Response formatted", ['success' => $formattedResponse['success']]);

            return response()->json($formattedResponse);

        } catch (\Exception $e) {
            Log::error("AI Search Error", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur IA: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Détecte automatiquement le type de recherche basé sur le contenu de la requête
     */
    private function detectSearchType(string $message, string $defaultType): string
    {
        $messageLower = strtolower($message);
        
        // Détection des mots-clés pour les auteurs
        $authorKeywords = ['auteur', 'auteurs', 'écrivain', 'rédacteur'];
        foreach ($authorKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'authors';
            }
        }
        
        // Détection des mots-clés pour les mails
        $mailKeywords = ['mail', 'email', 'courrier', 'correspondance', 'message'];
        foreach ($mailKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'mails';
            }
        }
        
        // Détection des mots-clés pour les communications
        $commKeywords = ['communication', 'échange', 'dialogue'];
        foreach ($commKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'communications';
            }
        }
        
        // Détection des mots-clés pour les bordereaux/transferts
        $slipKeywords = ['bordereau', 'transfert', 'borderaux', 'slip', 'envoi'];
        foreach ($slipKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'slips';
            }
        }
        
        // Si aucun mot-clé spécifique trouvé, retourner le type par défaut
        return $defaultType;
    }
}