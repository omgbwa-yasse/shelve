<?php

namespace App\Services\AI\Agent;

use AiBridge\Facades\AiBridge;
use App\Models\User;
use App\Services\AI\PromptTransactionService;
use App\Services\AI\ProviderRegistry;
use App\Services\AI\ResponseTextExtractor;
use Illuminate\Support\Facades\Log;

/**
 * Boucle agentique : le modèle choisit un outil, observe le résultat,
 * relance d'autres recherches si nécessaire, puis conclut.
 *
 * Le protocole est indépendant du provider (pas de tool-calling natif) :
 * le modèle répond uniquement en JSON, soit {"action":"tool",...},
 * soit {"action":"final",...}. Compatible Ollama/Mistral/Claude/OpenAI
 * via AiBridge, y compris avec des petits modèles locaux.
 */
class AgentOrchestratorService
{
    private const MAX_ITERATIONS = 10;
    private const MAX_ITEMS_IN_CONTEXT = 10;

    public function __construct(
        private ProviderRegistry $registry,
        private AgentToolRegistry $tools,
        private PromptTransactionService $transactions
    ) {
    }

    /**
     * @param callable|null $onEvent Reçoit ('step', array $step) après chaque
     *                               outil et ('final', array $payload) à la fin
     *                               — utilisé par le streaming SSE.
     */
    public function run(string $userMessage, User $user, ?callable $onEvent = null): array
    {
        $startedAt = microtime(true);
        $transactionId = null;

        try {
            $result = $this->runLoop($userMessage, $user, $onEvent, $transactionId, $startedAt);

            if ($transactionId !== null) {
                $this->safeFinish(fn () => $this->transactions->finishSuccess($transactionId, [
                    'latency_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                ]));
            }

            return $result;
        } catch (\Throwable $e) {
            if ($transactionId !== null) {
                $this->safeFinish(fn () => $this->transactions->finishFailure($transactionId, $e->getMessage(), (int) ((microtime(true) - $startedAt) * 1000)));
            }
            throw $e;
        }
    }

    private function safeFinish(callable $finish): void
    {
        try {
            $finish();
        } catch (\Throwable $e) {
            Log::warning('AI Agent: échec journalisation PromptTransaction', ['error' => $e->getMessage()]);
        }
    }

    private function runLoop(string $userMessage, User $user, ?callable $onEvent, ?int &$transactionId, float $startedAt): array
    {
        $providerName = $this->resolveProvider();
        $model = $this->resolveModel($providerName);
        $this->registry->ensureConfigured($providerName);

        try {
            $transactionId = $this->transactions->start([
                'model' => $model,
                'model_provider' => $providerName,
                'organisation_id' => $user->current_organisation_id,
                'user_id' => $user->id,
                'entity' => 'ai_agent',
            ]);
        } catch (\Throwable $e) {
            Log::warning('AI Agent: journalisation indisponible', ['error' => $e->getMessage()]);
            $transactionId = null;
        }

        $maxIterations = (int) ($this->registry->getSetting('ai_agent_max_iterations', self::MAX_ITERATIONS) ?: self::MAX_ITERATIONS);

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($user, $maxIterations)],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $steps = [];
        $itemMap = [];
        $invalidResponses = 0;

        for ($iteration = 1; $iteration <= $maxIterations; $iteration++) {
            $text = $this->chatWithRetry($providerName, $model, $messages);

            if ($text === null) {
                // Le fournisseur IA renvoie une erreur (rate limit, panne...) même
                // après nouvelle tentative : on l'annonce clairement, en échec,
                // plutôt que d'afficher le JSON brut ou de prétendre un succès.
                return $this->emitFinal($this->buildResponse(
                    "Le service IA est momentanément surchargé ou indisponible. Merci de réessayer dans quelques instants.",
                    [],
                    $steps,
                    $itemMap,
                    $iteration,
                    success: false
                ), $onEvent);
            }

            $decision = $this->parseDecision($text);

            if ($decision === null) {
                $invalidResponses++;
                Log::warning('AI Agent: réponse non parsable', ['iteration' => $iteration, 'raw' => mb_substr((string) $text, 0, 500)]);

                if ($invalidResponses >= 2) {
                    // Le modèle ne suit pas le protocole : on rend son texte tel quel.
                    return $this->emitFinal($this->buildResponse(trim((string) $text) ?: "Je n'ai pas pu traiter la demande.", [], $steps, $itemMap, $iteration), $onEvent);
                }

                $messages[] = ['role' => 'assistant', 'content' => (string) $text];
                $messages[] = ['role' => 'user', 'content' => 'Réponse invalide. Réponds UNIQUEMENT avec un objet JSON au format {"action":"tool",...} ou {"action":"final",...}, sans aucun texte autour.'];
                continue;
            }

            if (($decision['action'] ?? '') === 'final') {
                return $this->emitFinal($this->buildResponse(
                    (string) ($decision['message'] ?? ''),
                    is_array($decision['results'] ?? null) ? $decision['results'] : [],
                    $steps,
                    $itemMap,
                    $iteration
                ), $onEvent);
            }

            $tool = (string) ($decision['tool'] ?? '');
            $arguments = is_array($decision['arguments'] ?? null) ? $decision['arguments'] : [];
            $result = $this->tools->execute($tool, $arguments, $user);

            foreach ($result['items'] ?? [] as $item) {
                $itemMap[$item['type'] . ':' . $item['id']] = $item;
            }

            $step = [
                'tool' => $tool,
                'thought' => (string) ($decision['thought'] ?? ''),
                'arguments' => $arguments,
                'count' => $result['total'] ?? ($result['count'] ?? 0),
                'error' => $result['error'] ?? null,
            ];
            $steps[] = $step;

            if ($onEvent !== null) {
                $onEvent('step', $step);
            }

            Log::info('AI Agent: outil exécuté', ['iteration' => $iteration, 'tool' => $tool, 'arguments' => $arguments, 'count' => $result['count'] ?? 0, 'error' => $result['error'] ?? null]);

            $messages[] = ['role' => 'assistant', 'content' => json_encode($decision, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)];
            $messages[] = ['role' => 'user', 'content' => 'RÉSULTAT ' . $tool . ' : ' . json_encode($this->compactResult($result), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)];
        }

        // Budget épuisé : on restitue ce qui a été trouvé plutôt que d'échouer.
        $fallback = array_slice(array_values($itemMap), 0, self::MAX_ITEMS_IN_CONTEXT);
        $message = empty($fallback)
            ? "Je n'ai rien trouvé de concluant après plusieurs recherches. Essayez de reformuler (autre orthographe, autre période, autre type d'élément)."
            : 'Voici ce que mes recherches ont permis de trouver :';

        return $this->emitFinal([
            'success' => true,
            'response' => $message,
            'results' => $this->presentItems($fallback),
            'steps' => $steps,
            'iterations' => $maxIterations,
        ], $onEvent);
    }

    /**
     * Appelle le fournisseur IA et retente UNE fois, immédiatement, si la
     * réponse est une erreur provider identifiée (ex. rate limit). Les
     * exceptions réseau/config (clé invalide, DNS, timeout...) ne sont PAS
     * catchées ici : elles remontent à run(), qui journalise l'échec réel
     * (finishFailure) et le fait remonter à l'appelant plutôt que de le
     * déguiser en réponse générique.
     *
     * Pas de pause bloquante : run() est appelé de façon synchrone depuis un
     * contrôleur HTTP (pas une queue) — un usleep() retiendrait un worker
     * PHP-FPM, risque réel en cas de plusieurs utilisateurs simultanés.
     *
     * Retourne null si la réponse reste une erreur provider après une tentative.
     */
    private function chatWithRetry(string $providerName, string $model, array $messages): ?string
    {
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $response = AiBridge::provider($providerName)->chat($messages, ['model' => $model]);
            $text = ResponseTextExtractor::extract($response);

            if (!$this->isProviderError($text)) {
                return $text;
            }

            Log::warning('AI Agent: réponse provider en erreur', ['attempt' => $attempt, 'raw' => mb_substr($text, 0, 300)]);
        }

        return null;
    }

    /**
     * Détecte une réponse d'erreur brute du fournisseur (rate limit, panne...)
     * plutôt qu'une réponse JSON conforme au protocole de l'agent. Couvre les
     * formes réellement renvoyées par les providers connectés via AiBridge :
     * OpenAI/Mistral ({"error":{"message","type","code"}}) et Anthropic/Claude
     * ({"type":"error","error":{"type","message"}}).
     */
    private function isProviderError(string $text): bool
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            return false;
        }

