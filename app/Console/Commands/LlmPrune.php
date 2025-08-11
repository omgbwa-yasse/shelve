<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LlmPrune extends Command
{
    protected $signature = 'llm:prune {--days=90 : Conserver uniquement les interactions plus récentes que N jours}';
    protected $description = 'Purge les anciennes interactions LLM (table llm_interactions)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $threshold = now()->subDays($days);
        $count = DB::table('llm_interactions')->where('started_at', '<', $threshold)->count();
        if ($count === 0) {
            $this->info('Aucune interaction à purger.');
            return self::SUCCESS;
        }
        DB::table('llm_interactions')->where('started_at', '<', $threshold)->limit(50_000)->delete();
        $this->info("Purge effectuée (échantillon). Restant potentiel: ".max(0,$count-50_000));
        return self::SUCCESS;
    }
}
