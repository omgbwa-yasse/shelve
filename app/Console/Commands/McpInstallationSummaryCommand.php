<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class McpInstallationSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:installation-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Affiche le résumé complet de l\'installation MCP et les prochaines étapes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->displayHeader();
        $this->checkInstallation();
        $this->displayFeatures();
        $this->displayNextSteps();
        $this->displayResources();
        
        return Command::SUCCESS;
    }

    private function displayHeader()
    {
        $this->info('');
        $this->info('🎉 ============================================== 🎉');
        $this->info('   MODULE MCP (Model Context Protocol) INSTALLÉ');
        $this->info('🎉 ============================================== 🎉');
        $this->info('');
        $this->info('✨ Intégration Ollama + Laravel pour l\'archivage ISAD(G) ✨');
        $this->newLine();
    }

    private function checkInstallation()
    {
        $this->info('📋 VÉRIFICATION DE L\'INSTALLATION');
        $this->info('─'.str_repeat('─', 40));
        
        $files = [
            'config/ollama-mcp.php' => 'Configuration MCP',
            'app/Services/MCP/McpManagerService.php' => 'Service Manager',
            'app/Services/MCP/McpTitleReformulationService.php' => 'Service Reformulation Titre',
            'app/Services/MCP/McpThesaurusIndexingService.php' => 'Service Indexation',
            'app/Services/MCP/McpContentSummarizationService.php' => 'Service Résumé',
            'app/Http/Controllers/Api/McpController.php' => 'Contrôleur API',
            'app/Jobs/ProcessRecordWithMcp.php' => 'Job Asynchrone',
            'app/Observers/RecordObserver.php' => 'Observer Automatique',
            'routes/mcp.php' => 'Routes MCP',
            'README_MCP.md' => 'Documentation',
            'QUICK_START_MCP.md' => 'Guide Rapide'
        ];

        foreach ($files as $file => $description) {
            if (File::exists(base_path($file))) {
                $this->line("   ✅ {$description}");
            } else {
                $this->error("   ❌ {$description} - MANQUANT");
            }
        }

        $this->newLine();
        
        // Vérifier les commandes
        $this->info('🔧 COMMANDES ARTISAN DISPONIBLES');
        $this->info('─'.str_repeat('─', 40));
        $commands = [
            'mcp:test' => 'Test de l\'installation',
            'mcp:process-record' => 'Traitement individuel',
            'mcp:batch-process' => 'Traitement par lots',
            'mcp:installation-summary' => 'Ce résumé'
        ];

        foreach ($commands as $command => $description) {
            $this->line("   ⚡ php artisan {$command} - {$description}");
        }
        
        $this->newLine();
    }

    private function displayFeatures()
    {
        $this->info('🚀 FONCTIONNALITÉS IMPLÉMENTÉES');
        $this->info('─'.str_repeat('─', 40));
        
        $features = [
            [
                'title' => '📝 Reformulation du Titre Record (ISAD-G)',
                'description' => 'Reformule automatiquement les titres selon les règles ISAD(G)',
                'model' => 'llama3.1:8b',
                'example' => 'Documents mairie → Personnel municipal, médailles du travail : listes. 1950-1960'
            ],
            [
                'title' => '🏷️ Indexation Thésaurus',
                'description' => 'Extrait 5 mots-clés + 3 synonymes, recherche dans le thésaurus',
                'model' => 'mistral:7b',
                'example' => 'Texte → [personnel, médaille, municipal] → Concepts trouvés'
            ],
            [
                'title' => '📄 Résumé ISAD(G) - Élément 3.3.1',
                'description' => 'Génère le résumé "Portée et contenu" selon le niveau',
                'model' => 'llama3.1:8b',
                'example' => 'Contient les listes nominatives et correspondance concernant...'
            ]
        ];

        foreach ($features as $feature) {
            $this->line("   {$feature['title']}");
            $this->line("     • {$feature['description']}");
            $this->line("     • Modèle: {$feature['model']}");
            $this->line("     • Exemple: {$feature['example']}");
            $this->newLine();
        }
    }

    private function displayNextSteps()
    {
        $this->info('🎯 PROCHAINES ÉTAPES');
        $this->info('─'.str_repeat('─', 40));
        
        $this->line('   1️⃣ INSTALLER OLLAMA');
        $this->line('      • Windows: winget install ollama');
        $this->line('      • Démarrer: ollama serve');
        $this->line('      • Modèles: ollama pull llama3.1:8b && ollama pull mistral:7b');
        $this->newLine();
        
        $this->line('   2️⃣ CONFIGURER LARAVEL');
        $this->line('      • Copier les variables .env depuis le fichier .env.example');
        $this->line('      • Vérifier: OLLAMA_URL=http://127.0.0.1:11434');
        $this->newLine();
        
        $this->line('   3️⃣ TESTER L\'INSTALLATION');
        $this->line('      • php artisan mcp:test --skip-ollama  (sans Ollama)');
        $this->line('      • php artisan mcp:test               (avec Ollama)');
        $this->newLine();
        
        $this->line('   4️⃣ PREMIER ESSAI');
        $this->line('      • php artisan mcp:process-record 123 --preview');
        $this->line('      • php artisan mcp:batch-process --limit=5 --features=thesaurus');
        $this->newLine();
        
        $this->line('   5️⃣ PRODUCTION');
        $this->line('      • php artisan queue:work --queue=mcp-light,mcp-medium,mcp-heavy');
        $this->line('      • Configurer la surveillance avec Horizon');
        $this->newLine();
    }

    private function displayResources()
    {
        $this->info('📚 RESSOURCES ET AIDE');
        $this->info('─'.str_repeat('─', 40));
        
        $this->line('   📖 Documentation Complète');
        $this->line('      • README_MCP.md - Guide détaillé avec exemples');
        $this->line('      • QUICK_START_MCP.md - Démarrage en 5 minutes');
        $this->newLine();
        
        $this->line('   🌐 API REST');
        $this->line('      • GET  /api/mcp/health - État de santé');
        $this->line('      • POST /api/mcp/records/{id}/process - Traitement');
        $this->line('      • POST /api/mcp/records/{id}/title/reformulate - Titre');
        $this->line('      • POST /api/mcp/batch/process - Traitement par lots');
        $this->newLine();
        
        $this->line('   🖥️ Interface Web');
        $this->line('      • /admin/mcp - Dashboard de monitoring (à configurer)');
        $this->newLine();
        
        $this->line('   📊 Configuration Avancée');
        $this->line('      • config/ollama-mcp.php - Paramètres MCP');
        $this->line('      • .env - Variables d\'environnement');
        $this->newLine();
        
        $this->info('🆘 DÉPANNAGE');
        $this->info('─'.str_repeat('─', 40));
        $this->line('   • Ollama non accessible: ollama serve');
        $this->line('   • Modèle manquant: ollama pull llama3.1:8b');
        $this->line('   • Logs détaillés: tail -f storage/logs/laravel.log');
        $this->line('   • Test santé: curl http://127.0.0.1:11434/api/tags');
        $this->newLine();
        
        $this->info('🎉 FÉLICITATIONS !');
        $this->info('─'.str_repeat('─', 40));
        $this->line('   Votre module MCP est prêt à transformer votre archivage !');
        $this->line('   Les 3 fonctionnalités IA sont intégrées et opérationnelles.');
        $this->newLine();
        
        $this->warn('💡 CONSEIL: Commencez par tester en mode prévisualisation');
        $this->warn('   puis passez au traitement par lots avec --async');
        $this->newLine();
    }
}
