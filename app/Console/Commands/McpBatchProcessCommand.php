<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecordPhysical;
use App\Services\MCP\McpManagerService;
use App\Services\MCP\McpContentSummarizationService;

class McpBatchProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:batch-process 
                           {--organisation_id= : ID de l\'organisation} 
                           {--activity_id= : ID de l\'activité}
                           {--level_id= : ID du niveau}
                           {--features=title,thesaurus,summary : Fonctionnalités à appliquer}
                           {--limit=50 : Nombre maximum de records à traiter}
                           {--async : Traitement asynchrone via jobs}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traite plusieurs records en lot avec les fonctionnalités MCP';

    /**
     * Execute the console command.
     */
    public function handle(McpManagerService $mcpManager, McpContentSummarizationService $summaryService)
    {
        $features = explode(',', $this->option('features'));
        $limit = (int) $this->option('limit');
        $async = $this->option('async');
        
        $this->info("🚀 Traitement par lots MCP");
        $this->info("Fonctionnalités: " . implode(', ', $features));
        $this->info("Limite: {$limit} records");
        $this->info("Mode: " . ($async ? 'Asynchrone' : 'Synchrone'));
        $this->newLine();
        
        // Construire la requête
        $query = $this->buildQuery();
        
        // Filtrer les records qui peuvent être traités
        $query->where(function ($q) use ($summaryService) {
            $q->whereNotNull('name')
              ->where('name', '!=', '');
        });
        
        $recordIds = $query->limit($limit)->pluck('id')->toArray();
        
        if (empty($recordIds)) {
            $this->warn('Aucun record trouvé avec ces critères.');
            return Command::SUCCESS;
        }
        
        $this->info("Records sélectionnés: " . count($recordIds));
        
        if (!$this->confirm('Continuer le traitement ?')) {
            $this->info('Traitement annulé.');
            return Command::SUCCESS;
        }
        
        $this->newLine();
        
        if ($async) {
            return $this->handleAsyncProcessing($recordIds, $features);
        } else {
            return $this->handleSyncProcessing($mcpManager, $recordIds, $features);
        }
    }

    /**
     * Construit la requête selon les filtres
     */
    private function buildQuery()
    {
        $query = RecordPhysical::query();
        
        if ($organisationId = $this->option('organisation_id')) {
            $query->where('organisation_id', $organisationId);
            $this->info("Filtre organisation: {$organisationId}");
        }
        
        if ($activityId = $this->option('activity_id')) {
            $query->where('activity_id', $activityId);
            $this->info("Filtre activité: {$activityId}");
        }
        
        if ($levelId = $this->option('level_id')) {
            $query->where('level_id', $levelId);
            $this->info("Filtre niveau: {$levelId}");
        }
        
        return $query;
    }

    /**
     * Traitement asynchrone via jobs
     */
    private function handleAsyncProcessing(array $recordIds, array $features): int
    {
        $this->info("Lancement des jobs asynchrones...");
        
        $progressBar = $this->output->createProgressBar(count($recordIds));
        $progressBar->start();
        
        foreach ($recordIds as $recordId) {
            $record = RecordPhysical::find($recordId);
            if ($record) {
                \App\Jobs\ProcessRecordWithMcp::dispatch($record, $features);
            }
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✅ " . count($recordIds) . " jobs lancés !");
        $this->info("Vérifiez les logs pour suivre l'avancement.");
        $this->info("Commande de monitoring: php artisan queue:work");
        
        return Command::SUCCESS;
    }

    /**
     * Traitement synchrone
     */
    private function handleSyncProcessing(McpManagerService $mcpManager, array $recordIds, array $features): int
    {
        $this->info("Traitement synchrone en cours...");
        
        $progressBar = $this->output->createProgressBar(count($recordIds));
        $progressBar->start();
        
        $results = [];
        $errors = [];
        
        foreach ($recordIds as $recordId) {
            try {
                $record = RecordPhysical::find($recordId);
                if ($record) {
                    $results[$recordId] = $mcpManager->processRecord($record, $features);
                }
            } catch (\Exception $e) {
                $errors[$recordId] = $e->getMessage();
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->displaySummary($results, $errors, $features);
        
        return empty($errors) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Affiche le résumé des résultats
     */
    private function displaySummary(array $results, array $errors, array $features): void
    {
        $this->info("📊 Résumé du traitement:");
        $this->newLine();
        
        $this->line("Records traités avec succès: <info>" . count($results) . "</info>");
        
        if (!empty($errors)) {
            $this->line("Records en erreur: <error>" . count($errors) . "</error>");
            $this->newLine();
            
            $this->error("Détail des erreurs:");
            foreach (array_slice($errors, 0, 5) as $recordId => $error) {
                $this->line("  - Record {$recordId}: " . substr($error, 0, 100) . "...");
            }
            
            if (count($errors) > 5) {
                $this->line("  ... et " . (count($errors) - 5) . " autres erreurs");
            }
        }
        
        // Statistiques par fonctionnalité
        if (!empty($results)) {
            $this->newLine();
            $this->info("Statistiques par fonctionnalité:");
            
            foreach ($features as $feature) {
                $successCount = 0;
                foreach ($results as $result) {
                    if (isset($result[$feature])) {
                        $successCount++;
                    }
                }
                
                $this->line("  - {$feature}: {$successCount}/" . count($results) . " réussis");
            }
        }
        
        $this->newLine();
        $this->info("✅ Traitement par lots terminé !");
    }
}
