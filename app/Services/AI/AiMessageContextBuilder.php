<?php

namespace App\Services\AI;

use Illuminate\Http\Request;
use App\Models\Prompt;
use App\Models\Record;
use App\Models\Activity;

class AiMessageContextBuilder
{
    private AiRecordContextBuilder $records;
    private DefaultValueService $defaultValues;

    /**
     * Prépare les messages et documents pour le résumé AI d'un mail
     */
    public function buildMailSummaryMessages($mail, string $systemPrompt = ''): array
    {
        $ATTACH_LABEL = "Pièce jointe: ";
        $userContent = '';
        $userContent .= "Expéditeur : " . ($mail->sender->name ?? $mail->externalSender->first_name ?? $mail->externalSenderOrganization->name ?? 'N/A') . "\n";
        $userContent .= "Destinataire : " . ($mail->recipient->name ?? $mail->externalRecipient->first_name ?? $mail->externalRecipientOrganization->name ?? 'N/A') . "\n";
        $userContent .= "Date : " . ($mail->date ? \Carbon\Carbon::parse($mail->date)->format('d/m/Y') : 'N/A') . "\n";
        $userContent .= "Typologie : " . ($mail->typology->name ?? 'N/A') . "\n";
        $userContent .= "Priorité : " . ($mail->priority->name ?? 'N/A') . "\n";
        $userContent .= "Action : " . ($mail->action->name ?? 'N/A') . "\n";
        $userContent .= "Objet : " . ($mail->name ?? $mail->code ?? 'N/A') . "\n";
        $userContent .= "Description : " . ($mail->description ?? '') . "\n";

        $documents = [];
        $attachmentsContent = [];
        foreach ($mail->attachments as $attachment) {
            $mime = $attachment->mime_type ?? '';
            $filePath = storage_path('app/' . $attachment->path);
            $doc = [
                'name' => $attachment->name,
                'mime_type' => $mime,
                'path' => $filePath,
            ];
            if (!empty($attachment->content_text)) {
                $doc['content'] = $attachment->content_text;
                $attachmentsContent[] = $ATTACH_LABEL . $attachment->name . "\nContenu extrait:\n" . mb_substr($attachment->content_text, 0, 2000);
            } elseif (str_starts_with($mime, 'text/')) {
                $text = @file_get_contents($filePath);
                if ($text) {
                    $doc['content'] = mb_substr($text, 0, 2000);
                    $attachmentsContent[] = $ATTACH_LABEL . $attachment->name . "\nContenu:\n" . mb_substr($text, 0, 2000);
                } else {
                    $attachmentsContent[] = $ATTACH_LABEL . $attachment->name . " (texte non lisible)";
                }
            } elseif ($mime === 'application/pdf') {
                $attachmentsContent[] = "Pièce jointe PDF: " . $attachment->name . " (extraction non implémentée)";
            } else {
                $attachmentsContent[] = $ATTACH_LABEL . $attachment->name;
            }
            $documents[] = $doc;
        }
        if (count($attachmentsContent)) {
            $userContent .= "\n--- Pièces jointes ---\n" . implode("\n\n", $attachmentsContent);
        }

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userContent],
            ['documents' => $documents],
        ];
    }
    // ...existing code...

    /**
     * Options AI pour le résumé de mail (peut être utilisé par le controller)
     */
    public function buildMailSummaryOptions($provider, $model): array
    {
        return [
            'provider' => $provider,
            'model' => $model,
            'max_tokens' => 350,
            'temperature' => 0.3,
            'timeout' => $this->defaultValues->getRequestTimeout() * 1000,
        ];
    }

    /**
     * Constructeur
     */
    public function __construct(?AiRecordContextBuilder $records = null, ?DefaultValueService $defaultValues = null)
    {
        $this->records = $records ?? new AiRecordContextBuilder();
        $this->defaultValues = $defaultValues ?? app(DefaultValueService::class);
    }

    public function build(Prompt $prompt, Request $request): array
    {
        $action = (string) $request->string('action');
        $context = (array) $request->input('context', []);
        $system = (!empty($prompt->is_system)) ? ($prompt->content ?? '') : '';

        $messages = [];
        if ($system) { $messages[] = ['role' => 'system', 'content' => $system]; }

        $effectiveAction = $this->normalizeAction($action);
        $context = $this->autoBuildContext($effectiveAction, $request, $context);
        $messages[] = ['role' => 'user', 'content' => $this->buildUserContent($effectiveAction, $context)];

        $options = $this->buildActionOptions($effectiveAction, $request);
        return [$messages, $options];
    }

    public function extractText($res): string
    {
        return ResponseTextExtractor::extract($res);
    }

    private function normalizeAction(string $action): string
    { return in_array($action, ['index_thesaurus','index_theaurus'], true) ? 'assign_thesaurus' : $action; }

    private function autoBuildContext(string $effectiveAction, Request $request, array $context): array
    {
        $entity = (string) $request->string('entity');
        $context = $this->applySummarizeContext($effectiveAction, $entity, $request, $context);
        $context = $this->applyThesaurusContext($effectiveAction, $entity, $request, $context);
        $context = $this->applyAssignActivityContext($effectiveAction, $entity, $request, $context);
        $context = $this->applyReformulateTitleContext($effectiveAction, $entity, $request, $context);
        return $context;
    }

    private function applySummarizeContext(string $action, string $entity, Request $request, array $ctx): array
    {
        if ($action === 'summarize' && empty($ctx['text']) && $entity === 'record') {
            $ctx['text'] = $this->records->buildRecordsSummaryText((array)$request->input('entity_ids', []));
        }
        return $ctx;
    }

    private function applyThesaurusContext(string $action, string $entity, Request $request, array $ctx): array
    {
        if ($action === 'assign_thesaurus' && empty($ctx['text']) && $entity === 'record') {
            $ctx['text'] = $this->records->buildRecordsThesaurusText((array)$request->input('entity_ids', []));
        }
        return $ctx;
    }

    private function applyAssignActivityContext(string $action, string $entity, Request $request, array $ctx): array
    {
        if ($action === 'assign_activity') {
            if (empty($ctx['activities'])) { $ctx['activities'] = $this->records->buildActivitiesListText(); }
            if (empty($ctx['context']) && $entity === 'record') {
                $ctx['context'] = $this->records->buildRecordsActivityContext((array)$request->input('entity_ids', []));
            }
        }
        return $ctx;
    }

    private function applyReformulateTitleContext(string $action, string $entity, Request $request, array $ctx): array
    {
        if ($action === 'reformulate_title' && empty($ctx['title']) && $entity === 'record') {
            $ids = array_values(array_filter(array_map('intval', (array)$request->input('entity_ids', [])), fn($v) => $v > 0));
            if (!empty($ids)) {
                $rec = Record::query()->where('id', $ids[0])->first(['id','name']);
                if ($rec && !empty($rec->name)) { $ctx['title'] = (string) $rec->name; }
            }
        }
        return $ctx;
    }

    public function buildActionOptions(string $effectiveAction, Request $request): array
    {
        $provRaw = $request->input('model_provider');
        $modRaw = $request->input('model');
        $prov = $this->defaultValues->getValidatedProvider($provRaw);
        $mod = $this->defaultValues->getDefaultModel($modRaw);
        $timeoutMs = $this->getTimeoutMs();
        $options = ['provider' => $prov, 'model' => $mod, 'timeout' => $timeoutMs];
        return match ($effectiveAction) {
            'assign_thesaurus' => $options + ['max_tokens' => 300, 'temperature' => 0.2],
            'assign_activity' => $options + ['max_tokens' => 180, 'temperature' => 0.1],
            'summarize' => $options + ['max_tokens' => 350, 'temperature' => 0.3],
            'reformulate_title' => $options + ['max_tokens' => 60, 'temperature' => 0.2],
            'keywords' => $options + ['max_tokens' => 250, 'temperature' => 0.2],
            default => $options,
        };
    }

    private function buildUserContent(string $effectiveAction, array $context): string
    {
        $tpl = Prompt::where('title', 'action.' . $effectiveAction . '.user')->first();
        if ($tpl && !empty($tpl->content)) { return $this->renderActionTemplate($tpl->content, $effectiveAction, $context); }
        return $this->fallbackUserPrompt($effectiveAction, $context);
    }

    private function getTimeoutMs(): int
    {
        return $this->defaultValues->getRequestTimeout() * 1000;
    }

    private function renderActionTemplate(string $template, string $action, array $ctx): string
    {
        $vars = [];
        switch ($action) {
            case 'reformulate_title':
                $vars['title'] = (string) ($ctx['title'] ?? '');
                break;
            case 'summarize':
                $vars['text'] = (string) ($ctx['text'] ?? '');
                break;
            case 'assign_activity':
                $vars['activities'] = (string) ($ctx['activities'] ?? '');
                $vars['context'] = (string) ($ctx['context'] ?? '');
                break;
            case 'assign_thesaurus':
                $vars['pref_labels'] = implode(', ', array_map('strval', (array)($ctx['pref_labels'] ?? [])));
                $vars['text'] = (string) ($ctx['text'] ?? '');
                break;
            case 'summarize_slip':
                $items = (array)($ctx['slip_records'] ?? []);
                $vars['slip_records'] = substr(json_encode($items, JSON_UNESCAPED_UNICODE), 0, 4000);
                break;
            default:
                break;
        }
        $replacements = [];
        foreach ($vars as $k => $v) { $replacements['{{'.$k.'}}'] = $v; }
        return strtr($template, $replacements);
    }

    private function fallbackUserPrompt(string $action, array $ctx): string
    {
        return match ($action) {
            'reformulate_title' =>
                "Reformule l'intitulé archivistique ci-dessous en respectant strictement ces règles (FR) :\n" .
                "- Utilise le point-tiret (. —) pour séparer l'objet principal du reste (préféré).\n" .
                "- Virgule : données de même niveau ; point-virgule : éléments d'analyse de même nature ; deux points : typologie ; point : termine une sous-partie.\n" .
                "- 1 objet : Objet. — Action : typologie documentaire. Dates extrêmes\n" .
                "- 2 objets : Objet. — Action (dates). Autre action (dates). Dates extrêmes\n" .
                "- ≥3 objets : Objet principal. — Objet secondaire : typologie (dates). Autre objet secondaire : typologie (dates). Dates extrêmes\n" .
                "- Mets en facteur commun ce qui peut l'être, du général vers le particulier ; évite 'Idem'.\n" .
                "- Mots-outils possibles : avec, dont, contient, concerne, en particulier, notamment, aussi, ne concerne que.\n" .
                "- N'invente rien ; conserve les dates existantes et place-les en fin comme dates extrêmes si pertinent.\n\n" .
                "Contraintes de sortie :\n" .
                "- Une seule ligne, claire et concise ; renvoie uniquement le nouveau titre, sans guillemets ni commentaires.\n\n" .
                "Intitulé d'origine :\n" . ((string)($ctx['title'] ?? '')),
            'summarize' =>
                "À partir du texte suivant :\n" .
                "1) Fournis un résumé en 3 à 5 phrases (FR), concis et fidèle.\n" .
                "2) Puis extrais 5 mots-clés, chacun avec 3 synonymes (FR), et catégorise-les :\n" .
                "   - P (Personnalité) : objet principal / essence\n" .
                "   - M (Matière) : composants / éléments\n" .
                "   - E (Énergie) : actions / processus / fonctions\n" .
                "   - E (Espace) : localisation géographique ou spatiale\n" .
                "Format :\n" .
                "Résumé : <résumé>\n" .
                "Mots-clés (5) :\n" .
                "- [Catégorie] Mot-clé — synonymes : s1; s2; s3\n\n" .
                ((string)($ctx['text'] ?? '')),
            'assign_activity' =>
                "Voici la liste complète des activités disponibles (id | code | name), une par ligne :\n" .
                ((string)($ctx['activities'] ?? '')) . "\n\n" .
                "Contexte (si présent) :\n" . ((string)($ctx['context'] ?? '')) . "\n\n" .
                "Tâche : choisis l'activité la plus pertinente (selected) et propose une alternative (alternative).\n" .
                "Réponds STRICTEMENT en JSON valide (sans texte additionnel) avec ce schéma :\n" .
                "{\n  \"selected\": { \"id\": <int|null>, \"code\": <string|null>, \"name\": <string|null> },\n  \"alternative\": { \"id\": <int|null>, \"code\": <string|null>, \"name\": <string|null> },\n  \"confidence\": <number 0..1>,\n  \"reason\": <string court en FR>\n}\n",
            'assign_thesaurus' => isset($ctx['text']) && $ctx['text'] !== ''
                    ? "À partir du contenu ci-dessous, propose 5 à 10 libellés préférentiels (FR) pertinents du thésaurus.\n" .
                        "Pour chaque ligne :\n" .
                        "- Indique une catégorie entre crochets : P (Personnalité), M (Matière), En (Énergie), Es (Espace).\n" .
                        "- Donne le libellé principal (prefLabel) puis 1 à 3 synonymes séparés par des points-virgules.\n" .
                        "- Évite les doublons et les termes trop généraux.\n" .
                        "Format :\n" .
                        "- [Catégorie] Libellé — synonymes : s1; s2; s3\n\n" . (string)$ctx['text']
                    : "Propose des libellés pertinents à partir de: " . implode(', ', array_map('strval', (array)($ctx['pref_labels'] ?? []))) . ".",
            'summarize_slip' => "Génère un résumé synthétique de ces slips:\n\n" . substr(json_encode((array)($ctx['slip_records'] ?? []), JSON_UNESCAPED_UNICODE), 0, 4000),
            default => 'Provide assistance based on the given context.',
        };
    }

    // Delegated record helpers moved to AiRecordContextBuilder
// ...existing code...
}
