
<?php

namespace App\Jobs;

use App\Models\AiJob;
use App\Services\OllamaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOllamaBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AiJob $job;

    public function __construct(AiJob $job)
    {
        $this->job = $job;
    }

    public function handle(OllamaService $ollamaService)
    {
        $ollamaService->processBatchJob($this->job);
    }
}
