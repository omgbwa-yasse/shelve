<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Llm\LlmMetricsService;
use Carbon\Carbon;

class LlmAggregateDaily extends Command
{
    protected $signature = 'llm:aggregate-daily {date? : Date (YYYY-MM-DD) à agréger, défaut = aujourd\'hui}';
    protected $description = 'Agrège les interactions LLM de la journée dans llm_daily_stats';

    public function handle(LlmMetricsService $service): int
    {
        $date = $this->argument('date');
        $day = $date ? Carbon::parse($date) : now();
        $this->info('Agrégation LLM pour: '.$day->toDateString());
        $service->aggregateDaily($day);
        $this->info('Terminé');
        return self::SUCCESS;
    }
}
