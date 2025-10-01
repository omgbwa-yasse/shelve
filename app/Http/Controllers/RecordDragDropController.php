<?php

namespace App\Http\Controllers;

use AiBridge\Facades\AiBridge;
use App\Exceptions\AiCallFailedException;
use App\Exceptions\AiProviderNotConfiguredException;
use App\Exceptions\InvalidUploadedFilesException;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Author;
use App\Models\Keyword;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\AuthorType;
use App\Services\AI\ProviderRegistry;
use App\Services\AttachmentTextExtractor;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class RecordDragDropController extends Controller
{
    /**
     * Afficher le formulaire Drag & Drop
     */
    public function dragDropForm()
    {
        Gate::authorize('records_create');
        $appUploadMaxMb = (int) app(SettingService::class)->get('upload_max_file_size_mb', 50);
        return view('records.drag-drop', [
            'server_post_max' => ini_get('post_max_size'),
            'server_upload_max_filesize' => ini_get('upload_max_filesize'),
            'server_max_file_uploads' => ini_get('max_file_uploads'),
            'app_upload_max_file_size_mb' => $appUploadMaxMb,
        ]);
    }

    /**
     * Traiter les fichiers uploadés via Drag & Drop avec IA
     */
    public function processDragDrop(Request $request)
    {
        Gate::authorize('records_create');
        try {
            $files = $this->validateAndGetFiles($request);
            $charLimit = (int)($request->input('per_file_char_limit', 200));
            $pdfPageCount = (int)($request->input('pdf_page_count', 0));

            $processed = $this->storeAndExtractAttachments($files, $charLimit, $pdfPageCount);
            $messages = $this->buildAiMessages($processed['snippets']);
            $parsed = $this->callAiAndParse($messages);
            $record = $this->persistRecord($parsed, $processed['attachments']);

                $activitySuggestion = $this->topActivityCandidate($parsed['activity_candidates'] ?? []);
                if ($activitySuggestion && isset($activitySuggestion['code'])) {
                    $orgId = optional(Auth::user())->current_organisation_id;
                    $db = Activity::where('code', $activitySuggestion['code'])
                        ->when($orgId, function ($q) use ($orgId) {
                            $q->whereHas('organisations', function ($q2) use ($orgId) {
                                $q2->where('organisations.id', $orgId);
                            });
                        })
                        ->first();
                    if ($db) {
                        $activitySuggestion['id'] = $db->id;
                        $activitySuggestion['name'] = $db->name;
                    } elseif (!empty($activitySuggestion['name'])) {
                        $dbn = Activity::where('name', $activitySuggestion['name'])
                            ->when($orgId, function ($q) use ($orgId) {
                                $q->whereHas('organisations', function ($q2) use ($orgId) {
                                    $q2->where('organisations.id', $orgId);
                                });
                            })
                            ->first();
                        if ($dbn) { $activitySuggestion['id'] = $dbn->id; }
                    }
                }

            return response()->json([
                'success' => true,
                'record_id' => $record->id,
                'ai_suggestions' => [
                    'title' => (string)($parsed['title'] ?? ''),
                    'content' => (string)($parsed['content'] ?? ''),
                    'keywords' => array_values(array_filter(array_map('strval', (array)($parsed['keywords'] ?? [])))) ,
                    'activity_suggestion' => $activitySuggestion,
                ],
                'attachments' => collect($processed['attachments'])->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'path' => $a->path,
                    'size' => $a->size,
                ])->all(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation des fichiers échouée',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('DragDrop: échec global', ['err' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ========================
     | Helpers internes       |
     ======================== */

    private function extractAiText($res): string
    {
        return \App\Services\AI\ResponseTextExtractor::extract($res);
    }

    private function tryParseJson(?string $text): ?array
    {
        $result = null;
        if (!is_string($text) || trim($text) === '') { return $result; }
        $s = trim($text);
        // Remove markdown fences
        $s = preg_replace('/^```(?:json)?/mi', '', $s);
        $s = preg_replace('/```$/m', '', $s);
        $s = trim($s);
        // Try direct decode
        $data = json_decode($s, true);
        if (is_array($data)) { $result = $data; }
        // Fallback: extract first JSON object
        if ($result === null && preg_match('/\{[\s\S]*\}/m', $s, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) { $result = $data; }
        }
        return $result;
    }

    private function resolveActivityIdFromCandidates(array $candidates): ?int
    {
        // Pick highest confidence
        $id = null;
        $top = $this->topActivityCandidate($candidates);
        if ($top) {
            if (!empty($top['code'])) {
                $a = Activity::where('code', $top['code'])->first();
                if ($a) { $id = $a->id; }
            }
            if ($id === null && !empty($top['name'])) {
                $a = Activity::where('name', $top['name'])->first();
                if ($a) { $id = $a->id; }
            }
        }
        return $id;
    }

    private function topActivityCandidate(array $candidates): ?array
    {
        $best = null; $bestScore = -1;
        foreach ($candidates as $c) {
            if (!is_array($c)) { continue; }
            $conf = isset($c['confidence']) ? (float)$c['confidence'] : 0.0;
            if ($conf > $bestScore) { $best = $c; $bestScore = $conf; }
        }
        return $best;
    }

    private function generateCode(): string
    {
        // 10 chars max (migration constraint)
        for ($i=0; $i<5; $i++) {
            $code = strtoupper(substr('AI'.bin2hex(random_bytes(4)), 0, 10));
            if (!Record::where('code', $code)->exists()) {
                return $code;
            }
        }
        return strtoupper(substr('AI'.time(), 0, 10));
    }

    private function validateAndGetFiles(Request $request): array
    {
        $appUploadMaxMb = (int) app(SettingService::class)->get('upload_max_file_size_mb', 50);
        $maxKilobytes = max(1, $appUploadMaxMb) * 1024; // Laravel rule expects KB
        $request->validate([
            'files' => 'required',
            'files.*' => 'file|max:' . $maxKilobytes . '|mimes:pdf,jpeg,jpg,png,gif,doc,docx,txt,rtf,odt',
            'per_file_char_limit' => 'nullable|integer|min:200|max:100000',
            'pdf_page_count' => 'nullable|integer|min:1|max:2000',
        ]);

        $files = $request->file('files', []);
        Log::info('DragDrop: fichiers reçus', [
            'count' => is_array($files) ? count($files) : 0,
            'user_id' => Auth::id(),
        ]);
        return (array)$files;
    }

    private function storeAndExtractAttachments(array $files, int $charLimit, int $pdfPageCount = 0): array
    {
        $extractor = app(AttachmentTextExtractor::class);
        $attachments = [];
        $snippets = [];

        foreach ($files as $uploaded) {
            $attachment = $this->storeSingleAttachment($uploaded);
            if (!$attachment) { continue; }
            $attachments[] = $attachment;
            $snippet = $this->extractSnippetForAttachment($extractor, $attachment, $charLimit, $pdfPageCount);
            if ($snippet !== null) { $snippets[] = $snippet; }
        }

        if (empty($attachments)) {
            throw new InvalidUploadedFilesException('Aucun fichier valide reçu');
        }

        return compact('attachments', 'snippets');
    }

    private function storeSingleAttachment($uploaded): ?Attachment
    {
        if (!$uploaded || !$uploaded->isValid()) { return null; }
        $path = $uploaded->store('attachments');
        $real = $uploaded->getRealPath();
        $mime = $uploaded->getMimeType();

        return Attachment::create([
            'path' => $path,
            'name' => $uploaded->getClientOriginalName(),
            'crypt' => @md5_file($real) ?: '',
            'crypt_sha512' => @hash_file('sha512', $real) ?: '',
            'size' => $uploaded->getSize(),
            'creator_id' => Auth::id(),
            'mime_type' => $mime,
            'type' => 'record',
        ]);
    }

    private function extractSnippetForAttachment(AttachmentTextExtractor $extractor, Attachment $attachment, int $charLimit, int $pdfPageCount = 0): ?string
    {
        $abs = storage_path('app/' . ltrim($attachment->path, '/'));
        $text = null;
        if (is_file($abs)) {
            try {
                $options = [];
                if ($pdfPageCount > 0) {
                    $options['pdf_page_start'] = 1; // always start at first page for now
                    $options['pdf_page_count'] = $pdfPageCount;
                }
                $text = $extractor->extract($abs, $attachment->mime_type, $attachment->name, $options);
            } catch (\Throwable $e) {
                Log::warning('DragDrop: extraction échouée', ['att_id' => $attachment->id, 'err' => $e->getMessage()]);
            }
        }
        $text = is_string($text) ? trim($text) : '';
        if ($text === '') { return null; }
        $text = mb_substr(preg_replace('/\s+/u', ' ', $text), 0, max(200, $charLimit));
        return "Fichier: {$attachment->name}\n{$text}";
    }

    private function buildAiMessages(array $snippets): array
    {
        $joined = implode("\n\n---\n\n", $snippets);

        // Restreindre aux activités de l'organisation courante (parents + enfants récursifs)
        $orgId = optional(Auth::user())->current_organisation_id;
        $allowedActivities = $this->getOrganisationActivitiesWithDescendants($orgId);

        $allowedJson = json_encode($allowedActivities, JSON_UNESCAPED_UNICODE);
        if ($allowedJson === false) { $allowedJson = '[]'; }

    // Préparer les types d'auteurs autorisés (liste courte par nom)
    $authorTypes = AuthorType::query()->orderBy('id')->limit(20)->pluck('name', 'id');
    $authorTypesArray = $authorTypes->map(fn($name, $id) => ['id' => (int)$id, 'name' => (string)$name])->values()->all();
    $authorTypesJson = json_encode($authorTypesArray, JSON_UNESCAPED_UNICODE);
    if ($authorTypesJson === false) { $authorTypesJson = '[]'; }

    $system = "Tu es un assistant d'archives. En te basant uniquement sur le texte fourni, propose un titre court, un résumé factuel (2-4 phrases),\nainsi que 3 à 8 mots-clés. Pour les activités candidates, tu DOIS choisir exclusivement dans la liste d'activités autorisées fournie.\nPour chaque auteur proposé, indique aussi le type en choisissant STRICTEMENT dans la liste de types d'auteurs autorisée fournie.\nRéponds STRICTEMENT en JSON valide, sans aucun texte avant ni après.";

        $userText = <<<TXT
Voici le contenu extrait des fichiers (tronqué au besoin) :

$joined

Liste des activités autorisées (NE PROPOSE QUE celles-ci) :
$allowedJson

        Liste des types d'auteurs autorisés (NE CHOISIR QUE ceux-ci) :
        $authorTypesJson

Exigeance de sortie JSON stricte (UTF-8) suivant ce modèle exact :
{
  "title": "...",
  "content": "...",
  "keywords": ["mot1", "mot2", "mot3"],
            "activity_candidates": [ { "code": "DF-01110", "name": "COLLECTE DES PRÉVISIONS BUDGÉTAIRES", "confidence": 0.85 } ],
            "authors": [ { "name": "Nom Auteur", "type_id": 1 } ]
}

Règle: Si la liste des activités autorisées est vide, renvoie "activity_candidates" vide.
TXT;
        return [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $userText],
        ];
    }

    private function callAiAndParse(array $messages): array
    {
        $provider = app(SettingService::class)->get('ai_default_provider', 'ollama');
        $model = app(SettingService::class)->get('ai_default_model', config('ollama-laravel.model', 'gemma3:4b'));
        
        // Récupérer le timeout depuis l'env (en secondes), défaut 300s = 5 minutes
        $timeout = (int) env('AI_REQUEST_TIMEOUT', 300);
        
        try {
            app(ProviderRegistry::class)->ensureConfigured($provider);
        } catch (\Throwable $e) {
            throw new AiProviderNotConfiguredException('Fournisseur IA non configuré: '.$provider);
        }

        try {
            // Utiliser le timeout configuré pour les modèles lents
            $res = AiBridge::provider($provider)->chat($messages, [
                'model' => $model,
                'temperature' => 0.2,
                'max_tokens' => 800,
                'timeout' => $timeout,
                'connect_timeout' => 10,
            ]);
            $content = $this->extractAiText(is_array($res) ? $res : (array)$res);
        } catch (\Throwable $e) {
            throw new AiCallFailedException('Erreur lors de l\'appel à l\'IA: '.$e->getMessage());
        }

        $parsed = $this->tryParseJson($content);
        if (!$parsed) {
            Log::warning('DragDrop: réponse IA non JSON', ['content' => mb_substr($content ?? '', 0, 500)]);
            $parsed = [
                'title' => 'Nouveau dossier',
                'content' => $content ? mb_substr(trim($content), 0, 1000) : null,
                'keywords' => [],
                'activity_candidates' => [],
                'authors' => [],
            ];
        }
        return $parsed;
    }

    private function persistRecord(array $parsed, array $attachments): Record
    {
        return DB::transaction(function () use ($parsed, $attachments) {
            [$statusId, $supportId, $levelId] = $this->resolveDefaultIds();
            $activityId = $this->resolveActivityIdFromCandidates($parsed['activity_candidates'] ?? [])
                ?: (int) Activity::query()->value('id');
            [$title, $content] = $this->normalizeTitleAndContent($parsed);

            $record = Record::create([
                'code' => $this->generateCode(),
                'name' => mb_substr($title, 0, 255),
                'date_format' => 'Y',
                'level_id' => $levelId,
                'status_id' => $statusId,
                'support_id' => $supportId,
                'activity_id' => $activityId,
                'user_id' => Auth::id(),
                'content' => $content,
            ]);

            if (!empty($attachments)) {
                $record->attachments()->attach(collect($attachments)->pluck('id')->all());
            }

            $this->attachAuthors($record, $parsed);
            $this->attachKeywords($record, $parsed);
            return $record;
        });
    }

    private function resolveDefaultIds(): array
    {
        $statusId = optional(RecordStatus::where('name', 'Brouillon')->first())->id
            ?? (int) RecordStatus::query()->value('id');
        $supportId = optional(RecordSupport::where('name', 'Numérique')->first())->id
            ?? optional(RecordSupport::where('name', 'Papier')->first())->id
            ?? (int) RecordSupport::query()->value('id');
        $levelId = optional(RecordLevel::where('name', 'Pièce')->first())->id
            ?? (int) RecordLevel::query()->value('id');
        return [$statusId, $supportId, $levelId];
    }

    private function normalizeTitleAndContent(array $parsed): array
    {
        $title = trim((string)($parsed['title'] ?? ''));
        if ($title === '') { $title = 'Nouveau dossier'; }
        $content = isset($parsed['content']) ? (string)$parsed['content'] : null;
        return [$title, $content];
    }

    private function attachAuthors(Record $record, array $parsed): void
    {
        $authors = $this->normalizeAuthorsPayload($parsed['authors'] ?? []);
        if (empty($authors)) { $authors = $this->defaultAuthorsFromUser(); }

        $defaultTypeId = $this->defaultAuthorTypeId();
        foreach ($authors as $a) {
            $this->upsertAndAttachAuthor($record, $a, $defaultTypeId);
        }
    }

    private function normalizeAuthorsPayload($raw): array
    {
        $raw = (array)$raw;
        $out = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $name = trim($item);
                if ($name !== '') { $out[] = ['name' => $name]; }
                continue;
            }
            if (is_array($item)) {
                $name = trim((string)($item['name'] ?? ''));
                if ($name === '') { continue; }
                $typeId = isset($item['type_id']) && is_numeric($item['type_id']) ? (int)$item['type_id'] : null;
                $out[] = ['name' => $name, 'type_id' => $typeId];
            }
        }
        return $out;
    }

    private function defaultAuthorsFromUser(): array
    {
        $fallback = trim((string) (Auth::user()->name ?? 'Inconnu'));
        return $fallback !== '' ? [['name' => $fallback]] : [];
    }

    private function defaultAuthorTypeId(): ?int
    {
        $id = AuthorType::query()->value('id');
        return $id ? (int)$id : null;
    }

    private function upsertAndAttachAuthor(Record $record, array $a, ?int $defaultTypeId): void
    {
        $name = mb_substr((string)($a['name'] ?? ''), 0, 255);
        if ($name === '') { return; }
        $typeId = isset($a['type_id']) && is_numeric($a['type_id']) ? (int)$a['type_id'] : null;
        if (!$typeId) { $typeId = $defaultTypeId; }
        if (!$typeId) { return; } // pas de type disponible

        $author = Author::firstOrCreate(['name' => $name], ['type_id' => $typeId]);
        if (!$author->type_id) {
            $author->type_id = $typeId;
            $author->save();
        }
        $record->authors()->syncWithoutDetaching([$author->id]);
    }

    private function attachKeywords(Record $record, array $parsed): void
    {
        $kw = [];
        foreach ((array)($parsed['keywords'] ?? []) as $k) {
            $name = trim((string)$k);
            if ($name === '') { continue; }
            $keyword = Keyword::findOrCreate(mb_substr($name, 0, 250));
            if ($keyword) { $kw[] = $keyword->id; }
        }
        if (!empty($kw)) { $record->keywords()->syncWithoutDetaching($kw); }
    }

    /**
     * Récupère toutes les activités (parents + enfants récursifs)
     * de l'organisation courante de l'utilisateur
     */
    private function getOrganisationActivitiesWithDescendants(?int $orgId): array
    {
        if (!$orgId) {
            return [];
        }

        // Récupérer les activités directement associées à l'organisation
        $parentActivities = Activity::query()
            ->whereHas('organisations', function ($q) use ($orgId) {
                $q->where('organisations.id', $orgId);
            })
            ->pluck('id')
            ->toArray();

        if (empty($parentActivities)) {
            return [];
        }

        // Récupérer tous les descendants récursifs
        $allActivityIds = $this->getAllDescendantActivityIds($parentActivities);

        // Récupérer les activités complètes avec code et nom
        return Activity::query()
            ->select(['id', 'code', 'name'])
            ->whereIn('id', $allActivityIds)
            ->orderBy('code')
            ->get()
            ->map(fn($a) => ['code' => (string)$a->code, 'name' => (string)$a->name])
            ->values()
            ->all();
    }

    /**
     * Récupère récursivement tous les IDs des activités descendantes
     */
    private function getAllDescendantActivityIds(array $parentIds): array
    {
        $allIds = $parentIds;
        $currentParents = $parentIds;

        while (!empty($currentParents)) {
            $children = Activity::query()
                ->whereIn('parent_id', $currentParents)
                ->pluck('id')
                ->toArray();

            if (empty($children)) {
                break;
            }

            $allIds = array_merge($allIds, $children);
            $currentParents = $children;
        }

        return array_unique($allIds);
    }
}
