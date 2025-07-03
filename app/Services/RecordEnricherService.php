<?php

namespace App\Services;

use App\Models\Record;
use App\Models\AiInteraction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RecordEnricherService
{
    protected string $mcpBaseUrl;
    protected int $timeout;
    protected OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->mcpBaseUrl = env('MCP_BASE_URL', 'http://localhost:3000');
        $this->timeout = config('ollama.timeout', 120);
        $this->ollamaService = $ollamaService;
    }

    /**
     * Vérifier la santé du serveur MCP
     *
     * @return array
     */
    public function checkHealth(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->mcpBaseUrl}/health");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('status'),
                    'timestamp' => $response->json('timestamp')
                ];
            }

            return [
                'success' => false,
                'error' => "Erreur lors de la vérification de la santé du serveur MCP: " . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('MCP health check error: ' . $e->getMessage(), [
                'base_url' => $this->mcpBaseUrl,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier la connexion à Ollama via le serveur MCP
     *
     * @return array
     */
    public function checkOllama(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->mcpBaseUrl}/api/check-ollama");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('status'),
                    'models' => $response->json('models'),
                    'count' => $response->json('count')
                ];
            }

            return [
                'success' => false,
                'error' => "Erreur lors de la vérification d'Ollama via MCP: " . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('MCP Ollama check error: ' . $e->getMessage(), [
                'base_url' => $this->mcpBaseUrl,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Enrichir la description d'un enregistrement
     *
     * @param Record $record L'enregistrement à enrichir
     * @param string $modelName Le nom du modèle Ollama à utiliser
     * @param string $mode Le mode d'enrichissement (enrich, summarize, analyze)
     * @param int|null $userId L'ID de l'utilisateur qui effectue l'enrichissement
     * @return array
     */
    public function enrichRecord(Record $record, string $modelName = 'llama3', string $mode = 'enrich', ?int $userId = null): array
    {
        try {
            // Préparation des données du record pour l'API
            $recordData = [
                'id' => $record->id,
                'code' => $record->code,
                'name' => $record->name,
                'content' => $record->content,
                'biographical_history' => $record->biographical_history,
                'archival_history' => $record->archival_history,
                'note' => $record->note,
                'date_start' => $record->date_start,
                'date_end' => $record->date_end,
            ];

            // Appel à l'API MCP
            $response = Http::timeout($this->timeout)
                ->post("{$this->mcpBaseUrl}/api/enrich", [
                    'recordId' => $record->id,
                    'recordData' => $recordData,
                    'modelName' => $modelName,
                    'mode' => $mode
                ]);

            if ($response->successful()) {
                $result = $response->json();

                // Création d'une interaction AI pour le suivi
                if ($userId) {
                    // Trouver le modèle AI correspondant
                    $aiModels = $this->ollamaService->getAvailableModels();
                    $aiModelId = null;

                    foreach ($aiModels as $model) {
                        if ($model['name'] === $modelName) {
                            $aiModelId = $model['id'] ?? null;
                            break;
                        }
                    }

                    if ($aiModelId) {
                        // Créer une interaction
                        AiInteraction::create([
                            'user_id' => $userId,
                            'ai_model_id' => $aiModelId,
                            'input' => json_encode($recordData),
                            'output' => $result['enrichedContent'],
                            'parameters' => json_encode([
                                'mode' => $mode,
                                'record_id' => $record->id,
                                'stats' => $result['stats'] ?? null
                            ]),
                            'module_type' => 'record',
                            'module_id' => $record->id,
                            'status' => 'completed'
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'enrichedContent' => $result['enrichedContent'],
                    'mode' => $result['mode'],
                    'model' => $result['model'],
                    'stats' => $result['stats'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => "Erreur lors de l'enrichissement via MCP: " . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('MCP enrichRecord error: ' . $e->getMessage(), [
                'record_id' => $record->id,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formater un titre au format objet:action(typologie)
     *
     * @param string $title Le titre à formater
     * @param string $modelName Le nom du modèle à utiliser
     * @param int|null $userId L'ID de l'utilisateur qui effectue l'opération
     * @return array
     */
    public function formatTitle(string $title, string $modelName = 'llama3', ?int $userId = null): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->mcpBaseUrl}/api/format-title", [
                    'title' => $title,
                    'modelName' => $modelName
                ]);

            if ($response->successful()) {
                $result = $response->json();

                // Création d'une interaction AI pour le suivi
                if ($userId && isset($result['success']) && $result['success']) {
                    $this->logAiInteraction(
                        $userId,
                        $modelName,
                        $title,
                        $result['formattedTitle'],
                        'format_title',
                        null
                    );
                }

                return $result;
            }

            return [
                'success' => false,
                'error' => "Erreur lors du formatage du titre via MCP: " . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('MCP formatTitle error: ' . $e->getMessage(), [
                'title' => $title,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Rechercher des termes dans le thésaurus à partir du contenu d'un record
     *
     * @param Record $record L'enregistrement à analyser
     * @param string $modelName Le nom du modèle à utiliser
     * @param int $maxTerms Nombre maximum de termes à extraire
     * @param int|null $userId L'ID de l'utilisateur qui effectue l'opération
     * @return array
     */
    public function extractKeywords(Record $record, string $modelName = 'llama3', int $maxTerms = 5, ?int $userId = null): array
    {
        try {
            // Concaténer toutes les informations disponibles pour une meilleure extraction
            $contentToAnalyze = [
                $record->name,
                $record->content,
                $record->biographical_history,
                $record->archival_history,
                $record->note
            ];

            // Filtrer les valeurs null et vides puis joindre
            $contentToAnalyze = array_filter($contentToAnalyze, function($value) {
                return !is_null($value) && trim($value) !== '';
            });

            $content = implode("\n\n", $contentToAnalyze);

            // Appel à l'API MCP
            $response = Http::timeout($this->timeout)
                ->post("{$this->mcpBaseUrl}/api/thesaurus-search", [
                    'recordId' => $record->id,
                    'content' => $content,
                    'modelName' => $modelName,
                    'maxTerms' => $maxTerms
                ]);

            if ($response->successful()) {
                $result = $response->json();

                // Création d'une interaction AI pour le suivi
                if ($userId && isset($result['success']) && $result['success']) {
                    $this->logAiInteraction(
                        $userId,
                        $modelName,
                        $content,
                        json_encode([
                            'extractedKeywords' => $result['extractedKeywords'],
                            'matchedTerms' => $result['matchedTerms']
                        ]),
                        'extract_keywords',
                        $record->id
                    );
                }

                return $result;
            }

            return [
                'success' => false,
                'error' => "Erreur lors de l'extraction des mots-clés via MCP: " . $response->body()
            ];
        } catch (Exception $e) {
            Log::error('MCP extractKeywords error: ' . $e->getMessage(), [
                'record_id' => $record->id,
                'exception' => $e
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Utilitaire pour enregistrer les interactions AI
     */
    private function logAiInteraction(int $userId, string $modelName, string $input, string $output, string $mode, ?int $recordId = null): void
    {
        // Trouver le modèle AI correspondant
        $aiModels = $this->ollamaService->getAvailableModels();
        $aiModelId = null;

        foreach ($aiModels as $model) {
            if ($model['name'] === $modelName) {
                $aiModelId = $model['id'] ?? null;
                break;
            }
        }

        if ($aiModelId) {
            // Créer une interaction
            AiInteraction::create([
                'user_id' => $userId,
                'ai_model_id' => $aiModelId,
                'input' => $input,
                'output' => $output,
                'parameters' => json_encode([
                    'mode' => $mode,
                    'record_id' => $recordId
                ]),
                'module_type' => $recordId ? 'record' : 'general',
                'module_id' => $recordId,
                'status' => 'completed'
            ]);
        }
    }
}
