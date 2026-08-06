<?php

namespace App\Console\Commands;

use App\Models\AiRoutine;
use App\Services\AI\AiRoutineExecutionService;
use Illuminate\Console\Command;

/**
 * Exécute les routines IA programmées arrivées à échéance (`ai_routines`,
 * voir `AiRoutine::scopeDue`). Enregistrée dans `Kernel::schedule()`.
 */
class AiRoutinesRunDueCommand extends Command
{
    protected $signature = 'ai:routines:run-due';

    protected $description = "Exécute les routines IA programmées dont l'échéance est atteinte";

    public function handle(AiRoutineExecutionService $executor): int
    {
        $due = AiRoutine::due()->get();

        foreach ($due as $routine) {
            $result = $executor->execute($routine);
            $routine->markRun($result['status'], $result['output']);

            $this->info("Routine #{$routine->id} ({$routine->name}) : {$result['status']}");
        }

        $this->info("{$due->count()} routine(s) exécutée(s).");

        return self::SUCCESS;
    }
}
