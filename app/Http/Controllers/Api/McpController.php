<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Services\MCP\McpManagerService;
use App\Jobs\ProcessRecordWithMcp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class McpController extends Controller
{
    public function __construct(
        protected McpManagerService $mcpManager
    ) {}

    /**
     * Traiter un record avec les fonctionnalités MCP
     */
    public function processRecord(Request $request, Record $record): JsonResponse
    {
        $request->validate([
            'features' => 'array',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary']),
            'async' => 'boolean'
        ]);

        $features = $request->get('features', ['title', 'thesaurus', 'summary']);
        $async = $request->get('async', false);

        try {
            if ($async) {
                ProcessRecordWithMcp::dispatch($record, $features);
                
                return response()->json([
                    'message' => 'Traitement en cours en arrière-plan',
                    'record_id' => $record->id,
                    'features' => $features,
                    'status' => 'queued'
                ]);
            }

            $results = $this->mcpManager->processRecord($record, $features);

            return response()->json([
                'message' => 'Traitement réussi',
                'record_id' => $record->id,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Échec traitement MCP API', [
                'record_id' => $record->id,
                'features' => $features,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Échec du traitement MCP',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Prévisualiser les traitements MCP sans sauvegarder
     */
    public function previewProcessing(Request $request, Record $record): JsonResponse
    {
        $request->validate([
            'features' => 'array',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary'])
        ]);

        $features = $request->get('features', ['title', 'summary']);

        try {
            $previews = $this->mcpManager->previewProcessing($record, $features);

            return response()->json([
                'message' => 'Prévisualisation générée',
                'record_id' => $record->id,
                'previews' => $previews
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de la prévisualisation',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Traitement par lots
     */
    public function batchProcess(Request $request): JsonResponse
    {
        $request->validate([
            'record_ids' => 'required|array|max:100',
            'record_ids.*' => 'integer|exists:records,id',
            'features' => 'array',
            'features.*' => Rule::in(['title', 'thesaurus', 'summary']),
            'async' => 'boolean'
        ]);

        $recordIds = $request->get('record_ids');
        $features = $request->get('features', ['thesaurus']);
        $async = $request->get('async', true); // Par défaut asynchrone pour les lots

        try {
            if ($async) {
                foreach ($recordIds as $recordId) {
                    $record = Record::find($recordId);
                    if ($record) {
                        ProcessRecordWithMcp::dispatch($record, $features);
                    }
                }

                return response()->json([
                    'message' => 'Traitement par lots lancé',
                    'record_count' => count($recordIds),
                    'features' => $features,
                    'status' => 'queued'
                ]);
            }

            $results = $this->mcpManager->batchProcessRecords($recordIds, $features);

            return response()->json([
                'message' => 'Traitement par lots terminé',
                'summary' => [
                    'total_records' => count($recordIds),
                    'processed' => $results['processed'],
                    'errors' => $results['errors']
                ],
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec du traitement par lots',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reformulation de titre uniquement
     */
    public function reformulateTitle(Request $request, Record $record): JsonResponse
    {
        try {
            $titleService = app(\App\Services\MCP\McpTitleReformulationService::class);
            $originalTitle = $record->name;
            $newTitle = $titleService->reformulateTitle($record);

            return response()->json([
                'message' => 'Titre reformulé avec succès',
                'record_id' => $record->id,
                'original_title' => $originalTitle,
                'new_title' => $newTitle
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de la reformulation',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Aperçu de reformulation (sans sauvegarder)
     */
    public function previewTitleReformulation(Request $request, Record $record): JsonResponse
    {
        try {
            $titleService = app(\App\Services\MCP\McpTitleReformulationService::class);
            $preview = $titleService->previewTitleReformulation($record);

            return response()->json([
                'message' => 'Aperçu généré',
                'preview' => $preview
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de l\'aperçu',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Indexation thésaurus uniquement
     */
    public function indexWithThesaurus(Request $request, Record $record): JsonResponse
    {
        try {
            $thesaurusService = app(\App\Services\MCP\McpThesaurusIndexingService::class);
            $result = $thesaurusService->indexRecord($record);

            return response()->json([
                'message' => 'Indexation thésaurus réussie',
                'record_id' => $record->id,
                'indexing_result' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de l\'indexation',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Génération de résumé uniquement
     */
    public function generateSummary(Request $request, Record $record): JsonResponse
    {
        try {
            $summaryService = app(\App\Services\MCP\McpContentSummarizationService::class);
            $originalContent = $record->content;
            $newSummary = $summaryService->generateSummary($record);

            return response()->json([
                'message' => 'Résumé généré avec succès',
                'record_id' => $record->id,
                'original_content' => $originalContent,
                'new_summary' => $newSummary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de la génération de résumé',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }

    /**
     * Aperçu du résumé (sans sauvegarder)
     */
    public function previewSummary(Request $request, Record $record): JsonResponse
    {
        try {
            $summaryService = app(\App\Services\MCP\McpContentSummarizationService::class);
            $preview = $summaryService->previewSummary($record);

            return response()->json([
                'message' => 'Aperçu du résumé généré',
                'preview' => $preview
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de l\'aperçu du résumé',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statut des traitements en cours
     */
    public function getProcessingStatus(Record $record): JsonResponse
    {
        try {
            // Vérifier les jobs en queue pour ce record
            $pendingJobs = DB::table('jobs')
                ->where('payload', 'like', "%{$record->id}%")
                ->count();

            // Récupérer les concepts du thésaurus associés
            $thesaurusService = app(\App\Services\MCP\McpThesaurusIndexingService::class);
            $concepts = $thesaurusService->getRecordConcepts($record);

            return response()->json([
                'record_id' => $record->id,
                'pending_jobs' => $pendingJobs,
                'last_mcp_update' => $record->updated_at,
                'thesaurus_concepts_count' => $concepts->count(),
                'has_content' => !empty($record->content),
                'can_process' => app(\App\Services\MCP\McpContentSummarizationService::class)->canProcessRecord($record)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de récupération du statut',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques d'utilisation MCP
     */
    public function getUsageStats(): JsonResponse
    {
        try {
            $stats = $this->mcpManager->getUsageStats();

            return response()->json([
                'message' => 'Statistiques récupérées',
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de récupération des statistiques',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérification de l'état de santé du système MCP
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $health = $this->mcpManager->healthCheck();

            $overallStatus = 'ok';
            foreach ($health as $component => $status) {
                if (isset($status['status']) && $status['status'] === 'error') {
                    $overallStatus = 'error';
                    break;
                }
            }

            return response()->json([
                'overall_status' => $overallStatus,
                'components' => $health,
                'timestamp' => now()->toISOString()
            ], $overallStatus === 'ok' ? 200 : 503);

        } catch (\Exception $e) {
            return response()->json([
                'overall_status' => 'error',
                'error' => 'Échec de la vérification de santé',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 503);
        }
    }

    /**
     * Supprime l'indexation automatique d'un record
     */
    public function removeAutoIndexing(Record $record): JsonResponse
    {
        try {
            $thesaurusService = app(\App\Services\MCP\McpThesaurusIndexingService::class);
            $success = $thesaurusService->removeAutoIndexing($record);

            if ($success) {
                return response()->json([
                    'message' => 'Indexation automatique supprimée',
                    'record_id' => $record->id
                ]);
            } else {
                return response()->json([
                    'error' => 'Échec de la suppression',
                    'record_id' => $record->id
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Échec de la suppression de l\'indexation',
                'message' => $e->getMessage(),
                'record_id' => $record->id
            ], 500);
        }
    }
}