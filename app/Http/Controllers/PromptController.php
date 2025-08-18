<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Prompt;
use App\Models\Activity;
use App\Models\Record;
use App\Models\ThesaurusConcept;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use App\Services\AI\PromptTransactionService;
use Illuminate\Support\Facades\DB;
use AiBridge\Facades\AiBridge;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Services\AI\ProviderRegistry;

class PromptController extends Controller
{
    public function __construct(
        private PromptTransactionService $tx,
        private ProviderRegistry $providers
    ) {}


    /**
     * List prompts with optional filters.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $q = Prompt::query();
        if ($request->filled('is_system')) { $q->where('is_system', $request->boolean('is_system')); }
        if ($request->filled('organisation_id')) { $q->where('organisation_id', $request->integer('organisation_id')); }
        if ($request->filled('user_id')) { $q->where('user_id', $request->integer('user_id')); }
        return response()->json($q->orderByDesc('id')->paginate(20));
    }




    /**
     * Show a specific prompt by ID.
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $row = Prompt::find($id);
        if (!$row) { return response()->json(['message' => 'Not found'], 404); }
        return response()->json($row);
    }




    /**
     * Perform an action using the specified prompt.
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function actions(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:reformulate_title,summarize,assign_activity,assign_thesaurus,index_thesaurus,index_theaurus,summarize_slip',
            'entity' => 'required|string|in:record,mail,communication,slip_record',
            'entity_ids' => 'required|array|min:1',
            'entity_ids.*' => 'integer|min:1',
            'context' => 'sometimes|array',
            'model' => 'sometimes|string',
            'model_provider' => 'sometimes|string|in:ollama,openai,gemini,claude,openrouter,onn,ollama_turbo,openai_custom',
            'confirm' => 'sometimes|boolean'
        ]);
        $response = null;
        if ($validator->fails()) {
            $response = response()->json(['errors' => $validator->errors()], 422);
        } else {
            $prompt = Prompt::find($id);
            if (!$prompt) {
                $response = response()->json(['message' => 'Prompt not found'], 404);
            } else {
                $user = Auth::user();
                $orgId = $prompt->organisation_id ?? ($user->current_organisation_id ?? null);

                $txId = $this->tx->start([
                    'prompt_id' => $prompt->id,
                    'organisation_id' => $orgId,
                    'user_id' => $user?->id,
                    'entity' => $request->string('entity')->toString(),
                    'entity_ids' => $request->input('entity_ids', []),
                    'model' => $request->input('model'),
                    'model_provider' => $request->input('model_provider', 'ollama'),
                ]);

                $t0 = (int) (microtime(true) * 1000);
                try {
                    [$messages, $options] = $this->buildMessagesAndOptions($prompt, $request);
                    $providerName = $options['provider'] ?? 'ollama';
                    // Normalize provider name to avoid quoted inputs like "ollama"
                    $providerName = strtolower(trim(trim($providerName, " \t\n\r\0\x0B'\"")));
                    $model = $options['model'] ?? config('ollama-laravel.model', 'gemma3:4b');
                    $opts = $options; $opts['model'] = $model;

                    // Execute chat using AiBridge provider (with optional streaming)
                    $this->providers->ensureConfigured($providerName);
                    [$text, $usage] = $this->executeAi($providerName, $messages, $opts, $request->boolean('stream'));

                    $t1 = (int) (microtime(true) * 1000);
                    $this->tx->finishSuccess($txId, [
                        'latency_ms' => max(0, $t1 - $t0),
                        'tokens_input' => $usage['prompt_tokens'] ?? null,
                        'tokens_output' => $usage['completion_tokens'] ?? null,
                    ]);

                    $response = response()->json([
                        'status' => 'ok',
                        'transaction_id' => $txId,
                        'output' => $text,
                        'usage' => $usage,
                    ]);
                } catch (\Throwable $e) {
                    $t1 = (int) (microtime(true) * 1000);
                    $this->tx->finishFailure($txId, $e->getMessage(), max(0, $t1 - $t0));
                    Log::error('Prompt action failed', ['tx' => $txId, 'ex' => $e]);
                    $response = response()->json([
                        'status' => 'error',
                        'transaction_id' => $txId,
                        'message' => $e->getMessage() ?: 'AI error',
                    ], ($e instanceof AiProviderNotConfiguredException) ? 422 : 500);
                }
            }
        }
        return $response;
    }





    /**
     * Execute chat against AiBridge provider with optional streaming.
     * @return array{0:string,1:array|null} [text, usage]
     */
    private function executeAi(string $providerName, array $messages, array $opts, bool $stream): array
    {
        $provider = AiBridge::provider($providerName);
        if (!$provider) {
            // Try to register on the fly
            try { $this->providers->ensureConfigured($providerName); } catch (\Throwable) { /* ignore */ }
            $provider = AiBridge::provider($providerName);
            if (!$provider) {
                throw new AiProviderNotConfiguredException("AI provider '{$providerName}' not configured.");
            }
        }

        // Extract simple roles from our limited usage (optional system + one user)
        $systemContent = '';
        $userContents = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? null; $content = $m['content'] ?? '';
            if ($role === 'system' && $content) { $systemContent = (string) $content; }
            elseif ($role === 'user' && $content) { $userContents[] = (string) $content; }
        }