        // OpenAI / Mistral : {"error": {"message": ..., "type": ..., "code": ...}}
        if (isset($decoded['error']) && is_array($decoded['error'])) {
            return true;
        }

        // Anthropic / Claude : {"type": "error", "error": {...}}
        if (($decoded['type'] ?? null) === 'error') {
            return true;
        }

        // Formats génériques observés (gateways, proxies) : object=error, ou
        // code HTTP explicite dans le corps de la réponse.
        return (isset($decoded['object']) && $decoded['object'] === 'error')
            || isset($decoded['raw_status_code'])
            || isset($decoded['status_code']);
    }

    private function emitFinal(array $response, ?callable $onEvent): array
    {
        if ($onEvent !== null) {
            $onEvent('final', $response);
        }

        return $response;
    }

    private function resolveProvider(): string
    {
        $provider = $this->registry->getSetting('ai_default_provider', 'ollama');
        if (is_string($provider)) {
            $provider = json_decode($provider, true) ?: $provider;
        }

        return is_string($provider) ? $provider : 'ollama';
    }

    private function resolveModel(string $providerName): string
    {
        return match ($providerName) {
            'mistral' => $this->registry->getSetting('mistral_default_model', 'mistral-large-latest'),
            'claude' => 'claude-3-5-sonnet-20241022',
            'openai' => 'gpt-4o',
            'ollama' => $this->registry->getSetting('ollama_default_model', 'llama3.1'),
            default => 'mistral-large-latest',
        };
    }

    private function systemPrompt(User $user, int $maxIterations): string
    {
        $todayDate = now()->toDateString();
        $todayHuman = now()->locale('fr')->translatedFormat('l j F Y');
        $userName = $user->name;
        $organisation = $user->organisation->name ?? 'aucune organisation';
        $role = $user->isSuperAdmin() ? 'superadministrateur (accès à toutes les organisations)' : "utilisateur de l'organisation « {$organisation} » (accès limité aux données de cette organisation)";

        $toolLines = [];
        foreach ($this->tools->definitions() as $name => $definition) {
            $args = [];
            foreach ($definition['arguments'] as $argName => $argDescription) {
                $args[] = "{$argName}: {$argDescription}";
            }
            $toolLines[] = "- {$name} — {$definition['description']}\n  arguments: {" . implode('; ', $args) . '}';
        }
        $toolsBlock = implode("\n", $toolLines);

        return <<<PROMPT
Tu es l'archiviste assistant de la plateforme Shelve (gestion d'archives, courriers, versements, communications).
Tu travailles pour {$userName}, {$role}. Les outils appliquent déjà ses droits d'accès : tu ne vois que ce qu'il a le droit de voir.
Date du jour : {$todayDate} ({$todayHuman}).

