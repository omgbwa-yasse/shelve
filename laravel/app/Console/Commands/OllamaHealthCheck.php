<?php
namespace App\Console\Commands;

use App\Services\OllamaService;
use Illuminate\Console\Command;

class OllamaHealthCheck extends Command
{
    protected $signature = 'ollama:health';
    protected $description = 'Check Ollama service health';

    public function handle(OllamaService $ollamaService)
    {
        $health = $ollamaService->healthCheck();

        $this->info("Ollama Status: {$health['status']}");
        $this->info("Response Time: {$health['response_time']}ms");
        $this->info("Message: {$health['message']}");

        return $health['status'] === 'healthy' ? 0 : 1;
    }
}
