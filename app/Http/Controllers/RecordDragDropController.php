<?php

namespace App\Http\Controllers;

use AiBridge\Facades\AiBridge;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
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
        return view('records.drag-drop');
    }

    /**
     * Traiter les fichiers uploadés via Drag & Drop avec IA
     */
    public function processDragDrop(Request $request)
    {
        Gate::authorize('records_create');

        Log::info('Début processDragDrop', [
            'files_count' => count($request->file('files', [])),
            'user_id' => Auth::id()
        ]);

        // Validation des fichiers et options
        $request->validate([
            'files' => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:pdf,txt,docx,doc,rtf,odt,jpg,jpeg,png,gif|max:51200', // 50MB max
            'per_file_char_limit' => 'nullable|integer|min:200|max:100000',
        ]);

        DB::beginTransaction();
        try {
            // 1. Créer un record temporaire
            Log::info('Création du record temporaire');
            $record = $this->createTemporaryRecord();
            Log::info('Record temporaire créé', ['record_id' => $record->id]);

            // 2. Traiter les fichiers uploadés
            Log::info('Traitement des fichiers uploadés');
            $attachments = $this->handleDragDropFiles($request->file('files'));
            Log::info('Fichiers traités', ['attachments_count' => $attachments->count()]);

            // 3. Associer les attachments au record
            Log::info('Association des attachments au record');
            $record->attachments()->attach($attachments->pluck('id'));

            // 4. Traiter avec l'IA
            Log::info('Début du traitement IA');
            // Limite de caractères par fichier (avec valeur par défaut configurable)
            $defaultPerFile = (int) app(\App\Services\SettingService::class)->get('ai_max_chars_per_file', 200);
            $perFileLimit = (int) ($request->input('per_file_char_limit', $defaultPerFile));
            // Clamp pour éviter les valeurs extrêmes
            $perFileLimit = max(200, min(100000, $perFileLimit));

            $aiResponse = $this->processWithAI($record, $attachments, $perFileLimit);
            Log::info('Traitement IA terminé', ['response_preview' => substr(json_encode($aiResponse), 0, 200)]);

            // 5. Mettre à jour le record avec les suggestions IA
            Log::info('Application des suggestions IA');
            $this->applyAiSuggestions($record, $aiResponse);

            DB::commit();
            Log::info('Transaction commitée avec succès');

            return response()->json([
                'success' => true,
                'record_id' => $record->id,
                'ai_suggestions' => $aiResponse,
                'message' => 'Record créé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur Drag & Drop: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un record temporaire
     */
    private function createTemporaryRecord(): Record
    {
        return Record::create([
            'code' => 'T' . substr(uniqid(), -8), // T + 8 chars = 9 chars total (within 10 char limit)
            'name' => 'Document en cours de traitement...',
            'date_format' => 'Y', // Format par défaut, sera mis à jour par l'IA
            'level_id' => RecordLevel::first()->id ?? 1,
            'status_id' => RecordStatus::first()->id ?? 1,
            'support_id' => RecordSupport::first()->id ?? 1,
            'activity_id' => Activity::first()->id ?? 1,
            'user_id' => Auth::id(),
            'content' => 'Record créé via Drag & Drop - En attente de traitement IA'
        ]);
    }

    /**
     * Traiter les fichiers uploadés
     */
    private function handleDragDropFiles(array $files): \Illuminate\Database\Eloquent\Collection
    {
        $attachmentIds = [];

        foreach ($files as $file) {
            // Stocker le fichier
            $path = $file->store('attachments/drag-drop');

            // Créer l'attachment
            $attachment = Attachment::create([
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'type' => $this->determineAttachmentType(),
                'creator_id' => Auth::id(),
                'crypt' => hash_file('md5', $file->getRealPath()),
                'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
                'thumbnail_path' => '', // Pas de thumbnail pour les fichiers drag & drop
            ]);

            $attachmentIds[] = $attachment->id;
        }

        // Retourner une vraie Eloquent Collection
        return Attachment::whereIn('id', $attachmentIds)->get();
    }

    /**
     * Déterminer le type d'attachment basé sur le MIME type
     * Les valeurs possibles sont: 'mail','record','communication','transferting','bulletinboardpost','bulletinboard','bulletinboardevent'
     */
    private function determineAttachmentType(): string
    {
        // Pour les attachments créés via drag & drop, ils sont toujours liés aux records
        return 'record';
    }

    /**
     * Traiter avec l'IA
     */
    private function processWithAI(Record $record, EloquentCollection $attachments, int $perFileLimit): array
    {
        try {
            Log::debug('processWithAI - record context', ['record_id' => $record->id ?? null]);
            // Extraire le texte de chaque attachment au lieu d'envoyer les fichiers binaires
            $contents = [];
            foreach ($attachments as $attachment) {
                $filePath = storage_path('app/' . $attachment->path);
                if (!file_exists($filePath)) {
                    Log::warning('Fichier introuvable pour extraction', [
                        'attachment_id' => $attachment->id ?? null,
                        'path' => $attachment->path,
                    ]);
                    continue;
                }

                $contents[] = $this->extractAttachmentContent($attachment, $filePath, $perFileLimit);
            }

            // Filtrer les extractions vides et tronquer pour éviter les prompts trop volumineux
            $contents = array_values(array_filter($contents, function ($c) {
                return !empty(trim($c['content'] ?? ''));
            }));

            if (empty($contents)) {
                Log::warning('Aucun contenu texte extrait des fichiers');
                return [
                    'title' => 'Document importé le ' . now()->format('d/m/Y'),
                    'content' => 'Document créé automatiquement via Drag & Drop.',
                    'keywords' => [],
                    'activity_suggestion' => null,
                    'confidence' => 0.0
                ];
            }

            // Appliquer une limite globale de caractères pour éviter les payloads trop gros
            $maxTotalChars = (int) (app(\App\Services\SettingService::class)->get('ai_max_total_chars', 40000));
            $contents = $this->limitAggregateContents($contents, $maxTotalChars);

            // Construire le prompt pour l'IA avec le texte extrait (potentiellement tronqué)
            $prompt = $this->buildDragDropPrompt($contents);

            // Appeler l'IA SANS envoyer les fichiers
            $provider = app(\App\Services\SettingService::class)->get('ai_default_provider', 'ollama');
            $model = app(\App\Services\SettingService::class)->get('ai_default_model', 'gemma3:4b');

            // S'assurer que le provider est configuré
            app(\App\Services\AI\ProviderRegistry::class)->ensureConfigured($provider);

            Log::info('Envoi à l\'IA avec texte extrait (sans fichiers)', [
                'provider' => $provider,
                'model' => $model,
                'per_file_char_limit' => $perFileLimit,
                'documents' => array_map(function ($c) {
                    return [
                        'filename' => $c['filename'] ?? 'unknown',
                        'type' => $c['type'] ?? 'unknown',
                        'chars' => strlen($c['content'] ?? ''),
                    ];
                }, $contents),
            ]);

            // Utiliser AiBridge uniquement avec le prompt textuel
            $aiResponse = AiBridge::provider($provider)->chat([
                ['role' => 'user', 'content' => $prompt]
            ], [
                'model' => $model,
                'temperature' => 0.3,
                'max_tokens' => 1000,
                'timeout' => 120000 // 2 minutes pour traiter le contenu
            ]);

            // Parser la réponse
            $content = $this->extractText(is_array($aiResponse) ? $aiResponse : (array)$aiResponse);

            Log::info('Réponse IA reçue', [
                'content_length' => strlen($content),
                'response_preview' => substr($content, 0, 200)
            ]);

            return $this->parseAiDragDropResponse($content);

        } catch (\Exception $e) {
            Log::error('Erreur traitement IA Drag & Drop: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Retourner une réponse par défaut
            return [
                'title' => 'Document importé le ' . now()->format('d/m/Y'),
                'content' => 'Document créé automatiquement via Drag & Drop.',
                'keywords' => [],
                'activity_suggestion' => null,
                'confidence' => 0.0
            ];
        }
    }

    /**
     * Extraire le contenu texte d'un attachment selon son type MIME
     * Retourne un tableau: [filename, type, content]
     */
    private function extractAttachmentContent($attachment, string $absolutePath, int $perFileLimit): array
    {
        $filename = isset($attachment->name) ? (string) $attachment->name : basename($absolutePath);
        $mime = isset($attachment->mime_type) ? (string) $attachment->mime_type : mime_content_type($absolutePath);

        $type = 'unknown';
        $text = '';

        try {
            if (stripos($mime, 'pdf') !== false) {
                $type = 'pdf';
                $text = $this->extractTextFromPdf($absolutePath);
            } elseif (stripos($mime, 'text/plain') !== false || stripos($mime, 'csv') !== false) {
                $type = 'text';
                $text = @file_get_contents($absolutePath) ?: '';
            } elseif (stripos($mime, 'wordprocessingml') !== false || stripos($mime, 'officedocument') !== false || stripos($mime, 'vnd.oasis.opendocument.text') !== false || stripos($mime, 'rtf') !== false) {
                // DOCX / ODT / RTF
                $type = 'word';
                $text = $this->extractTextFromWordLike($absolutePath);
            } elseif (stripos($mime, 'msword') !== false) {
                // Ancien .doc - souvent non supporté en lecture; tentative via PhpWord, sinon vide
                $type = 'msword';
                $text = $this->extractTextFromWordLike($absolutePath);
            } elseif (stripos($mime, 'image/') === 0) {
                $type = 'image';
                $text = $this->extractTextFromImage($absolutePath);
            } else {
                // Fallback basique: tenter lecture en texte
                $type = 'binary';
                $raw = @file_get_contents($absolutePath) ?: '';
                // Ne pas envoyer du binaire; tronquer et filtrer
                $text = $this->sanitizeToText($raw);
            }
        } catch (\Throwable $e) {
            Log::warning('Erreur extraction de texte', [
                'filename' => $filename,
                'mime' => $mime,
                'error' => $e->getMessage(),
            ]);
            $text = '';
        }

    // Nettoyage et troncature pour limiter la taille envoyée à l'IA
        $text = $this->normalizeWhitespace($text);
    $text = $this->truncateMiddle($text, $perFileLimit); // limite configurable par document

        return [
            'filename' => $filename,
            'type' => $type,
            'content' => $text,
        ];
    }

    /**
     * Extraction texte depuis PDF (texte natif), fallback OCR si nécessaire si Imagick + Tesseract dispos
     */
    private function extractTextFromPdf(string $path): string
    {
        $text = '';
        try {
            if (class_exists(\Smalot\PdfParser\Parser::class)) {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
            }
        } catch (\Throwable $e) {
            Log::info('PDFParser a échoué, tentative OCR si possible', ['error' => $e->getMessage()]);
        }

        // Si texte quasi vide, tenter OCR basique page par page si Imagick et Tesseract disponibles
        if (mb_strlen(trim($text)) < 30 && extension_loaded('imagick') && class_exists('Imagick') && class_exists(\thiagoalessio\TesseractOCR\TesseractOCR::class)) {
            try {
                $imClass = '\\Imagick';
                $imagick = new $imClass();
                // Résolution plus élevée pour de meilleurs résultats OCR
                $imagick->setResolution(300, 300);
                $imagick->readImage($path);
                $ocrText = '';
                foreach ($imagick as $i => $page) {
                    // Prétraitement: niveaux de gris, contraste, sharpen
                    $imagickClass = '\\Imagick';
                    $page->setImageColorspace($imagickClass::COLORSPACE_GRAY);
                    $page->enhanceImage();
                    $page->contrastStretchImage(0.1, 0.9);
                    $page->unsharpMaskImage(0.5, 0.5, 0.7, 0.0);
                    $page->setImageFormat('png');
                    $tmp = tempnam(sys_get_temp_dir(), 'pdfpg_') . '.png';
                    $page->writeImage($tmp);
                    try {
                        $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($tmp);
                        // Essayer FR puis EN
                        $ocr->lang('fra', 'eng');
                        // Améliorer la qualité d'extraction: OEM LSTM, PSM 3 (auto)
                        $ocr->oem(1)->psm(3);
                        $ocrText .= "\n" . $ocr->run();
                    } catch (\Throwable $e) {
                        Log::debug('OCR tesseract erreur page', ['page' => $i, 'error' => $e->getMessage()]);
                    } finally {
                        @unlink($tmp);
                    }
                }
                $text = trim($ocrText) ?: $text;
            } catch (\Throwable $e) {
                Log::debug('OCR PDF fallback échoué', ['error' => $e->getMessage()]);
            }
        }

        return $text;
    }

    /**
     * Extraction texte depuis images via Tesseract (si dispo)
     */
    private function extractTextFromImage(string $path): string
    {
        if (!class_exists(\thiagoalessio\TesseractOCR\TesseractOCR::class)) {
            return '';
        }
        try {
            $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($path);
            $ocr->lang('fra', 'eng');
            return $ocr->run();
        } catch (\Throwable $e) {
            Log::debug('OCR image échoué', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Extraction texte depuis DOCX/ODT/RTF (via PhpWord si disponible)
     */
    private function extractTextFromWordLike(string $path): string
    {
        if (!class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            return '';
        }
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            $stream = fopen('php://temp', 'r+');
            $writer->save($stream);
            rewind($stream);
            $html = stream_get_contents($stream);
            fclose($stream);
            // Nettoyer le HTML en texte brut
            return strip_tags($html);
        } catch (\Throwable $e) {
            Log::debug('Extraction WordLike échouée', ['error' => $e->getMessage()]);
            return '';
        }
    }

    /**
     * Remplacer le binaire par un texte sûr (enlevant les null bytes, non-UTF8)
     */
    private function sanitizeToText(string $raw): string
    {
        // Forcer en UTF-8
        $utf8 = @mb_convert_encoding($raw, 'UTF-8', 'auto');
        $utf8 = is_string($utf8) ? $utf8 : $raw;
        // Retirer les caractères de contrôle
        $utf8 = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $utf8);
        return $utf8 ?? '';
    }

    /**
     * Normaliser les espaces/blancs
     */
    private function normalizeWhitespace(string $text): string
    {
        // Unifier les fins de ligne et compresser les espaces répétés
        $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = str_replace("\xEF\xBB\xBF", '', $text); // remove UTF-8 BOM if present
        // Limiter les suites d'espaces
        $text = preg_replace('/[\t ]{2,}/', ' ', $text);
        // Limiter les lignes vides consécutives
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text);
    }

    /**
     * Tronquer une longue chaîne en conservant le début et la fin
     */
    private function truncateMiddle(string $text, int $maxLen): string
    {
        $len = mb_strlen($text);
        if ($len <= $maxLen) {
            return $text;
        }
        $keep = (int) floor($maxLen / 2);
        return mb_substr($text, 0, $keep) . "\n...\n" . mb_substr($text, -$keep);
    }

    /**
     * Limiter le volume total de texte agrégé envoyé à l'IA.
     * - maxTotalChars: budget global (ex. 40k)
     * - On réserve un peu pour l'en-tête par document, puis on répartit le budget de contenu.
     */
    private function limitAggregateContents(array $contents, int $maxTotalChars): array
    {
        // Estimations: en-tête par doc ~120 chars
        $headerPerDoc = 120;
        $docCount = max(1, count($contents));
        $reserved = $headerPerDoc * $docCount;
        $budget = max(1000, $maxTotalChars - $reserved); // garder un minimum

        // Calculer le total actuel
        $total = 0;
        foreach ($contents as $c) {
            $total += mb_strlen($c['content'] ?? '');
        }
        if ($total <= $budget) {
            return $contents; // rien à faire
        }

        // Répartir le budget proportionnellement
        $result = [];
        foreach ($contents as $c) {
            $text = $c['content'] ?? '';
            $len = max(1, mb_strlen($text));
            $share = (int) floor($budget * ($len / $total));
            $share = max(500, $share); // au moins un peu de contexte
            $c['content'] = $this->truncateMiddle($text, $share);
            $result[] = $c;
        }
        // Double check: ne pas dépasser le budget à cause des arrondis et du min.
        $used = 0; foreach ($result as $r) { $used += mb_strlen($r['content'] ?? ''); }
        while ($used > $budget) {
            $delta = $used - $budget;
            // Trouver l'index du doc le plus long
            $maxIdx = 0; $maxLen = -1;
            foreach ($result as $idx => $r) {
                $l = mb_strlen($r['content'] ?? '');
                if ($l > $maxLen) { $maxLen = $l; $maxIdx = $idx; }
            }
            if ($maxLen <= 600) { break; } // éviter de trop réduire
            $reduce = min($delta, max(100, (int) floor($maxLen * 0.1))); // réduire par pas raisonnables
            $newLen = max(500, $maxLen - $reduce);
            $result[$maxIdx]['content'] = $this->truncateMiddle($result[$maxIdx]['content'], $newLen);
            // recalculer 'used'
            $used = 0; foreach ($result as $r) { $used += mb_strlen($r['content'] ?? ''); }
        }
        return $result;
    }

        // buildDragDropPromptWithFiles() supprimée car non utilisée

    /**
     * Construire le prompt pour l'IA (version avec extraction de texte)
     */
    private function buildDragDropPrompt(array $contents): string
    {
        $contentText = '';
        foreach ($contents as $content) {
            $contentText .= "=== Fichier: {$content['filename']} ({$content['type']}) ===\n";
            $contentText .= $content['content'] . "\n\n";
        }

        return "Tu es un assistant spécialisé en archivage. Analyse le contenu suivant et propose une description structurée pour créer un record archivistique.

CONTENU À ANALYSER:
{$contentText}

INSTRUCTIONS:
1. Propose un titre pertinent et concis (max 100 caractères)
2. Rédige une description/résumé du contenu (max 500 mots)
3. Suggère 3-5 mots-clés pertinents
4. Identifie les dates dans le contenu (date_start, date_end si période, ou date_exact si date précise)
5. Évalue le niveau de confiance de tes suggestions (0-1)

RÉPONSE ATTENDUE (JSON strict):
{
  \"title\": \"Titre proposé\",
  \"content\": \"Description détaillée\",
  \"keywords\": [\"mot1\", \"mot2\", \"mot3\"],
  \"date_start\": \"YYYY-MM-DD ou YYYY ou YYYY-MM\",
  \"date_end\": \"YYYY-MM-DD ou YYYY ou YYYY-MM\",
  \"date_exact\": \"YYYY-MM-DD\",
  \"confidence\": 0.85,
  \"summary\": \"Résumé en une phrase\"
}

NOTES SUR LES DATES:
- Si tu trouves une date précise, utilise \"date_exact\"
- Si tu trouves une période, utilise \"date_start\" et \"date_end\"
- Si tu trouves seulement une année, utilise le format \"YYYY\"
- Si tu trouves année-mois, utilise le format \"YYYY-MM\"
- Laisse null les champs de date si aucune date n'est identifiable

Réponds UNIQUEMENT en JSON valide, sans texte additionnel.";
    }

    /**
     * Parser la réponse de l'IA
     */
    private function parseAiDragDropResponse(string $response): array
    {
        try {
            // Nettoyer la réponse
            $cleaned = trim($response);
            $cleaned = preg_replace('/^```json\s*/', '', $cleaned);
            $cleaned = preg_replace('/\s*```$/', '', $cleaned);

            $data = json_decode($cleaned, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('JSON invalide lors du parsing de la réponse IA', [
                    'error' => json_last_error_msg(),
                ]);
                return [
                    'title' => 'Document importé',
                    'content' => 'Contenu extrait automatiquement.',
                    'keywords' => [],
                    'confidence' => 0.0,
                    'summary' => '',
                    'date_start' => null,
                    'date_end' => null,
                    'date_exact' => null
                ];
            }

            return [
                'title' => $data['title'] ?? 'Document sans titre',
                'content' => $data['content'] ?? '',
                'keywords' => $data['keywords'] ?? [],
                'confidence' => $data['confidence'] ?? 0.5,
                'summary' => $data['summary'] ?? '',
                'date_start' => $data['date_start'] ?? null,
                'date_end' => $data['date_end'] ?? null,
                'date_exact' => $data['date_exact'] ?? null
            ];

        } catch (\Exception $e) {
            Log::warning('Erreur parsing réponse IA: ' . $e->getMessage(), [
                'response' => $response
            ]);

            return [
                'title' => 'Document importé',
                'content' => 'Contenu extrait automatiquement.',
                'keywords' => [],
                'confidence' => 0.0,
                'summary' => '',
                'date_start' => null,
                'date_end' => null,
                'date_exact' => null
            ];
        }
    }

    /**
     * Appliquer les suggestions de l'IA au record
     */
    private function applyAiSuggestions(Record $record, array $aiResponse): void
    {
        // Préparer les données de mise à jour
        $updateData = [
            'name' => $aiResponse['title'] ?? $record->name,
            'content' => $aiResponse['content'] ?? $record->content,
            'code' => 'DD' . now()->format('md') . sprintf('%03d', $record->id), // DD + MMDD + ID padded = max 10 chars
        ];

        // Traitement des dates suggérées par l'IA
        if (!empty($aiResponse['date_exact'])) {
            // Date exacte fournie
            $updateData['date_exact'] = $aiResponse['date_exact'];
            $updateData['date_format'] = 'D'; // Format complet AAAA/MM/DD
        } elseif (!empty($aiResponse['date_start']) || !empty($aiResponse['date_end'])) {
            // Période fournie
            $dateStart = $aiResponse['date_start'] ?? null;
            $dateEnd = $aiResponse['date_end'] ?? null;

            if ($dateStart) { $updateData['date_start'] = $dateStart; }
            if ($dateEnd) { $updateData['date_end'] = $dateEnd; }

            // Utiliser la méthode existante pour déterminer le format
            $updateData['date_format'] = $this->getDateFormat($dateStart, $dateEnd);
        }
        // Si aucune date n'est fournie, garder le format 'Y' par défaut

        // Mettre à jour le record
        $record->update($updateData);

        // Traiter les mots-clés si fournis
        if (!empty($aiResponse['keywords'])) {
            $keywords = \App\Models\Keyword::processKeywordsString(implode(', ', $aiResponse['keywords']));
            $record->keywords()->attach($keywords->pluck('id'));
        }
    }

    private function getDateFormat($dateStart, $dateEnd)
    {
        $format = 'Y';
        if (!empty($dateStart) && !empty($dateEnd)) {
            try {
                $start = new \DateTime($dateStart);
                $end = new \DateTime($dateEnd);
                if ($start->format('Y') !== $end->format('Y')) {
                    $format = 'Y';
                } elseif ($start->format('m') !== $end->format('m')) {
                    $format = 'M';
                } else {
                    $format = 'D';
                }
            } catch (\Exception $e) {
                $format = 'Y';
            }
        }
        return $format;
    }

    /**
     * Extraire le texte de la réponse IA (réutilise la méthode existante)
     */
    private function extractText($response): string
    {
        if (is_string($response)) {
            return $response;
        }

        if (is_array($response)) {
            return $response['content'] ?? $response['message'] ?? $response['text'] ?? json_encode($response);
        }

        return (string) $response;
    }
}