        $model = $opts['model'] ?? null;
        $text = '';
        $usage = null;

        // Try new explicit builder-style API if available
        try {
            $builder = null;
            if (method_exists($provider, 'newChat')) {
                $builder = $provider->newChat();
            } elseif (method_exists($provider, 'chatRequest')) {
                $builder = $provider->chatRequest();
            } elseif (method_exists($provider, 'chatBuilder')) {
                $builder = $provider->chatBuilder();
            }

            if ($builder) {
                // Model
                if ($model && method_exists($builder, 'model')) { $builder = $builder->model($model); }

                // Common options if supported (best-effort)
                $map = [
                    'temperature' => ['temperature','setTemperature'],
                    'top_p' => ['topP','setTopP'],
                    'top_k' => ['topK','setTopK'],
                    'repeat_penalty' => ['repeatPenalty','setRepeatPenalty'],
                ];
                foreach ($map as $optKey => $methods) {
                    if (isset($opts[$optKey])) {
                        foreach ($methods as $meth) {
                            if (method_exists($builder, $meth)) { $builder = $builder->{$meth}($opts[$optKey]); break; }
                        }
                    }
                }

                // Messages
                if ($systemContent && method_exists($builder, 'system')) { $builder = $builder->system($systemContent); }
                foreach ($userContents as $uc) {
                    if (method_exists($builder, 'user')) { $builder = $builder->user($uc); }
                    elseif (method_exists($builder, 'message')) { $builder = $builder->message('user', $uc); }
                }

                // Streaming path if available
                if ($stream) {
                    if (method_exists($builder, 'sendStream')) {
                        foreach ($builder->sendStream() as $chunk) {
                            if (is_array($chunk)) {
                                $text .= $chunk['delta'] ?? ($chunk['content'] ?? json_encode($chunk));
                            } else {
                                $text .= (string) $chunk;
                            }
                        }
                        return [$text, $usage];
                    }
                    if (method_exists($builder, 'enableStreaming')) { $builder = $builder->enableStreaming(); }
                }

                if (method_exists($builder, 'send')) {
                    $res = $builder->send();
                    // Try to normalize output/usage
                    if (is_array($res)) {
                        $text = $this->extractText($res);
                        $usage = $res['usage'] ?? null;
                    } elseif (is_string($res)) {
                        $text = $res;
                    } elseif (is_object($res)) {
                        $text = (string) ($res->content ?? '');
                        $usage = $res->usage ?? null;
                    }
                    return [$text, $usage];
                }
            }
        } catch (\Throwable $e) {
            // Fall back to legacy array-based API below
            Log::warning('AiBridge new API path failed, falling back to legacy', ['ex' => $e->getMessage()]);
        }

