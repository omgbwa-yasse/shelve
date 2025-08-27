<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Record;
use App\Models\ThesaurusConcept;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Models\Prompt;
use AiBridge\Facades\AiBridge;
use App\Services\AI\ProviderRegistry;
use App\Services\AttachmentTextExtractor;
use App\Services\SettingService;

class AiRecordApplyController extends Controller
{
    private const RULE_RAW = 'nullable|string|min:1';
    private const ERR_NO_DATA = 'No data provided';

    public function saveTitle(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'title' => 'nullable|string|min:1|max:255',
            'raw_text' => self::RULE_RAW,
        ]);
        $value = $data['title'] ?? $data['raw_text'] ?? null;
        if ($value === null) {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }
        // If raw_text provided, still enforce DB constraint via max length
        $record->name = mb_substr($value, 0, 255);
        $record->save();
        return response()->json(['status' => 'ok', 'record_id' => $record->id, 'name' => $record->name]);
    }

    public function saveSummary(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'summary' => 'nullable|string|min:1',
            'raw_text' => self::RULE_RAW,
        ]);
        $value = $data['summary'] ?? $data['raw_text'] ?? null;
        if ($value === null) {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }
        $record->content = $value;
        $record->save();
        return response()->json(['status' => 'ok', 'record_id' => $record->id]);
    }

    public function saveThesaurus(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'concepts' => 'nullable|array|min:1',
            'concepts.*.id' => 'nullable|integer|exists:thesaurus_concepts,id',
            'concepts.*.preferred_label' => 'nullable|string',
            'concepts.*.weight' => 'nullable|numeric|min:0|max:1',
            'raw_text' => self::RULE_RAW,
        ]);

        $attach = [];
        if (!empty($data['concepts'])) {
            $attach = $this->attachFromConceptArray($data['concepts']);
        } elseif (!empty($data['raw_text'])) {
            $attach = $this->attachFromRawLabels($data['raw_text']);
        } else {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }
        if (!empty($attach)) {
            $record->thesaurusConcepts()->syncWithoutDetaching($attach);
        }
        return response()->json(['status' => 'ok', 'record_id' => $record->id, 'attached' => array_keys($attach)]);
    }

    /**
     * Suggest thesaurus concepts from AI-extracted keywords with synonyms.
     * Input: { raw_text?: string } where raw_text contains lines like:
     *   - [Catégorie] Mot-clé — synonymes : s1; s2; s3
     * or a summary block that includes such a section.
     * Returns: { suggestions: [ { label, category, synonyms[], concept_id?, matches: [ {concept_id, label, type} ] } ] }
     */
    public function suggestThesaurus(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'raw_text' => self::RULE_RAW,
        ]);
        $text = (string)($data['raw_text'] ?? '');
        if ($text === '') {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }

        $items = $this->extractKeywordSynonymLines($text);
        $suggestions = [];
        foreach ($items as $it) {
            // Search pref/alt labels for the main keyword and synonyms
            $matches = $this->findConceptMatchesForTerms(array_merge([$it['label']], $it['synonyms']));
            $suggestions[] = [
                'label' => $it['label'],
                'category' => $it['category'],
                'synonyms' => $it['synonyms'],
                'matches' => $matches,
            ];
        }

        return response()->json([
            'status' => 'ok',
            'record_id' => $record->id,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Suggest from client-provided JSON array of items: [{label, category, synonyms[]}]
     * This supports your “je veux les données en JSON … et la recherche” use case directly.
     */
    public function suggestThesaurusFromJson(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.label' => 'required|string|min:1',
            'items.*.category' => 'nullable|string',
            'items.*.synonyms' => 'nullable|array',
            'items.*.synonyms.*' => 'nullable|string',
        ]);

        $suggestions = [];
        foreach ($data['items'] as $it) {
            $label = trim((string)$it['label']);
            if ($label === '') { continue; }
            $syn = array_values(array_filter(array_map('trim', (array)($it['synonyms'] ?? []))));
            $matches = $this->findConceptMatchesForTerms(array_merge([$label], $syn));
            $suggestions[] = [
                'label' => $label,
                'category' => $it['category'] ?? null,
                'synonyms' => $syn,
                'matches' => $matches,
            ];
        }

        return response()->json([
            'status' => 'ok',
            'record_id' => $record->id,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * End-to-end: build record text, call AI summarization prompt, extract keyword lines, match concepts, return suggestions.
     */
    public function autoSuggestThesaurus(Request $request, Record $record)
    {
        Gate::authorize('records_edit');

        // 1) Build text from the record similar to PromptController summarize path
        $text = $this->buildTextFromRecord($record);

        // 2) Load action.summarize.user (user template) and record_summarize (system) prompts
        $system = Prompt::where('title', 'record_summarize')->first();
        $user = Prompt::where('title', 'action.summarize.user')->first();
        $messages = [];
        if ($system && !empty($system->content)) {
            $messages[] = ['role' => 'system', 'content' => $system->content];
        }
        $userText = $user?->content ?: "À partir du texte suivant, produis un résumé et 5 mots-clés catégorisés avec 3 synonymes chacun.\nTexte:\n{{text}}";
        $messages[] = ['role' => 'user', 'content' => strtr($userText, ['{{text}}' => $text])];

        // 3) Execute using default provider/model
        $provider = app(\App\Services\SettingService::class)->get('ai_default_provider', 'ollama');
        $model = app(\App\Services\SettingService::class)->get('ai_default_model', config('ollama-laravel.model', 'gemma3:4b'));
        // Ensure provider is configured
        try { app(ProviderRegistry::class)->ensureConfigured($provider); } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'AI provider not configured: '.$provider], 422);
        }

        $res = AiBridge::provider($provider)->chat($messages, [
            'model' => $model,
            'temperature' => 0.3,
            'max_tokens' => 400,
            'timeout' => 40000,
        ]);
        $content = $this->extractText(is_array($res) ? $res : (array)$res);

        // 4) Extract lines and match concepts
        $items = $this->extractKeywordSynonymLines($content);
        $suggestions = [];
        foreach ($items as $it) {
            $matches = $this->findConceptMatchesForTerms(array_merge([$it['label']], $it['synonyms']))
                ;
            $suggestions[] = [
                'label' => $it['label'],
                'category' => $it['category'],
                'synonyms' => $it['synonyms'],
                'matches' => $matches,
            ];
        }

        return response()->json([
            'status' => 'ok',
            'record_id' => $record->id,
            'ai_output' => $content,
            'suggestions' => $suggestions,
        ]);
    }

    public function saveActivity(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'activity_id' => 'nullable|integer|exists:activities,id',
            'activity_name' => 'nullable|string',
            'raw_text' => self::RULE_RAW,
        ]);

    $selection = null;
        $activityId = null;

        // Prefer JSON selection if provided in raw_text
        if (!empty($data['raw_text'])) {
            $parsed = $this->parseActivitySelectionJson($data['raw_text']);
            if ($parsed) {
                $selection = $parsed; // keep for response
                $activityId = $this->resolveActivityFromSelection($parsed['selected'] ?? []);
            }
        }

        // Fallback to direct id/name/parsed list if JSON not provided or resolution failed
        if ($activityId === null) {
            $activityId = $this->resolveActivityId($data);
        }

        if ($activityId === null) {
            return response()->json(['status' => 'error', 'message' => 'Activity not found'], 422);
        }
        $record->activity_id = $activityId;

        $record->save();
        return response()->json([
            'status' => 'ok',
            'record_id' => $record->id,
            'activity_id' => $record->activity_id,
            'ai_selection' => $selection,
        ]);
    }

    /**
     * Suggest relevant activities from the database based on an AI output string.
     * Input: { raw_text?: string }
     * Returns: { candidates: [ { id, code, name, score } ] }
     */
    public function suggestActivityCandidates(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'raw_text' => self::RULE_RAW,
        ]);

        $raw = (string)($data['raw_text'] ?? '');
        $terms = $this->parseList($raw);

        // Also include tokens split from the raw text to help matching
        $extraTokens = [];
        if ($raw !== '') {
            $tmp = preg_split('/[^\p{L}\p{N}\-]+/u', mb_strtolower($raw));
            foreach ($tmp as $t) {
                $t = trim($t);
                if ($t !== '' && mb_strlen($t) >= 2) { $extraTokens[$t] = true; }
            }
        }

        $hasSignals = !empty($terms) || !empty($extraTokens);

        $all = Activity::query()->select('id','code','name')->get();
        $candidates = [];
        foreach ($all as $act) {
            $score = 0.0;
            $name = (string)($act->name ?? '');
            $code = (string)($act->code ?? '');
            $lname = mb_strtolower($name);
            $lcode = mb_strtolower($code);

            // Strong signal: exact code mention in list or text
            if ($code !== '') {
                foreach ($terms as $t) {
                    if (trim($t) === $code) { $score += 8; }
                }
                if ($raw !== '' && preg_match('/(^|\b)'.preg_quote($code, '/').'($|\b)/iu', $raw)) {
                    $score += 5;
                }
            }

            // Name signals
            foreach ($terms as $t) {
                $lt = mb_strtolower($t);
                if ($lt === $lname) { $score += 6; }
                elseif ($lname !== '' && str_contains($lname, $lt)) { $score += 3; }
            }
            foreach (array_keys($extraTokens) as $tok) {
                if ($tok === $lname) { $score += 2; }
                elseif ($tok !== '' && $lname !== '' && str_contains($lname, $tok)) { $score += 1.2; }
                if ($lcode !== '' && $tok === $lcode) { $score += 3; }
            }

            // Slight boost if existing record already has this activity (de-dup)
            if ($record->activity_id && (int)$record->activity_id === (int)$act->id) {
                $score += 0.1;
            }

            $candidates[] = [
                'id' => (int)$act->id,
                'code' => $code,
                'name' => $name,
                'score' => round($score, 3),
            ];
        }

        // Sort by score desc, then by code/name for stability
        usort($candidates, function($a, $b){
            if ($a['score'] === $b['score']) {
                return strcmp(($a['code'].$a['name']), ($b['code'].$b['name']));
            }
            return $a['score'] < $b['score'] ? 1 : -1;
        });

        // If we had no signals, just return the first 10 alphabetically by name
        if (!$hasSignals) {
            usort($candidates, function($a, $b){ return strcmp($a['name'], $b['name']); });
        }

        $top = array_slice($candidates, 0, 10);

        return response()->json([
            'status' => 'ok',
            'record_id' => $record->id,
            'candidates' => $top,
        ]);
    }

    private function parseList(string $text): array
    {
        $parts = preg_split('/[\n,;\t\x{2022}\*]+/u', str_replace("\r", '', $text));
        $out = [];
        $seen = [];
        foreach ($parts as $p) {
            $t = trim((string)$p);
            if ($t === '') { continue; }
            if (isset($seen[mb_strtolower($t)])) { continue; }
            $seen[mb_strtolower($t)] = true;
            $out[] = $t;
            if (count($out) >= 30) { break; }
        }
        return $out;
    }

    private function attachFromConceptArray(array $concepts): array
    {
        $attach = [];
        foreach ($concepts as $c) {
            if (!empty($c['id'])) {
                $attach[(int)$c['id']] = [
                    'weight' => isset($c['weight']) ? (float)$c['weight'] : 0.7,
                    'context' => 'ai',
                    'extraction_note' => null,
                ];
            } elseif (!empty($c['preferred_label'])) {
                $concept = $this->findConceptByLabel((string)$c['preferred_label']);
                if ($concept) {
                    $attach[$concept->id] = [
                        'weight' => isset($c['weight']) ? (float)$c['weight'] : 0.7,
                        'context' => 'ai',
                        'extraction_note' => null,
                    ];
                }
            }
        }
        return $attach;
    }

    private function attachFromRawLabels(string $raw): array
    {
        $attach = [];
        $labels = $this->parseThesaurusLines($raw);
        foreach ($labels as $label) {
            $concept = $this->findConceptByLabel($label);
            if ($concept) {
                $attach[$concept->id] = [
                    'weight' => 0.7,
                    'context' => 'ai',
                    'extraction_note' => null,
                ];
            }
        }
        return $attach;
    }

    /**
     * Parse thesaurus suggestions formatted like:
     *   - [Catégorie] Libellé — synonymes : s1; s2; s3
     * or simple lines with just the label. Returns an array of cleaned label strings.
     */
    private function parseThesaurusLines(string $text): array
    {
        $text = str_replace("\r", '', $text);
        $lines = preg_split('/\n+/', $text) ?: [];
        $out = [];
        $seen = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') { continue; }
            // Skip headers
            if (preg_match('/^(résumé\s*:|mots[- ]clés|keywords)/i', $t)) { continue; }
            // Remove leading bullets or numbering
            $t = preg_replace('/^([\-\*•\d]+[\).\]]?)\s*/u', '', $t);
            // Remove leading category tag like [P], [M], [En], [Es]
            $t = preg_replace('/^\[[^\]]+\]\s*/u', '', $t);
            // If line contains an em dash or hyphen used as separator before synonyms, keep only the left part
            if (preg_match('/\s—\s|\s-\s/u', $t)) {
                $parts = preg_split('/\s—\s|\s-\s/u', $t, 2);
                $t = trim($parts[0] ?? $t);
            }
            // Also strip explicit "synonymes : ..." tails if present
            $t = preg_replace('/\s*synonymes?\s*:.*/iu', '', $t);
            // Clean trailing punctuation
            $t = trim($t, " \t\x0B\f\v.\x{2026}");
            if ($t === '') { continue; }
            $k = mb_strtolower($t);
            if (isset($seen[$k])) { continue; }
            $seen[$k] = true;
            $out[] = $t;
            if (count($out) >= 30) { break; }
        }
        return $out;
    }

    /**
     * Extract keywords with optional category and synonyms from a block of text.
     * Supports lines formatted as: "- [Catégorie] Mot — synonymes : s1; s2; s3".
     */
    private function extractKeywordSynonymLines(string $text): array
    {
        $text = str_replace("\r", '', $text);
        $lines = preg_split('/\n+/', $text) ?: [];
        $out = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') { continue; }
            // Skip summary headers
            if (preg_match('/^(résumé\s*:|mots[- ]clés|keywords)/i', $t)) { continue; }
            // Strip leading bullet/number
            $t = preg_replace('/^([\-\*•\d]+[\).\]]?)\s*/u', '', $t);
            // Capture category if present at the start
            $category = null;
            if (preg_match('/^\[([^\]]+)\]\s*(.*)$/u', $t, $m)) {
                $category = trim($m[1]);
                $t = $m[2];
            }
            // Split label vs synonyms
            $label = $t;
            $syn = '';
            if (preg_match('/\s—\s|\s-\s/u', $t)) {
                $parts = preg_split('/\s—\s|\s-\s/u', $t, 2);
                $label = trim($parts[0] ?? '');
                $syn = trim($parts[1] ?? '');
            }
            // Extract synonyms after 'synonymes :' if present
            $synonyms = [];
            if ($syn !== '') {
                if (preg_match('/synonymes?\s*:\s*(.*)$/iu', $syn, $m2)) {
                    $synonyms = array_filter(array_map('trim', preg_split('/[;,]+/u', $m2[1])));
                } else {
                    $synonyms = array_filter(array_map('trim', preg_split('/[;,]+/u', $syn)));
                }
            }
            if ($label !== '') {
                $out[] = [
                    'label' => $label,
                    'category' => $category,
                    'synonyms' => array_values($synonyms),
                ];
            }
        }
        return $out;
    }

    /**
     * Build a concise text from a record to drive AI summarization/keyword extraction.
     */
    private function buildTextFromRecord(Record $record): string
    {
        $parts = [];
        $name = trim((string)($record->name ?? ''));
        if ($name !== '') { $parts[] = 'Titre: ' . $name; }
        $content = trim((string)($record->content ?? ''));
        if ($content !== '') {
            $parts[] = 'Contenu: ' . mb_substr(preg_replace('/\s+/u', ' ', $content), 0, 1200);
        }
        return implode("\n", $parts);
    }

    /**
     * Find matching thesaurus concepts for a set of terms (pref or alt labels).
     * Returns a list of {concept_id, label, type}.
     */
    private function findConceptMatchesForTerms(array $terms): array
    {
        // Batch normalize and deduplicate terms to reduce DB round-trips
        $norm = [];
        foreach ($terms as $t) {
            $t = trim((string)$t);
            if ($t === '') { continue; }
            $key = mb_strtolower($t);
            $norm[$key] = $t; // preserve original casing for 'matched'
            if (count($norm) >= 50) { break; } // hard cap to avoid overly large queries
        }
        if (empty($norm)) { return []; }
        $in = array_values($norm);

        $matches = [];
        $seen = [];

        // PrefLabel: eager-load only matched labels to capture exact literal_form
        $prefConcepts = ThesaurusConcept::query()
            ->with(['labels' => function ($q) use ($in) {
                $q->whereIn('literal_form', $in)->where('type', 'prefLabel');
            }])
            ->whereHas('labels', function ($q) use ($in) {
                $q->whereIn('literal_form', $in)->where('type', 'prefLabel');
            })
            ->get();
        foreach ($prefConcepts as $c) {
            foreach ($c->labels as $lbl) {
                $key = 'p:' . $c->id . ':' . mb_strtolower((string)$lbl->literal_form);
                if (isset($seen[$key])) { continue; }
                $seen[$key] = true;
                $matches[] = [
                    'concept_id' => $c->id,
                    'preferred_label' => $c->preferred_label ?? null,
                    'matched' => (string)$lbl->literal_form,
                    'type' => 'prefLabel',
                ];
            }
        }

        // AltLabel: same batching approach
        $altConcepts = ThesaurusConcept::query()
            ->with(['labels' => function ($q) use ($in) {
                $q->whereIn('literal_form', $in)->where('type', 'altLabel');
            }])
            ->whereHas('labels', function ($q) use ($in) {
                $q->whereIn('literal_form', $in)->where('type', 'altLabel');
            })
            ->get();
        foreach ($altConcepts as $c) {
            foreach ($c->labels as $lbl) {
                $key = 'a:' . $c->id . ':' . mb_strtolower((string)$lbl->literal_form);
                if (isset($seen[$key])) { continue; }
                $seen[$key] = true;
                $matches[] = [
                    'concept_id' => $c->id,
                    'preferred_label' => $c->preferred_label ?? null,
                    'matched' => (string)$lbl->literal_form,
                    'type' => 'altLabel',
                ];
            }
        }

        return $matches;
    }

    /**
     * Resolve a concept by a human label using labels relation (prefLabel preferred, then altLabel), any language.
     */
    private function findConceptByLabel(string $label): ?ThesaurusConcept
    {
        $label = trim($label);
        if ($label === '') { return null; }
        $q = ThesaurusConcept::query()
            ->whereHas('labels', function ($sub) use ($label) {
                $sub->where('literal_form', $label)->where('type', 'prefLabel');
            });
        $concept = $q->first();
        if ($concept) { return $concept; }
        // Fallback to altLabel
        return ThesaurusConcept::query()
            ->whereHas('labels', function ($sub) use ($label) {
                $sub->where('literal_form', $label)->where('type', 'altLabel');
            })
            ->first();
    }

    private function resolveActivityId(array $data): ?int
    {
        $id = null;
        if (!empty($data['activity_id'])) {
            $id = (int)$data['activity_id'];
        } elseif (!empty($data['activity_name'])) {
            $activity = Activity::where('name', $data['activity_name'])->first();
            $id = $activity?->id;
        } elseif (!empty($data['raw_text'])) {
            $candidates = $this->parseList($data['raw_text']);
            foreach ($candidates as $cand) {
                $found = Activity::where('name', $cand)->first();
                if ($found) { $id = $found->id; break; }
            }
        }
        return $id;
    }

    /**
     * Parses a strict JSON selection structure as requested from the model.
     * Expected shape:
     *   { "selected": {"id":int|null, "code":string|null, "name":string|null},
     *     "alternative": {...}, "confidence": number, "reason": string }
     * Returns associative array or null if not valid JSON/shape.
     */
    private function parseActivitySelectionJson(string $raw): ?array
    {
        $trim = ltrim($raw);
        if ($trim === '' || $trim[0] !== '{') { return null; }
        try {
            $obj = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
        if (!is_array($obj) || !isset($obj['selected'])) { return null; }
        // Normalize
        $norm = [
            'selected' => [
                'id' => isset($obj['selected']['id']) ? (is_numeric($obj['selected']['id']) ? (int)$obj['selected']['id'] : null) : null,
                'code' => isset($obj['selected']['code']) ? (string)$obj['selected']['code'] : null,
                'name' => isset($obj['selected']['name']) ? (string)$obj['selected']['name'] : null,
            ],
            'alternative' => [
                'id' => isset($obj['alternative']['id']) ? (is_numeric($obj['alternative']['id']) ? (int)$obj['alternative']['id'] : null) : null,
                'code' => isset($obj['alternative']['code']) ? (string)$obj['alternative']['code'] : null,
                'name' => isset($obj['alternative']['name']) ? (string)$obj['alternative']['name'] : null,
            ],
            'confidence' => isset($obj['confidence']) && is_numeric($obj['confidence']) ? max(0, min(1, (float)$obj['confidence'])) : null,
            'reason' => isset($obj['reason']) ? (string)$obj['reason'] : null,
        ];
        return $norm;
    }

    /**
     * Resolve an activity id from a partial selection record {id, code, name}.
     */
    private function resolveActivityFromSelection(array $sel): ?int
    {
        if (!empty($sel['id'])) {
            $found = Activity::find((int)$sel['id']);
            if ($found) { return $found->id; }
        }
        if (!empty($sel['code'])) {
            $found = Activity::where('code', (string)$sel['code'])->first();
            if ($found) { return $found->id; }
        }
        if (!empty($sel['name'])) {
            $found = Activity::where('name', (string)$sel['name'])->first();
            if ($found) { return $found->id; }
        }
        return null;
    }

    /**
     * Save keywords to a record after user validation.
     * Input: { keywords: [ { name, create?, selected? } ], raw_text?: string }
     * Returns: { status: 'ok', record_id, attached: [...] }
     */
    public function saveKeywords(Request $request, Record $record)
    {
        Gate::authorize('records_edit');
        $data = $request->validate([
            'keywords' => 'nullable|array|min:1',
            'keywords.*.name' => 'required|string|min:1|max:255',
            'keywords.*.create' => 'nullable|boolean',
            'keywords.*.selected' => 'nullable|boolean',
            'raw_text' => self::RULE_RAW,
        ]);

        $attach = [];
        if (!empty($data['keywords'])) {
            foreach ($data['keywords'] as $keywordData) {
                $name = trim($keywordData['name']);
                $create = $keywordData['create'] ?? false;
                $selected = $keywordData['selected'] ?? true;

                if (!$selected || empty($name)) {
                    continue;
                }

                // Find or create keyword if user approved creation
                if ($create) {
                    $keyword = Keyword::findOrCreate($name);
                } else {
                    $keyword = Keyword::where('name', $name)->first();
                }

                if ($keyword) {
                    $attach[] = $keyword->id;
                }
            }
        } elseif (!empty($data['raw_text'])) {
            // Fallback: parse from raw text
            $keywordNames = $this->parseKeywordLines($data['raw_text']);
            foreach ($keywordNames as $name) {
                $keyword = Keyword::findOrCreate($name);
                if ($keyword) {
                    $attach[] = $keyword->id;
                }
            }
        } else {
            return response()->json(['status' => 'error', 'message' => self::ERR_NO_DATA], 422);
        }

        if (!empty($attach)) {
            $record->keywords()->syncWithoutDetaching($attach);
        }

        return response()->json([
            'status' => 'ok',
            'record_id' => $record->id,
            'attached' => $attach
        ]);
    }

    /**
     * Suggest keywords from AI-extracted content including attachments.
     * Input: { raw_text?: string }
     * Returns: { suggestions: [ { name, exists, keyword_id? } ] }
     */
    public function suggestKeywords(Request $request, Record $record)
    {
        Log::info('Keywords AI Method Called', ['record_id' => $record->id]);

        Gate::authorize('records_edit');
        $data = $request->validate([
            'raw_text' => self::RULE_RAW,
        ]);

        // 1) Build text from record and attachments
        $text = $this->buildTextFromRecordWithAttachments($record);

        // 2) Load keyword extraction prompt and call AI
        $systemPrompt = Prompt::where('title', 'record_keywords')->first();
        $messages = [];

        if ($systemPrompt && $systemPrompt->content) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt->content];
        }

        $userPrompt = "Analysez le contenu suivant et extractez les mots-clés pertinents. Retournez un JSON avec un tableau 'keywords' contenant des objets avec 'name' et optionnellement 'category':\n\n" . $text;
        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        try {
            // Get default provider and model settings (same pattern as working method)
            $provider = app(\App\Services\SettingService::class)->get('ai_default_provider', 'ollama');
            $model = app(\App\Services\SettingService::class)->get('ai_default_model', config('ollama-laravel.model', 'gemma3:4b'));

            Log::info('Keywords AI Debug - Start', [
                'provider' => $provider,
                'model' => $model,
                'provider_type' => gettype($provider),
                'provider_length' => strlen($provider)
            ]);

            // Ensure provider is configured (exact same pattern as working method)
            try {
                app(ProviderRegistry::class)->ensureConfigured($provider);
                Log::info('Keywords AI Debug - Provider ensureConfigured succeeded');
            } catch (\Throwable $e) {
                Log::error('Keywords AI Debug - ensureConfigured failed', ['error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => 'AI provider not configured: '.$provider], 422);
            }

            // Check provider availability
            $providerInstance = AiBridge::provider($provider);
            Log::info('Keywords AI Debug - Provider instance', [
                'instance' => $providerInstance ? 'NOT NULL' : 'NULL',
                'class' => $providerInstance ? get_class($providerInstance) : 'N/A'
            ]);

            // For debugging: try the exact same approach as the working method
            $res = AiBridge::provider($provider)->chat($messages, [
                'model' => $model,
                'temperature' => 0.2,
                'max_tokens' => 250,
                'timeout' => 40000,
            ]);

            $content = $this->extractText(is_array($res) ? $res : (array)$res);

            // Parse AI response for keywords
            $suggestions = $this->parseAiKeywordsResponse($content);

            return response()->json([
                'status' => 'ok',
                'record_id' => $record->id,
                'suggestions' => $suggestions,
                'ai_output' => $content
            ]);

        } catch (\Exception $e) {
            Log::error('Keywords AI extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'record_id' => $record->id,
                'provider' => $provider ?? 'unknown',
                'model' => $model ?? 'unknown'
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'AI extraction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract text content from AI response array
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
        }
        return $text !== null ? $text : json_encode($res);
    }

    /**
     * Build text content from record and its attachments for AI analysis.
     */
    private function buildTextFromRecordWithAttachments(Record $record): string
    {
        $parts = [];

        // Add record content
        $name = trim((string)($record->name ?? ''));
        if ($name !== '') {
            $parts[] = 'Titre: ' . $name;
        }

        $content = trim((string)($record->content ?? ''));
        if ($content !== '') {
            $parts[] = 'Contenu: ' . mb_substr(preg_replace('/\s+/u', ' ', $content), 0, 1200);
        }

        $note = trim((string)($record->note ?? ''));
        if ($note !== '') {
            $parts[] = 'Note: ' . mb_substr(preg_replace('/\s+/u', ' ', $note), 0, 800);
        }

        $archivistNote = trim((string)($record->archivist_note ?? ''));
        if ($archivistNote !== '') {
            $parts[] = 'Note archiviste: ' . mb_substr(preg_replace('/\s+/u', ' ', $archivistNote), 0, 800);
        }

        // Add attachment content
        if ($record->attachments->isNotEmpty()) {
            $attachmentTexts = [];
            $extractor = app(AttachmentTextExtractor::class);

            foreach ($record->attachments->take(5) as $attachment) { // Limit to 5 attachments
                if (!empty($attachment->content_text)) {
                    // Use pre-extracted text
                    $text = mb_substr(trim($attachment->content_text), 0, 1000);
                    if ($text) {
                        $attachmentTexts[] = "Pièce jointe ({$attachment->name}): " . $text;
                    }
                } elseif (!empty($attachment->path)) {
                    // Try to extract text on demand
                    $absolutePath = storage_path('app/' . ltrim($attachment->path, '/'));
                    if (is_file($absolutePath)) {
                        $extractedText = $extractor->extract($absolutePath, $attachment->mime_type, $attachment->name);
                        if ($extractedText) {
                            $text = mb_substr(trim($extractedText), 0, 1000);
                            $attachmentTexts[] = "Pièce jointe ({$attachment->name}): " . $text;
                        }
                    }
                }
            }

            if (!empty($attachmentTexts)) {
                $parts[] = "Pièces jointes:\n" . implode("\n", $attachmentTexts);
            }
        }

        return implode("\n\n", $parts);
    }

    /**
     * Parse AI response to extract keywords with database matching.
     */
    private function parseAiKeywordsResponse(string $response): array
    {
        $suggestions = [];

        // Try to parse JSON first
        $jsonData = $this->tryParseJson($response);
        if ($jsonData && isset($jsonData['keywords']) && is_array($jsonData['keywords'])) {
            foreach ($jsonData['keywords'] as $item) {
                if (is_array($item) && !empty($item['name'])) {
                    $name = trim($item['name']);
                    if ($name) {
                        $suggestions[] = $this->buildKeywordSuggestion($name, $item['category'] ?? null);
                    }
                } elseif (is_string($item)) {
                    $name = trim($item);
                    if ($name) {
                        $suggestions[] = $this->buildKeywordSuggestion($name);
                    }
                }
            }
        } else {
            // Fallback: parse as lines
            $lines = $this->parseKeywordLines($response);
            foreach ($lines as $name) {
                $suggestions[] = $this->buildKeywordSuggestion($name);
            }
        }

        return array_slice($suggestions, 0, 20); // Limit to 20 suggestions
    }

    /**
     * Build a keyword suggestion with database lookup.
     */
    private function buildKeywordSuggestion(string $name, ?string $category = null): array
    {
        $name = trim($name);
        $existingKeyword = Keyword::where('name', $name)->first();

        return [
            'name' => $name,
            'category' => $category,
            'exists' => !is_null($existingKeyword),
            'keyword_id' => $existingKeyword?->id,
            'selected' => true, // Default to selected for user review
        ];
    }

    /**
     * Parse keyword lines from text output.
     */
    private function parseKeywordLines(string $text): array
    {
        $text = str_replace("\r", '', $text);
        $lines = preg_split('/\n+/', $text) ?: [];
        $out = [];
        $seen = [];

        foreach ($lines as $line) {
            $t = trim($line);
            if ($t === '') { continue; }

            // Skip headers
            if (preg_match('/^(résumé\s*:|mots[- ]clés|keywords)/i', $t)) { continue; }

            // Remove leading bullets or numbering
            $t = preg_replace('/^([\-\*•\d]+[\).\]]?)\s*/u', '', $t);

            // Remove category tags like [P], [M], [En], etc.
            $t = preg_replace('/^\[[^\]]+\]\s*/u', '', $t);

            // Remove JSON syntax artifacts
            $t = preg_replace('/^["\'{}\[\],]\s*/', '', $t);
            $t = preg_replace('/["\'{}\[\],]\s*$/', '', $t);

            // Split on common separators and clean
            $parts = preg_split('/[,;]+/', $t);
            foreach ($parts as $part) {
                $keyword = trim($part, " \t\x0B\f\v.\x{2026}\"'");
                if ($keyword === '' || mb_strlen($keyword) < 2) { continue; }

                $k = mb_strtolower($keyword);
                if (isset($seen[$k])) { continue; }
                $seen[$k] = true;

                $out[] = $keyword;
                if (count($out) >= 30) { break 2; }
            }
        }

        return $out;
    }

    /**
     * Try to parse JSON from AI response.
     */
    private function tryParseJson(string $response): ?array
    {
        // Look for JSON block in response
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $jsonStr = $matches[0];
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // Try the full response as JSON
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return null;
    }
}
