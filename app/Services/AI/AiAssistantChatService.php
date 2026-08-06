<?php

namespace App\Services\AI;

use AiBridge\Facades\AiBridge;
use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Chat généraliste de l'assistant IA du panneau latéral — distinct du
 * pipeline `QueryAnalyzerService`/`QueryExecutorService` (spécialisé dans la
 * traduction NL → instructions JSON de recherche pour la page `/ai-search`) :
 * ici l'IA répond librement, en tenant compte du contexte de la page active
 * transmis par le client (voir onglet "Chat" du panneau, Next.js).
 *
 * Exigence utilisateur du 2026-08-05 : l'assistant ne doit se comporter que
 * dans le périmètre des permissions de l'agent connecté (voir
 * `AiCapabilityService`), et le degré de confirmation exigé avant une action
 * de création/modification/suppression dépend du mode de la conversation
 * (voir `AiConversation::MODES`) — le chat ne fait aujourd'hui aucun appel
 * CRUD réel : ce garde-fou prépare le terrain pour une future exécution
 * d'actions outillée, aucun mode ne contourne les policies Laravel.
 */
class AiAssistantChatService
{
    public function __construct(
        private ProviderRegistry $registry,
        private DefaultValueService $defaultValues,
        private AiCapabilityService $capabilities,
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @param  array<string, mixed>|null  $pageContext
     * @return array{success: bool, reply?: string, error?: string}
     */
    public function reply(
        string $message,
        array $history,
        ?array $pageContext = null,
        ?User $user = null,
        string $mode = AiConversation::MODE_MANUEL,
    ): array {
        $providerName = $this->defaultValues->getDefaultProvider();

        try {
            $this->registry->ensureConfigured($providerName);

            $messages = [
                ['role' => 'system', 'content' => $this->systemPrompt($pageContext, $user, $mode)],
            ];

            foreach (array_slice($history, -20) as $turn) {
                if (isset($turn['role'], $turn['content']) && in_array($turn['role'], ['user', 'assistant'], true)) {
                    $messages[] = ['role' => $turn['role'], 'content' => (string) $turn['content']];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $message];

            $response = AiBridge::provider($providerName)->chat($messages, [
                'model' => $this->defaultValues->getDefaultModel(),
            ]);

            return [
                'success' => true,
                'reply' => ResponseTextExtractor::extract($response),
            ];
        } catch (\Throwable $e) {
            Log::error('AiAssistantChatService: erreur de réponse', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Erreur IA : ' . $e->getMessage(),
            ];
        }
    }

    private function systemPrompt(?array $pageContext, ?User $user, string $mode): string
    {
        $prompt = "Tu es l'assistant intégré de l'application d'archivage. Réponds en français, "
            . 'de façon concise et actionnable. Tu peux t\'appuyer sur le contexte de la page '
            . "actuellement consultée par l'agent, fourni ci-dessous au format JSON, pour adapter ta réponse.";

        $prompt .= "\n\nRÈGLE DE PÉRIMÈTRE (obligatoire, quel que soit le mode) :\n"
            . "Ne propose et ne prétends jamais exécuter une action hors des permissions de l'agent listées ci-dessous. "
            . "Si une demande dépasse ces permissions, refuse et explique pourquoi.";

        $prompt .= "\n\n" . $this->modeInstructions($mode);

        if ($user) {
            $prompt .= "\n\n" . $this->capabilities->summaryFor($user);
        }

        if ($pageContext) {
            $prompt .= "\n\nCONTEXTE DE LA PAGE ACTIVE:\n" . json_encode($pageContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $prompt;
    }

    private function modeInstructions(string $mode): string
    {
        return match ($mode) {
            AiConversation::MODE_PLAN =>
                "MODE PLAN — tu ne dois JAMAIS exécuter ni prétendre exécuter quoi que ce soit. Produis uniquement un "
                . "plan détaillé, étape par étape, de ce qu'il faudrait faire. Termine systématiquement par : "
                . "« Ceci est un plan, aucune action n'a été exécutée. Dites-moi si je dois procéder. »",

            AiConversation::MODE_EDIT =>
                "MODE EDIT — tu peux présenter les modifications (mises à jour) comme pré-approuvées par l'agent, sans "
                . "redemander confirmation à chaque échange. En revanche, toute action de CRÉATION ou de SUPPRESSION doit "
                . "toujours être proposée et attendre une confirmation explicite avant d'être considérée comme faite.",

            AiConversation::MODE_AUTONOME =>
                "MODE AUTONOME — tu peux traiter les actions de création, modification et suppression comme autorisées "
                . "sans redemander confirmation à chaque fois, à condition qu'elles restent strictement dans les "
                . "permissions de l'agent listées ci-dessous. Résume toujours clairement ce qui a été fait après coup. "
                . "Dès qu'une action sort de ces permissions, arrête-toi et demande une confirmation explicite.",

            default => // AiConversation::MODE_MANUEL
                "MODE MANUEL — pour toute action de création, modification ou suppression, présente-la comme une "
                . "proposition et demande explicitement la confirmation de l'agent avant de la considérer comme "
                . "effectuée. Ne dis jamais qu'une action de ce type a été réalisée sans confirmation préalable.",
        };
    }
}
