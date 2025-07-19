<?php

namespace App\Imports;

use App\Models\ThesaurusScheme;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusConceptNote;
use App\Models\ThesaurusConceptRelation;
use App\Exceptions\ThesaurusImportException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThesaurusJsonImport
{
    /**
     * @var array Résultat de l'import
     */
    protected array $result = [
        'total' => 0,
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
        'relationships' => 0,
        'message' => 'Import JSON en cours...'
    ];

    /**
     * @var array Map des URI des concepts à leurs IDs
     */
    protected array $conceptUriMap = [];

    /**
     * @var ThesaurusScheme Schéma de thésaurus
     */
    protected ThesaurusScheme $scheme;

    /**
     * Importer un fichier JSON
     *
     * @param string $path Chemin du fichier stocké
     * @param int|null $schemeId ID du schéma existant ou null pour en créer un nouveau
     * @param string $language Langue par défaut des labels
     * @param string $mergeMode Mode de fusion (replace, merge, append)
     * @return array Résultats de l'import
     */
    public function import(string $path, ?int $schemeId = null, string $language = 'fr-fr', string $mergeMode = 'append'): array
    {
        try {
            // Charger les données JSON
            $data = $this->loadAndValidateJson($path);

            // Obtenir ou créer le schéma
            $this->scheme = $this->getOrCreateScheme($schemeId, $data, $language);

            // Gérer le mode de remplacement
            if ($mergeMode === 'replace') {
                ThesaurusConcept::where('scheme_id', $this->scheme->getKey())->delete();
            }

            $this->result['total'] = count($data['concepts']);

            // Importer les concepts
            $this->importConcepts($data['concepts'], $language, $mergeMode);

            // Importer les relations
            $this->importRelations($data['concepts']);

            $this->result['message'] = "Import terminé avec succès. {$this->result['created']} concepts créés, {$this->result['updated']} mis à jour, {$this->result['relationships']} relations créées.";
            return $this->result;

        } catch (Exception $e) {
            Log::error('Erreur lors de l\'import JSON: ' . $e->getMessage());
            $this->result['errors']++;
            $this->result['message'] = 'Erreur lors de l\'import: ' . $e->getMessage();
            return $this->result;
        }
    }

    /**
     * Charger et valider le contenu JSON
     *
     * @param string $path
     * @return array
     * @throws ThesaurusImportException
     */
    protected function loadAndValidateJson(string $path): array
    {
        // Obtenir le contenu du fichier JSON
        $jsonContent = Storage::get($path);

        if (!$jsonContent) {
            throw new ThesaurusImportException(
                "Impossible de lire le fichier JSON.",
                ThesaurusImportException::ERROR_FILE_NOT_READABLE
            );
        }

        // Décoder le JSON
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ThesaurusImportException(
                "Erreur de décodage JSON: " . json_last_error_msg(),
                ThesaurusImportException::ERROR_JSON_DECODE
            );
        }

        // Valider la structure minimale
        if (!isset($data['concepts']) || !is_array($data['concepts'])) {
            throw new ThesaurusImportException(
                "Le fichier JSON doit contenir un tableau 'concepts'.",
                ThesaurusImportException::ERROR_INVALID_STRUCTURE
            );
        }

        return $data;
    }

    /**
     * Obtenir ou créer le schéma de thésaurus
     *
     * @param int|null $schemeId
     * @param array $data
     * @param string $language
     * @return ThesaurusScheme
     * @throws ThesaurusImportException
     */
    protected function getOrCreateScheme(?int $schemeId, array $data, string $language): ThesaurusScheme
    {
        if ($schemeId) {
            $scheme = ThesaurusScheme::find($schemeId);
            if (!$scheme) {
                throw new ThesaurusImportException(
                    "Le schéma de thésaurus spécifié n'existe pas.",
                    ThesaurusImportException::ERROR_SCHEME_NOT_FOUND
                );
            }
            return $scheme;
        }

        if (isset($data['scheme'])) {
            // Créer un schéma à partir des données JSON
            $scheme = new ThesaurusScheme();
            $scheme->identifier = $data['scheme']['identifier'] ?? 'json-import-' . date('YmdHis');
            $scheme->title = $data['scheme']['title'] ?? 'Thésaurus importé depuis JSON';
            $scheme->description = $data['scheme']['description'] ?? null;
            $scheme->language = $data['scheme']['language'] ?? $language;
            $scheme->save();
            return $scheme;
        }

        // Créer un schéma par défaut
        $scheme = new ThesaurusScheme();
        $scheme->identifier = 'json-import-' . date('YmdHis');
        $scheme->title = 'Thésaurus importé depuis JSON le ' . date('Y-m-d H:i:s');
        $scheme->language = $language;
        $scheme->save();
        return $scheme;
    }

    /**
     * Importer les concepts
     *
     * @param array $concepts
     * @param string $language
     * @param string $mergeMode
     * @return void
     */
    protected function importConcepts(array $concepts, string $language, string $mergeMode): void
    {
        foreach ($concepts as $conceptData) {
            $this->result['processed']++;

            try {
                // Valider les données minimales
                if (!isset($conceptData['uri']) && !isset($conceptData['notation'])) {
                    $this->result['errors']++;
                    continue;
                }

                // Trouver le libellé préféré pour l'affichage des messages
                $prefLabel = $this->findPreferredLabel($conceptData, $language);

                // Traiter le concept
                $concept = $this->processConceptData($conceptData, $prefLabel, $mergeMode);

                if (!$concept) {
                    continue; // Le concept existe déjà et le mode est 'append'
                }

                // Traiter les labels
                $this->processLabels($concept, $conceptData, $language, $mergeMode);

                // Traiter les notes
                $this->processNotes($concept, $conceptData, $language);

            } catch (Exception $e) {
                Log::error('Erreur lors du traitement du concept JSON: ' . $e->getMessage());
                $this->result['errors']++;
            }
        }
    }

    /**
     * Traiter les données d'un concept
     *
     * @param array $conceptData
     * @param string $prefLabel
     * @param string $mergeMode
     * @return ThesaurusConcept|null
     */
    protected function processConceptData(array $conceptData, string $prefLabel, string $mergeMode): ?ThesaurusConcept
    {
        // Vérifier si le concept existe déjà
        $concept = null;
        if (isset($conceptData['uri'])) {
            $concept = ThesaurusConcept::where('uri', $conceptData['uri'])
                ->where('scheme_id', $this->scheme->getKey())
                ->first();
        } elseif (isset($conceptData['notation'])) {
            $concept = ThesaurusConcept::where('notation', $conceptData['notation'])
                ->where('scheme_id', $this->scheme->getKey())
                ->first();
        }

        // Gérer le mode de fusion
        if ($concept && $mergeMode === 'append') {
            // En mode append, on saute les concepts existants
            return null;
        }

        // Créer ou mettre à jour le concept
        if (!$concept) {
            $concept = new ThesaurusConcept();
            $concept->scheme_id = $this->scheme->getKey();
            $concept->uri = $conceptData['uri'] ?? 'urn:concept:' . Str::slug($prefLabel) . ':' . Str::random(8);
            $concept->notation = $conceptData['notation'] ?? null;
            $concept->save();
            $this->result['created']++;
        } else {
            // Mettre à jour si nécessaire
            if (isset($conceptData['notation']) && empty($concept->notation)) {
                $concept->notation = $conceptData['notation'];
                $concept->save();
            }

            $this->result['updated']++;
        }

        // Stocker l'ID pour les relations
        $this->conceptUriMap[$conceptData['uri'] ?? $conceptData['notation']] = $concept->getKey();

        return $concept;
    }

    /**
     * Traiter les labels d'un concept
     *
     * @param ThesaurusConcept $concept
     * @param array $conceptData
     * @param string $language
     * @param string $mergeMode
     * @return void
     */
    protected function processLabels(ThesaurusConcept $concept, array $conceptData, string $language, string $mergeMode): void
    {
        if (!isset($conceptData['labels']) || !is_array($conceptData['labels'])) {
            return;
        }

        foreach ($conceptData['labels'] as $labelData) {
            if (!isset($labelData['type']) || !isset($labelData['literal_form'])) {
                continue;
            }

            $labelLang = $labelData['language'] ?? $language;

            // Si c'est un label préféré et en mode merge/replace, supprimer les anciens
            if ($labelData['type'] === 'prefLabel' && $mergeMode !== 'append') {
                ThesaurusLabel::where('concept_id', $concept->getKey())
                    ->where('type', 'prefLabel')
                    ->where('language', $labelLang)
                    ->delete();
            }

            // Créer le label
            ThesaurusLabel::firstOrCreate(
                [
                    'concept_id' => $concept->getKey(),
                    'type' => $labelData['type'],
                    'language' => $labelLang,
                    'literal_form' => $labelData['literal_form']
                ]
            );
        }
    }

    /**
     * Traiter les notes d'un concept
     *
     * @param ThesaurusConcept $concept
     * @param array $conceptData
     * @param string $language
     * @return void
     */
    protected function processNotes(ThesaurusConcept $concept, array $conceptData, string $language): void
    {
        if (!isset($conceptData['notes']) || !is_array($conceptData['notes'])) {
            return;
        }

        foreach ($conceptData['notes'] as $noteData) {
            if (!isset($noteData['type']) || !isset($noteData['content'])) {
                continue;
            }

            $noteLang = $noteData['language'] ?? $language;

            // Créer la note
            ThesaurusConceptNote::firstOrCreate(
                [
                    'concept_id' => $concept->getKey(),
                    'type' => $noteData['type'],
                    'language' => $noteLang,
                    'content' => $noteData['content']
                ]
            );
        }
    }

    /**
     * Importer les relations entre concepts
     *
     * @param array $concepts
     * @return void
     */
    protected function importRelations(array $concepts): void
    {
        foreach ($concepts as $conceptData) {
            if (!isset($conceptData['uri']) && !isset($conceptData['notation'])) {
                continue;
            }

            $conceptIdentifier = $conceptData['uri'] ?? $conceptData['notation'];

            if (!isset($this->conceptUriMap[$conceptIdentifier])) {
                continue;
            }

            $sourceId = $this->conceptUriMap[$conceptIdentifier];

            if (!isset($conceptData['relations']) || !is_array($conceptData['relations'])) {
                continue;
            }

            $this->processRelationType($sourceId, $conceptData['relations'], 'broader');
            $this->processRelationType($sourceId, $conceptData['relations'], 'narrower', true);
            $this->processRelationType($sourceId, $conceptData['relations'], 'related');
        }
    }

    /**
     * Traiter un type spécifique de relation
     *
     * @param int $sourceId
     * @param array $relations
     * @param string $relationType
     * @param bool $isInverse
     * @return void
     */
    protected function processRelationType(int $sourceId, array $relations, string $relationType, bool $isInverse = false): void
    {
        if (!isset($relations[$relationType]) || !is_array($relations[$relationType])) {
            return;
        }

        foreach ($relations[$relationType] as $relation) {
            $targetUri = $relation['uri'] ?? $relation['notation'] ?? null;

            if (!$targetUri || !isset($this->conceptUriMap[$targetUri])) {
                continue;
            }

            $targetId = $this->conceptUriMap[$targetUri];

            ThesaurusConceptRelation::firstOrCreate(
                [
                    'source_concept_id' => $isInverse ? $targetId : $sourceId,
                    'target_concept_id' => $isInverse ? $sourceId : $targetId,
                    'relation_type' => $isInverse ? 'broader' : $relationType
                ]
            );
            $this->result['relationships']++;
        }
    }

    /**
     * Trouver le libellé préféré d'un concept
     *
     * @param array $conceptData
     * @param string $language
     * @return string
     */
    protected function findPreferredLabel(array $conceptData, string $language): string
    {
        // Extraire le meilleur label selon la priorité
        $label = $this->extractBestLabel($conceptData, $language);

        // Si aucun label n'est trouvé, utiliser la notation ou l'URI
        return $label ?? $conceptData['notation'] ?? $conceptData['uri'] ?? 'Concept sans identifiant';
    }

    /**
     * Extraire le meilleur label disponible selon la priorité
     *
     * @param array $conceptData
     * @param string $language
     * @return string|null
     */
    protected function extractBestLabel(array $conceptData, string $language): ?string
    {
        // Vérifier si des labels existent
        if (!$this->hasValidLabels($conceptData)) {
            return null;
        }

        // Collecter les labels par priorité
        $labelCandidates = $this->collectLabelCandidates($conceptData, $language);

        // Retourner le meilleur label selon l'ordre de priorité
        return $labelCandidates['prefLabelInLanguage']
            ?? $labelCandidates['anyPrefLabel']
            ?? $labelCandidates['firstLabel'];
    }

    /**
     * Vérifier si le concept a des labels valides
     *
     * @param array $conceptData
     * @return bool
     */
    protected function hasValidLabels(array $conceptData): bool
    {
        return isset($conceptData['labels'])
            && is_array($conceptData['labels'])
            && !empty($conceptData['labels']);
    }

    /**
     * Collecter les candidats de labels selon leur priorité
     *
     * @param array $conceptData
     * @param string $language
     * @return array
     */
    protected function collectLabelCandidates(array $conceptData, string $language): array
    {
        $candidates = [
            'prefLabelInLanguage' => null,
            'anyPrefLabel' => null,
            'firstLabel' => null
        ];

        foreach ($conceptData['labels'] as $label) {
            if (!isset($label['literal_form'])) {
                continue;
            }

            // Collecter le premier label rencontré
            if ($candidates['firstLabel'] === null) {
                $candidates['firstLabel'] = $label['literal_form'];
            }

            // Collecter les labels préférés
            if (isset($label['type']) && $label['type'] === 'prefLabel') {
                if (($label['language'] ?? '') === $language) {
                    $candidates['prefLabelInLanguage'] = $label['literal_form'];
                } elseif ($candidates['anyPrefLabel'] === null) {
                    $candidates['anyPrefLabel'] = $label['literal_form'];
                }
            }
        }

        return $candidates;
    }
}