Tu procèdes PAR ÉTAPES, comme un vrai archiviste :
1. Tu choisis l'outil le plus pertinent et tu l'appelles.
2. Tu observes le résultat.
3. Si le résultat n'est pas concluant, tu relances : variantes d'orthographe (ex. Judite/Judith), autre outil, critères élargis ou restreints.
4. Quand tu as des résultats pertinents (ou après avoir vraiment épuisé les pistes), tu conclus.

OUTILS DISPONIBLES :
{$toolsBlock}

FORMAT DE RÉPONSE — réponds UNIQUEMENT avec un objet JSON, sans texte avant ni après, sans balises markdown :
- Pour appeler un outil, "action" vaut TOUJOURS le mot littéral "tool" (jamais le nom de l'outil) ; le nom de l'outil va dans le champ séparé "tool" :
{"action":"tool","tool":"nom_outil","arguments":{...},"thought":"une phrase en français expliquant cette étape (elle sera montrée à l'utilisateur)"}
  Exemple pour appeler search_records : {"action":"tool","tool":"search_records","arguments":{"query":"AR-2010-007"},"thought":"..."}
  INCORRECT (ne fais jamais ça) : {"action":"search_records",...} — "action" doit rester "tool".
- Pour donner la réponse finale :
{"action":"final","message":"réponse claire en français","results":[{"type":"mails","id":12},{"type":"contacts","id":3}]}

RÈGLES :
- UN SEUL objet JSON par réponse : un seul appel d'outil à la fois, jamais plusieurs objets à la suite. Tu verras le résultat avant de décider de l'étape suivante.
- Maximum {$maxIterations} appels d'outils au total : conclus dès que tu as ce qu'il faut.
- Pour une question sur une PERSONNE (email, téléphone, coordonnées), commence par search_contacts.
- Pour une question du type « propose/suggère des mots-clés ou une indexation pour... », utilise DIRECTEMENT suggest_indexing — ne fais jamais de recherche préalable dans les dossiers, mails ou versements pour ce type de question.
- Pour une question du type « propose/suggère une classification/un classement pour... », utilise DIRECTEMENT suggest_classification, sans recherche préalable.
- Dans "results", ne liste QUE des éléments réellement retournés par les outils (type et id exacts). Jamais d'élément inventé.
- Ne fabrique jamais d'information : si tu ne trouves rien, dis-le et propose des pistes.
- "message" est destiné à l'utilisateur final : concis, en français, et mentionne les informations clés trouvées (ex. l'adresse email demandée).
PROMPT;
    }

    private function parseDecision(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);

        $decoded = json_decode($clean, true);

        if (!is_array($decoded)) {
            // Le modèle peut renvoyer plusieurs objets JSON d'affilée ou du
            // texte parasite : on isole le PREMIER objet complet (équilibrage
            // des accolades) et on l'exécute ; la boucle enchaînera la suite.
            $decoded = $this->firstJsonObject($clean);
        }

        if (!is_array($decoded)) {
            return null;
        }

        // Auto-correction : le modèle met parfois le nom de l'outil directement
        // dans "action" (ex. {"action":"search_records",...}) au lieu du littéral
        // "tool" + champ "tool" séparé. On normalise plutôt que de rejeter.
        $action = $decoded['action'] ?? null;
        if (!in_array($action, ['tool', 'final'], true) && is_string($action) && array_key_exists($action, $this->tools->definitions())) {
            $decoded['tool'] = $decoded['tool'] ?? $action;
            $decoded['action'] = 'tool';
            $action = 'tool';
        }

        if (!in_array($action, ['tool', 'final'], true)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Extrait le premier objet JSON complet d'un texte (accolades équilibrées,
     * en ignorant celles contenues dans les chaînes).
     */
    private function firstJsonObject(string $text): ?array
    {
        $start = strpos($text, '{');
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escaped = false;

        for ($i = $start, $length = strlen($text); $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                } elseif ($char === '\\') {
                    $escaped = true;
                } elseif ($char === '"') {
                    $inString = false;
                }
                continue;
            }

            if ($char === '"') {
                $inString = true;
            } elseif ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    $decoded = json_decode(substr($text, $start, $i - $start + 1), true);

                    return is_array($decoded) ? $decoded : null;
                }
            }
        }

        return null;
    }

    /**
     * Version compacte d'un résultat d'outil pour limiter la taille du contexte.
     */
    private function compactResult(array $result): array
    {
        if (isset($result['error'])) {
            return ['error' => $result['error']];
        }

        $items = array_slice($result['items'] ?? [], 0, self::MAX_ITEMS_IN_CONTEXT);
        $items = array_map(function (array $item) {
            unset($item['url']);

            return array_filter($item, fn ($value) => $value !== null && $value !== '');
        }, $items);

        $compact = [
            'count' => $result['count'] ?? count($items),
            'items' => $items,
        ];

        // count_items renvoie un total/une répartition sans liste d'éléments.
        foreach (['total', 'message', 'breakdown'] as $key) {
            if (isset($result[$key])) {
                $compact[$key] = $result[$key];
            }
        }

        return $compact;
    }

    private function buildResponse(string $message, array $requestedResults, array $steps, array $itemMap, int $iterations, bool $success = true): array
    {
        $selected = [];
        foreach ($requestedResults as $reference) {
            if (!is_array($reference)) {
                continue;
            }
            $key = ($reference['type'] ?? '') . ':' . ($reference['id'] ?? '');
            if (isset($itemMap[$key])) {
                $selected[$key] = $itemMap[$key];
            }
        }

        return [
            'success' => $success,
            'response' => $message !== '' ? $message : 'Recherche terminée.',
            'results' => $this->presentItems(array_values($selected)),
            'steps' => $steps,
            'iterations' => $iterations,
        ];
    }

    /**
     * Met les éléments au format attendu par l'interface (cartes cliquables).
     */
    private function presentItems(array $items): array
    {
        return array_map(function (array $item) {
            $extras = array_filter([
                $item['email'] ?? null,
                $item['location'] ?? null,
                $item['file'] ?? null,
            ]);
            $description = trim(implode(' • ', array_filter([$item['description'] ?? null, ...$extras])));

            return [
                'type' => $item['type'],
                'id' => $item['id'],
                'title' => $item['title'],
                'code' => $item['code'] ?? null,
                'meta' => $extras ? implode(' • ', $extras) : null,
                'description' => $description,
                'url' => $item['url'] ?? null,
            ];
        }, $items);
    }
}
