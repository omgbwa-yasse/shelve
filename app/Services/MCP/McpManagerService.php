<?php

namespace App\Services\MCP;

use App\Models\Record;
use App\Services\MCP\McpTitleReformulationService;
use App\Services\MCP\McpThesaurusIndexingService;
use App\Services\MCP\McpContentSummarizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class McpManagerService
{
    protected McpTitleReformulationService $titleService;
    protected McpThesaurusIndexingService $thesaurusService;
    protected McpContentSummarizationService $summaryService;

    public function __construct(
        McpTitleReformulationService $titleService,
        McpThesaurusIndexingService $thesaurusService,
        McpContentSummarizationService $summaryService
    ) {
        $this->titleService = $titleService;
        $this->thesaurusService = $thesaurusService;
        $this->summaryService = $summaryService;
    }

    /**
     * Traite un record avec les fonctionnalités MCP sélectionnées
     */
    public function processRecord(Record $record, array $features = ['title', 'thesaurus', 'summary']): array
    {
        $this->validateFeatures($features);
        $this->validateRecord($record);
        
        $results = [];
        $startTime = microtime(true);
        
        DB::beginTransaction();
        
        try {
            // Fonctionnalité 1 : Reformulation du titre
            if (in_array('title', $features)) {
                $results['title'] = $this->titleService->reformulateTitle($record);
            }
            
            // Fonctionnalité 2 : Indexation thésaurus
            if (in_array('thesaurus', $features)) {
                $results['thesaurus'] = $this->thesaurusService->indexRecord($record);
            }
            
            // Fonctionnalité 3 : Génération du résumé
            if (in_array('summary', $features)) {
                $results['summary'] = $this->summaryService->generateSummary($record);
            }
            
            DB::commit();
            
            $duration = microtime(true) - $startTime;
            
            // Enregistrer les métriques
            $this->recordMetrics($record, $features, $duration, true);
            
            Log::info('Traitement MCP réussi', [
                'record_id' => $record->id,
                'features' => $features,
                'duration' => round($duration, 2),
                'results' => array_keys($results)
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $duration = microtime(true) - $startTime;
            
            // Enregistrer les métriques d'échec
            $this->recordMetrics($record, $features, $duration, false);
            
            Log::error('Échec traitement MCP', [
                'record_id' => $record->id,
                'features' => $features,
                'duration' => round($duration, 2),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Traite plusieurs records en lot
     */
    public function batchProcessRecords(array $recordIds, array $features = ['title', 'thesaurus', 'summary']): array
    {
        $this->validateFeatures($features);
        $batchSize = config('ollama-mcp.performance.batch_size', 10);
        
        $results = [];
        $errors = [];
        $processed = 0;
        
        $chunks = array_chunk($recordIds, $batchSize);
        
        foreach ($chunks as $chunk) {
            foreach ($chunk as $recordId) {
                try {
                    $record = Record::findOrFail($recordId);
                    
                    if ($this->summaryService->canProcessRecord($record)) {
                        $results[$recordId] = $this->processRecord($record, $features);
                        $processed++;
                    } else {
                        $errors[$recordId] = 'Record ne respecte pas les critères de validation';
                    }
                    
                } catch (\Exception $e) {
                    $errors[$recordId] = $e->getMessage();
                }
                
                // Délai entre les requêtes pour éviter la surcharge
                $delay = config('ollama-mcp.performance.delay_between_requests', 100);
                if ($delay > 0) {
                    usleep($delay * 1000); // Convertir ms en µs
                }
            }
        }
        
        Log::info('Traitement par lots MCP terminé', [
            'total_records' => count($recordIds),
            'processed' => $processed,
            'errors' => count($errors),
            'features' => $features
        ]);
        
        return [
            'processed' => $processed,
            'errors' => count($errors),
            'results' => $results,
            'error_details' => $errors,
            'summary' => [
                'total_records' => count($recordIds),
                'success_rate' => count($recordIds) > 0 ? ($processed / count($recordIds)) * 100 : 0,
                'features_applied' => $features
            ]
        ];
    }

    /**
     * Prévisualise les traitements MCP sans sauvegarder
     */
    public function previewProcessing(Record $record, array $features = ['title', 'thesaurus', 'summary']): array
    {
        $this->validateFeatures($features);
        $this->validateRecord($record);
        
        $previews = [];
        
        try {
            // Aperçu reformulation du titre
            if (in_array('title', $features)) {
                $previews['title'] = $this->titleService->previewTitleReformulation($record);
            }
            
            // Aperçu génération du résumé
            if (in_array('summary', $features)) {
                $previews['summary'] = $this->summaryService->previewSummary($record);
            }
            
            // Pour le thésaurus, on fait un traitement complet car il ne modifie pas les champs principaux
            if (in_array('thesaurus', $features)) {
                $previews['thesaurus'] = $this->thesaurusService->indexRecord($record);
            }
            
            return $previews;
            
        } catch (\Exception $e) {
            Log::error('Erreur prévisualisation MCP', [
                'record_id' => $record->id,
                'features' => $features,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtient les statistiques d'utilisation MCP
     */
    public function getUsageStats(): array
    {
        $cacheKey = 'mcp_usage_stats_' . now()->format('Y-m-d-H');
        
        return Cache::remember($cacheKey, 3600, function () {
            // Récupérer les stats depuis les logs ou une table dédiée
            return [
                'today' => $this->getTodayStats(),
                'this_week' => $this->getWeekStats(),
                'this_month' => $this->getMonthStats(),
                'top_features' => $this->getTopFeatures(),
                'average_duration' => $this->getAverageDuration(),
                'success_rate' => $this->getSuccessRate()
            ];
        });
    }

    /**
     * Vérifie l'état de santé du système MCP
     */
    public function healthCheck(): array
    {
        $health = [];
        
        try {
            // Test de connexion Ollama
            $testRecord = new Record(['name' => 'Test MCP']);
            $response = \Cloudstudio\Ollama\Facades\Ollama::prompt('Test de connexion')
                ->model(config('ollama-mcp.models.title_reformulation'))
                ->options(['max_tokens' => 10])
                ->ask();
            
            $health['ollama_connection'] = [
                'status' => 'ok',
                'response_time' => 0.5, // Placeholder - à implémenter si nécessaire
                'model' => config('ollama-mcp.models.title_reformulation')
            ];
            
        } catch (\Exception $e) {
            $health['ollama_connection'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
        
        // Vérifier les modèles configurés
        $health['models'] = [];
        $models = config('ollama-mcp.models');
        foreach ($models as $feature => $model) {
            $health['models'][$feature] = [
                'configured' => !empty($model),
                'model_name' => $model
            ];
        }
        
        // Vérifier la base de données
        try {
            Record::count();
            \App\Models\ThesaurusConcept::count();
            $health['database'] = ['status' => 'ok'];
        } catch (\Exception $e) {
            $health['database'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
        
        // Calculer le statut global
        $health['overall_status'] = $this->calculateOverallStatus($health);
        
        return $health;
    }

    /**
     * Calcule le statut global basé sur les composants individuels
     */
    private function calculateOverallStatus(array $health): string
    {
        $hasErrors = false;
        $hasWarnings = false;
        
        // Vérifier le statut de connexion Ollama (critique)
        if (isset($health['ollama_connection']['status']) && $health['ollama_connection']['status'] === 'error') {
            return 'error';
        }
        
        // Vérifier la base de données (critique)
        if (isset($health['database']['status']) && $health['database']['status'] === 'error') {
            return 'error';
        }
        
        // Vérifier les modèles configurés
        if (isset($health['models'])) {
            foreach ($health['models'] as $feature => $modelHealth) {
                if (!$modelHealth['configured']) {
                    $hasWarnings = true;
                }
            }
        }
        
        // Déterminer le statut global
        if ($hasErrors) {
            return 'error';
        } elseif ($hasWarnings) {
            return 'warning';
        } else {
            return 'ok';
        }
    }

    /**
     * Valide les fonctionnalités demandées
     */
    private function validateFeatures(array $features): void
    {
        $allowedFeatures = ['title', 'thesaurus', 'summary'];
        $invalidFeatures = array_diff($features, $allowedFeatures);
        
        if (!empty($invalidFeatures)) {
            throw new \InvalidArgumentException(
                'Fonctionnalités invalides: ' . implode(', ', $invalidFeatures) . 
                '. Fonctionnalités autorisées: ' . implode(', ', $allowedFeatures)
            );
        }
        
        if (empty($features)) {
            throw new \InvalidArgumentException('Au moins une fonctionnalité doit être spécifiée');
        }
    }

    /**
     * Valide qu'un record peut être traité
     */
    private function validateRecord(Record $record): void
    {
        if (!$record->exists) {
            throw new \InvalidArgumentException('Le record doit exister en base de données');
        }
        
        if (!$this->summaryService->canProcessRecord($record)) {
            throw new \InvalidArgumentException('Le record ne respecte pas les critères de validation MCP');
        }
    }

    /**
     * Enregistre les métriques de performance
     */
    private function recordMetrics(Record $record, array $features, float $duration, bool $success): void
    {
        $timestamp = now();
        $date = $timestamp->format('Y-m-d');
        $hour = $timestamp->format('H');
        
        foreach ($features as $feature) {
            $metricsKey = "mcp_metrics:{$feature}:{$date}:{$hour}";
            
            $metrics = Cache::get($metricsKey, [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'total_duration' => 0,
                'min_duration' => null,
                'max_duration' => null
            ]);
            
            $metrics['total_requests']++;
            $metrics['total_duration'] += $duration;
            
            if ($success) {
                $metrics['successful_requests']++;
            } else {
                $metrics['failed_requests']++;
            }
            
            if (is_null($metrics['min_duration']) || $duration < $metrics['min_duration']) {
                $metrics['min_duration'] = $duration;
            }
            
            if (is_null($metrics['max_duration']) || $duration > $metrics['max_duration']) {
                $metrics['max_duration'] = $duration;
            }
            
            Cache::put($metricsKey, $metrics, 3600 * 24); // 24h
        }
    }

    // Méthodes privées pour les statistiques
    private function getTodayStats(): array
    {
        // Implémenter selon vos besoins de logging
        return ['requests' => 0, 'success' => 0, 'errors' => 0];
    }

    private function getWeekStats(): array
    {
        return ['requests' => 0, 'success' => 0, 'errors' => 0];
    }

    private function getMonthStats(): array
    {
        return ['requests' => 0, 'success' => 0, 'errors' => 0];
    }

    private function getTopFeatures(): array
    {
        return ['title' => 0, 'thesaurus' => 0, 'summary' => 0];
    }

    private function getAverageDuration(): float
    {
        return 0.0;
    }

    private function getSuccessRate(): float
    {
        return 100.0;
    }
}