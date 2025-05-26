// app/Jobs/ProcessOllamaInteraction.php
<?php

namespace App\Jobs;

use App\Models\AiInteraction;
use App\Services\OllamaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOllamaInteraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AiInteraction $interaction;

    public function __construct(AiInteraction $interaction)
    {
        $this->interaction = $interaction;
    }

    public function handle(OllamaService $ollamaService)
    {
        $ollamaService->processInteraction($this->interaction);
    }
}