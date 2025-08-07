<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Record;
use App\Services\MCP\McpManagerService;

class McpProcessRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:process-record {record_id} {--features=title,thesaurus,summary} {--preview}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traite un record avec les fonctionnalités MCP';

    /**
     * Execute the console command.
     */
    public function handle(McpManagerService $mcpManager)
    {
        $recordId = $this->argument('record_id');
        $features = explode(',', $this->option('features'));
        $preview = $this->option('preview');
        
        try {
            $record = Record::findOrFail($recordId);
            
            $this->info("Traitement du record ID: {$recordId}");
            $this->info("Titre: {$record->name}");
            $this->info("Fonctionnalités: " . implode(', ', $features));
            $this->newLine();
            
            if ($preview) {
                $this->info("Mode prévisualisation activé - aucune modification ne sera sauvegardée");
                $results = $mcpManager->previewProcessing($record, $features);
                
                $this->displayPreviewResults($results);
            } else {
                $this->info("Traitement en cours...");
                $results = $mcpManager->processRecord($record, $features);
                
                $this->displayResults($results);
            }
            
            $this->newLine();
            $this->info('✅ Traitement réussi !');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Affiche les résultats de traitement
     */
    private function displayResults(array $results): void
    {
        foreach ($results as $feature => $result) {
            $this->newLine();
            
            switch ($feature) {
                case 'title':
                    $this->line("📝 <info>Titre reformulé:</info> {$result}");
                    break;
                    
                case 'thesaurus':
                    $this->line("🏷️  <info>Indexation thésaurus:</info>");
                    $this->line("   - Mots-clés extraits: " . count($result['keywords_extracted']));
                    $this->line("   - Concepts trouvés: {$result['concepts_found']}");
                    
                    if (!empty($result['concepts'])) {
                        $this->line("   - Principaux concepts:");
                        foreach (array_slice($result['concepts'], 0, 3) as $concept) {
                            $this->line("     • {$concept['preferred_label']} (poids: " . round($concept['weight'], 2) . ")");
                        }
                    }
                    break;
                    
                case 'summary':
                    $this->line("📄 <info>Résumé généré:</info>");
                    $this->line("   " . substr($result, 0, 150) . (strlen($result) > 150 ? '...' : ''));
                    break;
            }
        }
    }

    /**
     * Affiche les résultats de prévisualisation
     */
    private function displayPreviewResults(array $results): void
    {
        foreach ($results as $feature => $result) {
            $this->newLine();
            
            switch ($feature) {
                case 'title':
                    $this->line("📝 <info>Aperçu reformulation titre:</info>");
                    $this->line("   Original: {$result['original_title']}");
                    $this->line("   Suggéré: {$result['suggested_title']}");
                    break;
                    
                case 'summary':
                    $this->line("📄 <info>Aperçu résumé:</info>");
                    $this->line("   Original: " . substr($result['original_content'] ?? 'Aucun', 0, 100) . "...");
                    $this->line("   Suggéré: " . substr($result['suggested_summary'], 0, 100) . "...");
                    break;
                    
                case 'thesaurus':
                    $this->line("🏷️  <info>Indexation thésaurus (appliquée):</info>");
                    $this->line("   - Concepts associés: {$result['concepts_found']}");
                    break;
            }
        }
    }
}
