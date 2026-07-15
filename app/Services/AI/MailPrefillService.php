<?php

namespace App\Services\AI;

use AiBridge\Facades\AiBridge;
use App\Models\Attachment;
use App\Models\MailAction;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Services\AttachmentTextExtractor;
use Illuminate\Support\Facades\Log;

/**
 * Propose un préremplissage de formulaire de courrier à partir du texte d'une
 * pièce jointe (PDF/image) déjà uploadée. Lecture seule : ne crée, ne modifie
 * aucune donnée — l'utilisateur reste seul décisionnaire de ce qui est soumis.
 */
class MailPrefillService
{
    private const TEXT_MAX_LENGTH = 4000;
    private const NO_TEXT = ['status' => 'no_text', 'message' => "Impossible d'extraire du texte de ce document. Veuillez remplir le formulaire manuellement."];

    public function __construct(
        private ProviderRegistry $registry,
        private AttachmentTextExtractor $extractor
    ) {
    }

    /**
     * @param string $context received|send|received_external|send_external
     */
    public function suggest(Attachment $attachment, string $context): array
    {
        $text = $this->resolveText($attachment);
        if ($text === null || trim($text) === '') {
            return self::NO_TEXT;
        }

        $typologies = MailTypology::query()->orderBy('name')->get(['id', 'name'])->toArray();
        $priorities = MailPriority::query()->orderBy('name')->get(['id', 'name'])->toArray();
        $actions = MailAction::query()->orderBy('name')->get(['id', 'name'])->toArray();

        $providerName = $this->resolveProvider();
        $model = $this->resolveModel($providerName);
        $this->registry->ensureConfigured($providerName);

        $messages = [
            ['role' => 'system', 'content' => $this->buildPrompt($typologies, $priorities, $actions, $context)],
            ['role' => 'user', 'content' => mb_substr($text, 0, self::TEXT_MAX_LENGTH)],
        ];

        try {
            $response = AiBridge::provider($providerName)->chat($messages, ['model' => $model]);
            $raw = ResponseTextExtractor::extract($response);
        } catch (\Throwable $e) {
            Log::warning('MailPrefillService: appel IA en échec', ['error' => $e->getMessage()]);

            return ['status' => 'error', 'message' => "Le service IA est momentanément indisponible. Merci de remplir le formulaire manuellement."];
        }

        $decoded = JsonExtractor::firstJsonObject($raw);
        if (!is_array($decoded)) {
            Log::warning('MailPrefillService: réponse IA non parsable', ['raw' => mb_substr((string) $raw, 0, 500)]);

            return ['status' => 'error', 'message' => "L'analyse n'a pas produit de résultat exploitable. Merci de remplir le formulaire manuellement."];
        }

        return [
            'status' => 'ok',
            'suggestions' => $this->validateSuggestions($decoded, $typologies, $priorities, $actions),
        ];
    }

    private function resolveText(Attachment $attachment): ?string
    {
        if (!empty($attachment->content_text)) {
            return $attachment->content_text;
        }

        $absolutePath = storage_path('app/public/' . $attachment->path);
        if (!is_file($absolutePath)) {
            return null;
        }

        return $this->extractor->extract($absolutePath, $attachment->mime_type, $attachment->name);
    }

    private function buildPrompt(array $typologies, array $priorities, array $actions, string $context): string
    {
        $typologyList = implode(', ', array_map(fn ($t) => "{$t['id']}:{$t['name']}", $typologies));
        $priorityList = implode(', ', array_map(fn ($p) => "{$p['id']}:{$p['name']}", $priorities));
        $actionList = implode(', ', array_map(fn ($a) => "{$a['id']}:{$a['name']}", $actions));

        $isExternal = str_contains($context, 'external');
        $senderNote = $isExternal
            ? "Ce courrier est forcément avec un contact ou une organisation externe (pas un utilisateur interne)."
            : "L'expéditeur ou destinataire peut être interne (utilisateur de la plateforme) ou externe.";

        return <<<PROMPT
Tu es un assistant qui analyse un document (courrier scanné ou électronique) pour préremplir un formulaire d'archivage.
{$senderNote}

Listes RÉELLES disponibles (tu dois recopier exactement un id de ces listes, jamais en inventer un) :
- Typologies : {$typologyList}
- Priorités : {$priorityList}
- Actions : {$actionList}

Réponds UNIQUEMENT avec un objet JSON, sans texte autour, sans balises markdown, au format :
{"name":"objet du courrier résumé en une phrase","date":"AAAA-MM-JJ ou null si absente","typology_id":id ou null,"priority_id":id ou null,"action_id":id ou null,"sender_name":"nom de la personne ou organisation à l'origine du document, tel qu'il apparaît, ou null","sender_kind":"internal ou external ou null si incertain","confidence_note":"une phrase courte en français sur la fiabilité de l'extraction"}

Règles :
- N'invente jamais un id qui n'est pas dans les listes ci-dessus.
- Si une information n'est pas clairement présente dans le texte, mets null plutôt que de deviner.
- "date" est la date du document lui-même (pas la date du jour).
PROMPT;
    }

    private function validateSuggestions(array $decoded, array $typologies, array $priorities, array $actions): array
    {
        $typologyIds = array_column($typologies, 'name', 'id');
        $priorityIds = array_column($priorities, 'name', 'id');
        $actionIds = array_column($actions, 'name', 'id');

        return [
            'name' => is_string($decoded['name'] ?? null) ? trim($decoded['name']) : null,
            'date' => $this->validDate($decoded['date'] ?? null),
            'typology_id' => $this->whitelisted($decoded['typology_id'] ?? null, $typologyIds),
            'typology_label' => $typologyIds[$decoded['typology_id'] ?? null] ?? null,
            'priority_id' => $this->whitelisted($decoded['priority_id'] ?? null, $priorityIds),
            'priority_label' => $priorityIds[$decoded['priority_id'] ?? null] ?? null,
            'action_id' => $this->whitelisted($decoded['action_id'] ?? null, $actionIds),
            'action_label' => $actionIds[$decoded['action_id'] ?? null] ?? null,
            'sender_name' => is_string($decoded['sender_name'] ?? null) ? trim($decoded['sender_name']) : null,
            'sender_kind' => in_array($decoded['sender_kind'] ?? null, ['internal', 'external'], true) ? $decoded['sender_kind'] : null,
            'confidence_note' => is_string($decoded['confidence_note'] ?? null) ? trim($decoded['confidence_note']) : null,
        ];
    }

    private function whitelisted(mixed $id, array $validIds): ?int
    {
        $id = is_numeric($id) ? (int) $id : null;

        return ($id !== null && array_key_exists($id, $validIds)) ? $id : null;
    }

    private function validDate(mixed $date): ?string
    {
        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        return $date;
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
}
