<?php
namespace App\Console\Commands;

use App\Services\OllamaService;
use Illuminate\Console\Command;

class SyncOllamaModels extends Command
{
    protected $signature = 'ollama:sync-models';
    protected $description = 'Synchronize Ollama models with database';

    public function handle(OllamaService $ollamaService)
    {
        $this->info('Starting Ollama models synchronization...');

        try {
            $synced = $ollamaService->syncModels();
            $this->info("Successfully synced {$synced} models from Ollama");
        } catch (\Exception $e) {
            $this->error('Failed to sync models: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
