<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MCP\McpManagerService;
use App\Models\Record;
use Cloudstudio\Ollama\Facades\Ollama;

class McpTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:test {--create-sample} {--skip-ollama}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste l\'implémentation complète du module MCP';

    /**
     * Execute the console command.
     */
    public function handle(McpManagerService $mcpManager)
    {
        $this->info('🧪 Test de l\'implémentation MCP');
        $this->newLine();

        // 1. Test de la configuration
        $this->info('1️⃣ Vérification de la configuration...');
        if (!$this->testConfiguration()) {
            return Command::FAILURE;
        }

        // 2. Test de connexion Ollama
        if (!$this->option('skip-ollama')) {
            $this->info('2️⃣ Test de connexion Ollama...');
            if (!$this->testOllamaConnection()) {
                return Command::FAILURE;
            }
        } else {
            $this->warn('⚠️ Test Ollama ignoré (--skip-ollama)');
        }

        // 3. Test de la base de données
        $this->info('3️⃣ Vérification de la base de données...');
        if (!$this->testDatabase()) {
            return Command::FAILURE;
        }

        // 4. Test avec un record d'exemple
        $this->info('4️⃣ Test avec un record d\'exemple...');
        $record = $this->getOrCreateSampleRecord();
        if (!$record) {
            return Command::FAILURE;
        }

        // 5. Test des services MCP
        if (!$this->option('skip-ollama')) {
            $this->info('5️⃣ Test des fonctionnalités MCP...');
            if (!$this->testMcpServices($mcpManager, $record)) {
                return Command::FAILURE;
            }
        }

        // 6. Test du health check
        $this->info('6️⃣ Test du health check...');
        $this->testHealthCheck($mcpManager);

        $this->newLine();
        $this->info('✅ Tous les tests sont passés avec succès !');
        $this->newLine();
        
        $this->displayUsageExamples();
        
        return Command::SUCCESS;
    }

    private function testConfiguration(): bool
    {
        $configs = [
            'ollama-mcp.base_url' => 'URL Ollama',
            'ollama-mcp.models.title_reformulation' => 'Modèle titre',
            'ollama-mcp.models.thesaurus_indexing' => 'Modèle thésaurus',
            'ollama-mcp.models.content_summarization' => 'Modèle résumé',
        ];

        foreach ($configs as $key => $description) {
            $value = config($key);
            if (empty($value)) {
                $this->error("❌ Configuration manquante: {$description} ({$key})");
                return false;
            }
            $this->line("   ✓ {$description}: {$value}");
        }

        return true;
    }

    private function testOllamaConnection(): bool
    {
        try {
            $startTime = microtime(true);
            
            $response = Ollama::prompt('Test de connexion MCP')
                ->model(config('ollama-mcp.models.title_reformulation'))
                ->options(['max_tokens' => 10])
                ->ask();

            $duration = microtime(true) - $startTime;

            $this->line("   ✓ Connexion réussie en " . round($duration, 2) . "s");
            
            if (isset($response['response']) && !empty($response['response'])) {
                $this->line("   ✓ Réponse: " . substr($response['response'], 0, 50) . "...");
            } else {
                $this->line("   ⚠ Réponse Ollama vide ou format inattendu");
            }
            
            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Échec de connexion: " . $e->getMessage());
            $this->warn("   💡 Vérifiez qu'Ollama est démarré: ollama serve");
            $this->warn("   💡 Vérifiez que le modèle est installé: ollama pull " . config('ollama-mcp.models.title_reformulation'));
            return false;
        }
    }

    private function testDatabase(): bool
    {
        try {
            $recordCount = Record::count();
            $this->line("   ✓ Records disponibles: {$recordCount}");

            if (class_exists(\App\Models\ThesaurusConcept::class)) {
                $conceptCount = \App\Models\ThesaurusConcept::count();
                $this->line("   ✓ Concepts thésaurus: {$conceptCount}");
            }

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Erreur base de données: " . $e->getMessage());
            return false;
        }
    }

    private function getOrCreateSampleRecord(): ?Record
    {
        // Chercher un record existant
        $record = Record::whereNotNull('name')
            ->where('name', '!=', '')
            ->first();

        if ($record) {
            $this->line("   ✓ Utilisation du record existant ID: {$record->id}");
            $this->line("   ✓ Titre: {$record->name}");
            return $record;
        }

        // Créer un record d'exemple si autorisé
        if ($this->option('create-sample')) {
            try {
                $record = Record::create([
                    'name' => 'Documents municipaux test MCP',
                    'content' => 'Documents relatifs à l\'administration municipale pour les tests du système MCP.',
                    'date_start' => '1950',
                    'date_end' => '1960',
                ]);

                $this->line("   ✓ Record d'exemple créé ID: {$record->id}");
                return $record;

            } catch (\Exception $e) {
                $this->error("   ❌ Impossible de créer un record d'exemple: " . $e->getMessage());
            }
        }

        $this->warn("   ⚠️ Aucun record disponible pour les tests");
        $this->warn("   💡 Utilisez --create-sample pour créer un record d'exemple");
        return null;
    }

    private function testMcpServices(McpManagerService $mcpManager, Record $record): bool
    {
        try {
            // Test prévisualisation (ne modifie pas les données)
            $this->line("   🔍 Test en mode prévisualisation...");
            
            $previews = $mcpManager->previewProcessing($record, ['title']);
            
            if (isset($previews['title'])) {
                $this->line("   ✓ Reformulation de titre:");
                $this->line("     Original: " . $previews['title']['original_title']);
                $this->line("     Suggéré: " . $previews['title']['suggested_title']);
            }

            return true;

        } catch (\Exception $e) {
            $this->error("   ❌ Erreur lors du test MCP: " . $e->getMessage());
            return false;
        }
    }

    private function testHealthCheck(McpManagerService $mcpManager): void
    {
        try {
            $health = $mcpManager->healthCheck();
            
            foreach ($health as $component => $status) {
                if (isset($status['status'])) {
                    $icon = $status['status'] === 'ok' ? '✓' : '❌';
                    $this->line("   {$icon} {$component}: " . $status['status']);
                }
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Erreur health check: " . $e->getMessage());
        }
    }

    private function displayUsageExamples(): void
    {
        $this->info('📚 Exemples d\'utilisation:');
        $this->newLine();
        
        $this->line('🔧 <comment>Commandes disponibles:</comment>');
        $this->line('   php artisan mcp:process-record 123 --features=title');
        $this->line('   php artisan mcp:batch-process --limit=10 --features=thesaurus');
        $this->line('   php artisan mcp:process-record 123 --preview');
        $this->newLine();
        
        $this->line('🌐 <comment>API REST:</comment>');
        $this->line('   POST /api/mcp/records/123/process');
        $this->line('   POST /api/mcp/records/123/title/reformulate');
        $this->line('   POST /api/mcp/batch/process');
        $this->line('   GET  /api/mcp/health');
        $this->newLine();
        
        $this->line('📖 <comment>Documentation complète:</comment>');
        $this->line('   README_MCP.md');
    }
}
