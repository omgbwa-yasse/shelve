<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Record;
use App\Observers\RecordObserver;
use App\Services\MCP\McpManagerService;
use App\Services\MCP\McpTitleReformulationService;
use App\Services\MCP\McpThesaurusIndexingService;
use App\Services\MCP\McpContentSummarizationService;

class McpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer les services MCP comme singletons
        $this->app->singleton(McpManagerService::class);
        $this->app->singleton(McpTitleReformulationService::class);
        $this->app->singleton(McpThesaurusIndexingService::class);
        $this->app->singleton(McpContentSummarizationService::class);

        // Configuration par défaut du module MCP
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ollama-mcp.php', 'ollama-mcp'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Enregistrer l'Observer pour le traitement automatique
        if (config('ollama-mcp.auto_processing.enabled', false)) {
            Record::observe(RecordObserver::class);
        }

        // Publier la configuration si nous sommes en mode console
        if ($this->app->runningInConsole()) {
            $this->publishConfiguration();
        }

        // Enregistrer les commandes Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\McpProcessRecordsCommand::class,
                \App\Console\Commands\McpBatchProcessCommand::class,
                \App\Console\Commands\McpTestCommand::class,
            ]);
        }
    }

    /**
     * Publier les fichiers de configuration et assets
     */
    private function publishConfiguration(): void
    {
        // Configuration MCP
        $this->publishes([
            __DIR__.'/../../config/ollama-mcp.php' => config_path('ollama-mcp.php'),
        ], 'mcp-config');

        // Documentation
        $this->publishes([
            __DIR__.'/../../README_MCP.md' => base_path('README_MCP.md'),
            __DIR__.'/../../QUICK_START_MCP.md' => base_path('QUICK_START_MCP.md'),
        ], 'mcp-docs');

        // Vues d'administration (si créées)
        $this->publishes([
            __DIR__.'/../../resources/views/admin/mcp' => resource_path('views/admin/mcp'),
        ], 'mcp-views');
    }

    /**
     * Obtenir les services fournis par ce provider
     */
    public function provides(): array
    {
        return [
            McpManagerService::class,
            McpTitleReformulationService::class,
            McpThesaurusIndexingService::class,
            McpContentSummarizationService::class,
        ];
    }
}
