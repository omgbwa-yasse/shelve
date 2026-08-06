<?php

namespace App\Services\AI;

use AiBridge\Facades\AiBridge;
use App\Models\AiRoutine;
use Illuminate\Support\Facades\Log;

/**
 * Exécute une routine programmée (`AiRoutine`) : envoie le contenu du prompt
 * ou du skill (D14, `Prompt`/`AiSkill`) lié à l'IA et retourne le résultat.
 * Appelée à la demande (`AiRoutineController::run`) ou par la commande
 * planifiée `ai:routines:run-due` (voir `Kernel::schedule()`).
 *
 * Exécution non interactive par nature (planifiée) : elle ne peut pas
 * s'arrêter pour demander une confirmation humaine. Le résultat est donc
 * systématiquement traité comme une PROPOSITION à valider — voir l'instruction
 * système ci-dessous — jamais comme une action déjà effectuée sur les données
 * de l'application (exigence utilisateur du 2026-08-05).
 */
class AiRoutineExecutionService
{
    public function __construct(
        private ProviderRegistry $registry,
        private DefaultValueService $defaultValues,
    ) {}

    /** @return array{status: string, output: string} */
    public function execute(AiRoutine $routine): array
    {
        $instruction = $this->resolveInstruction($routine);

        if ($instruction === null) {
            return [
                'status' => AiRoutine::STATUS_ERROR,
                'output' => 'Routine sans prompt ni skill associé — rien à exécuter.',
            ];
        }

        $providerName = $this->defaultValues->getDefaultProvider();

        try {
            $this->registry->ensureConfigured($providerName);

            $response = AiBridge::provider($providerName)->chat([
                ['role' => 'system', 'content' => "Tu exécutes une routine programmée de l'application d'archivage. Fournis un résultat concis et actionnable. "
                    . "Cette exécution est automatique et non supervisée : si le résultat suggère une action de création, "
                    . "modification ou suppression sur les données de l'application, formule-la explicitement comme une "
                    . "RECOMMANDATION à valider par un utilisateur humain, jamais comme une action déjà réalisée."],
                ['role' => 'user', 'content' => $instruction],
            ], [
                'model' => $this->defaultValues->getDefaultModel(),
            ]);

            return [
                'status' => AiRoutine::STATUS_SUCCESS,
                'output' => ResponseTextExtractor::extract($response),
            ];
        } catch (\Throwable $e) {
            Log::error('AiRoutineExecutionService: échec d\'exécution', [
                'routine_id' => $routine->id,
                'message' => $e->getMessage(),
            ]);

            return [
                'status' => AiRoutine::STATUS_ERROR,
                'output' => 'Erreur IA : ' . $e->getMessage(),
            ];
        }
    }

    private function resolveInstruction(AiRoutine $routine): ?string
    {
        if ($routine->prompt_id && $routine->prompt) {
            return $routine->prompt->content;
        }

        if ($routine->skill_id && $routine->skill && is_file($routine->skill->skill_md_path)) {
            return file_get_contents($routine->skill->skill_md_path) ?: null;
        }

        return null;
    }
}