        // Legacy path (arrays)
        if ($stream && method_exists($provider, 'supportsStreaming') && $provider->supportsStreaming()) {
            foreach ($provider->stream($messages, $opts) as $chunk) {
                if (is_array($chunk)) {
                    $text .= $chunk['delta'] ?? ($chunk['content'] ?? json_encode($chunk));
                } else {
                    $text .= (string) $chunk;
                }
            }
        } else {
            $res = $provider->chat($messages, $opts);
            $text = $this->extractText($res);
            $usage = $res['usage'] ?? null;
        }
        return [$text, $usage];
    }




    /**
     * Builds messages and options for the AI request based on the prompt and request data.
     * Returns an array of messages and options.
     */
    private function buildMessagesAndOptions(object $prompt, Request $request): array
    {
    $action = $request->string('action')->toString();
    $context = $request->input('context', []);
    $system = (!empty($prompt->is_system)) ? ($prompt->content ?? '') : '';

        $messages = [];
        if ($system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }

        // Normalize alias for thesaurus indexing button
        $effectiveAction = in_array($action, ['index_thesaurus','index_theaurus'], true) ? 'assign_thesaurus' : $action;

        // If summarize and no user text provided, build it from records
        if ($effectiveAction === 'summarize' && empty($context['text']) && $request->string('entity')->toString() === 'record') {
            $ids = (array) $request->input('entity_ids', []);
            $context['text'] = $this->buildRecordsSummaryText($ids);
        }
        // If thesaurus indexing and no text provided, build from records too
        if ($effectiveAction === 'assign_thesaurus' && empty($context['text']) && $request->string('entity')->toString() === 'record') {
            $ids = (array) $request->input('entity_ids', []);
            $context['text'] = $this->buildRecordsThesaurusText($ids);
        }
        // If activity assignment, provide full activities list (id, code, name) and optional record context
        if ($effectiveAction === 'assign_activity') {
            if (empty($context['activities'])) {
                $context['activities'] = $this->buildActivitiesListText();
            }
            if (empty($context['context']) && $request->string('entity')->toString() === 'record') {
                $ids = (array) $request->input('entity_ids', []);
                $context['context'] = $this->buildRecordsActivityContext($ids);
            }
        }
        // If title reformulation and no title provided, fetch record name as source title
        if ($effectiveAction === 'reformulate_title' && empty($context['title']) && $request->string('entity')->toString() === 'record') {
            $ids = array_values(array_filter(array_map('intval', (array)$request->input('entity_ids', [])), fn($v) => $v > 0));
            if (!empty($ids)) {
                $rec = Record::query()->where('id', $ids[0])->first(['id','name']);
                if ($rec && !empty($rec->name)) { $context['title'] = (string) $rec->name; }
            }
        }

        // Load user message template from DB by action title (e.g., action.reformulate_title.user)
        $templateTitle = 'action.' . $effectiveAction . '.user';
        $tpl = Prompt::where('title', $templateTitle)->first();

        if ($tpl && !empty($tpl->content)) {
            $userContent = $this->renderActionTemplate($tpl->content, $effectiveAction, $context);
        } else {
            // Fallback to minimal inline prompts if template not found
            $userContent = $this->fallbackUserPrompt($effectiveAction, $context);
        }
        $messages[] = ['role' => 'user', 'content' => $userContent];

    $defaultProvider = app(\App\Services\SettingService::class)->get('ai_default_provider', 'ollama');
    $defaultModel = app(\App\Services\SettingService::class)->get('ai_default_model', config('ollama-laravel.model', 'gemma3:4b'));
        // Normalize provider/model values (remove quotes/whitespace)
        $provRaw = $request->input('model_provider', $defaultProvider);
        $modRaw = $request->input('model', $defaultModel);
        $prov = strtolower(trim(trim((string)$provRaw, " \t\n\r\0\x0B'\"")));
        $mod = trim(trim((string)$modRaw, " \t\n\r\0\x0B'\""));
        $options = [
            'provider' => $prov ?: 'ollama',
            'model' => $mod ?: config('ollama-laravel.model', 'gemma3:4b'),
        ];
        // Conservative generation defaults to avoid timeouts for heavy contexts
        if ($effectiveAction === 'assign_thesaurus') {
            $options['max_tokens'] = 300;
            $options['temperature'] = 0.2;
            $options['timeout'] = 60000; // 60s for indexing
        } elseif ($effectiveAction === 'assign_activity') {
            $options['max_tokens'] = 180;
            $options['temperature'] = 0.1;
            $options['timeout'] = 30000; // JSON selection should be quick
        } elseif ($effectiveAction === 'summarize') {
            $options['max_tokens'] = 350; // allow room for 5 keywords with synonyms
            $options['temperature'] = 0.3;
            $options['timeout'] = 40000; // 40s to accommodate extra output
        } elseif ($effectiveAction === 'reformulate_title') {
            $options['max_tokens'] = 60;
            $options['temperature'] = 0.2;
            $options['timeout'] = 25000; // titles are short
        } else {
            $options['timeout'] = 30000;
        }
        return [$messages, $options];
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
                // no variables
                break;
        }
        // Replace {{var}} placeholders
        $replacements = [];
        foreach ($vars as $k => $v) { $replacements['{{'.$k.'}}'] = $v; }
        return strtr($template, $replacements);
    }





    /**
     * Fallback user prompt for common actions.
     * Returns a simple prompt based on the action and context.
     */
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




    /**
     * Extracts text content from various response formats.
     * Handles different structures to ensure we get the main text.
     */
    private function extractText(array $res): string
    {
        $text = null;
        if (!empty($res['content'])) {
            $text = is_array($res['content']) ? implode("", $res['content']) : (string)$res['content'];
        } elseif (!empty($res['message']['content'])) {
            $text = (string)$res['message']['content'];
        } elseif (!empty($res['choices'][0]['message']['content'])) {
            $text = (string)$res['choices'][0]['message']['content'];
        } else {
            $text = json_encode($res);
        }
        return $text;
    }

    /**
     * Builds a per-line text list of all activities: "id | code | name".
     */
    private function buildActivitiesListText(): string
    {
        $rows = Activity::query()->orderBy('id')->get(['id','code','name']);
        $lines = [];
        foreach ($rows as $r) {
            $lines[] = $r->id . ' | ' . (string)($r->code ?? '') . ' | ' . (string)($r->name ?? '');
        }
        return implode("\n", $lines);
    }

    /**
     * Builds a short context from records to help choose an activity.
     */
    private function buildRecordsActivityContext(array $ids): string
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (empty($ids)) { return ''; }
        $recs = Record::query()->whereIn('id', $ids)->get(['id','name','content','activity_id']);
        $buf = [];
        foreach ($recs as $r) {
            $name = trim((string)($r->name ?? ''));
            $content = trim((string)($r->content ?? ''));
            if ($content !== '') { $content = mb_substr(preg_replace('/\s+/u',' ', $content), 0, 300); }
            $buf[] = "Record #{$r->id}: " . ($name !== '' ? $name : '[sans titre]') . ($content !== '' ? " — " . $content : '');
        }
        return implode("\n", $buf);
    }

    /**
     * Construit un texte à partir des données des records pour permettre un résumé par l'IA.
     * Inclut les champs clés si présents et tronque proprement les champs très longs.
     */
    private function buildRecordsSummaryText(array $ids): string
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (empty($ids)) { return ''; }

        $records = Record::query()
            ->with([
                'activity:id,name',
                'level:id,name',
                'support:id,name',
                'status:id,name',
                'authors:id,name',
                'thesaurusConcepts' => function ($q) {
                    $q->with(['labels' => function ($q2) {
                        $q2->where('type', 'prefLabel')->where('language', 'fr-fr');
                    }]);
                },
            ])
            ->whereIn('id', $ids)
            ->get([
                'id','code','name','date_format','date_start','date_end','date_exact',
                'biographical_history','archival_history','acquisition_source','content','appraisal','accrual','arrangement',
                'access_conditions','reproduction_conditions','language_material','characteristic','finding_aids',
                'location_original','location_copy','related_unit','publication_note','note','archivist_note','rule_convention'
            ]);

        $parts = [];
        foreach ($records as $r) {
            $lines = [];
            $lines[] = 'Enregistrement #' . $r->id;
            if (!empty($r->name)) { $lines[] = 'Titre: ' . $r->name; }
            if (!empty($r->code)) { $lines[] = 'Cote: ' . $r->code; }
            $dateStr = '';
            if (!empty($r->date_exact) && $r->date_exact && !empty($r->date_start)) {
                $dateStr = (string)$r->date_start;
            } else {
                $start = $r->date_start ? (string)$r->date_start : '';
                $end = $r->date_end ? (string)$r->date_end : '';
                if ($start || $end) { $dateStr = trim($start . ' - ' . $end, ' -'); }
            }
            if ($dateStr) { $lines[] = 'Dates: ' . $dateStr; }

            // Données liées (courtes)
            if ($r->activity?->name) { $lines[] = 'Activité: ' . $r->activity->name; }
            if ($r->level?->name) { $lines[] = 'Niveau: ' . $r->level->name; }
            if ($r->support?->name) { $lines[] = 'Support: ' . $r->support->name; }
            if ($r->status?->name) { $lines[] = 'Statut: ' . $r->status->name; }
            if ($r->authors && $r->authors->count()) {
                $lines[] = 'Auteurs: ' . implode(', ', $r->authors->pluck('name')->filter()->take(8)->all());
            }
            if ($r->thesaurusConcepts && $r->thesaurusConcepts->count()) {
                $labels = [];
                foreach ($r->thesaurusConcepts as $c) {
                    $lbl = $c->labels->first();
                    if ($lbl && !empty($lbl->literal_form)) { $labels[] = $lbl->literal_form; }
                    if (count($labels) >= 12) { break; }
                }
                if ($labels) { $lines[] = 'Thésaurus: ' . implode(', ', $labels); }
            }

            $longFields = [
                'biographical_history' => 'Historique biographique',
                'archival_history' => 'Historique de la conservation',
                'acquisition_source' => "Source d'acquisition",
                'content' => 'Contenu',
                'appraisal' => 'Évaluation',
                'accrual' => 'Accroissements',
                'arrangement' => 'Mode de classement',
                'access_conditions' => "Conditions d'accès",
                'reproduction_conditions' => 'Reproduction',
                'language_material' => 'Langue(s)',
                'characteristic' => 'Caractéristiques matérielles',
                'finding_aids' => 'Instruments de recherche',
                'location_original' => 'Localisation originaux',
                'location_copy' => 'Localisation copies',
                'related_unit' => 'Unités liées',
                'publication_note' => 'Publication',
                'note' => 'Note',
                'archivist_note' => "Note de l'archiviste",
                'rule_convention' => 'Règles et conventions',
            ];
            foreach ($longFields as $field => $label) {
                $val = (string) ($r->{$field} ?? '');
                if ($val !== '') { $lines[] = $label . ': ' . $this->softTrim($val, 1200); }
            }

            $parts[] = implode("\n", $lines);
        }

        return implode("\n\n---\n\n", $parts);
    }

    /**
     * Tronque proprement une chaîne à ~N caractères en respectant des limites de phrase si possible.
     */
    private function softTrim(string $text, int $max): string
    {
        $text = trim($text);
        if (mb_strlen($text) <= $max) { return $text; }
        $snippet = mb_substr($text, 0, $max);
        // Essayez de couper à la fin d'une phrase
        $pos = max(mb_strrpos($snippet, '.'), mb_strrpos($snippet, '!'), mb_strrpos($snippet, '?'));
        if ($pos !== false && $pos > ($max * 0.6)) {
            $snippet = mb_substr($snippet, 0, $pos + 1);
        }
        return rtrim($snippet) . '…';
    }

    /**
     * Version plus concise du texte pour l'indexation thésaurus.
     */
    private function buildRecordsThesaurusText(array $ids): string
    {
        $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        // Limit to the first record to keep request small and fast
        if (count($ids) > 1) { $ids = [ $ids[0] ]; }
        if (empty($ids)) { return ''; }

        $records = Record::query()
            ->with([
                'authors:id,name',
                'thesaurusConcepts' => function ($q) {
                    $q->with(['labels' => function ($q2) {
                        $q2->where('type', 'prefLabel')->where('language', 'fr-fr');
                    }]);
                },
            ])
            ->whereIn('id', $ids)
            ->get(['id','code','name','date_start','date_end','date_exact','content','note','archivist_note','finding_aids']);

        $parts = [];
        foreach ($records as $r) {
            $lines = [];
            $lines[] = 'Enregistrement #' . $r->id;
            if (!empty($r->name)) { $lines[] = 'Titre: ' . $r->name; }
            if (!empty($r->code)) { $lines[] = 'Cote: ' . $r->code; }
            $dateStr = '';
            if (!empty($r->date_exact) && $r->date_exact && !empty($r->date_start)) {
                $dateStr = (string)$r->date_start;
            } else {
                $start = $r->date_start ? (string)$r->date_start : '';
                $end = $r->date_end ? (string)$r->date_end : '';
                if ($start || $end) { $dateStr = trim($start . ' - ' . $end, ' -'); }
            }
            if ($dateStr) { $lines[] = 'Dates: ' . $dateStr; }

            if ($r->authors && $r->authors->count()) {
                $lines[] = 'Auteurs: ' . implode(', ', $r->authors->pluck('name')->filter()->take(8)->all());
            }
            if ($r->thesaurusConcepts && $r->thesaurusConcepts->count()) {
                $labels = [];
                foreach ($r->thesaurusConcepts as $c) {
                    $lbl = $c->labels->first();
                    if ($lbl && !empty($lbl->literal_form)) { $labels[] = $lbl->literal_form; }
                    if (count($labels) >= 12) { break; }
                }
                if ($labels) { $lines[] = 'Thésaurus existants: ' . implode(', ', $labels); }
            }

            foreach (['content' => 'Contenu', 'finding_aids' => 'Instruments', 'note' => 'Note', 'archivist_note' => "Note de l'archiviste"] as $field => $label) {
                $val = (string) ($r->{$field} ?? '');
                if ($val !== '') { $lines[] = $label . ': ' . $this->softTrim($val, 800); }
            }

            $parts[] = implode("\n", $lines);
        }

    $out = implode("\n\n---\n\n", $parts);
    // Apply a global cap to avoid exceeding provider context
    return $this->softTrim($out, 4800);
    }
    // Provider configuration and settings lookup are centralized in ProviderRegistry
}
