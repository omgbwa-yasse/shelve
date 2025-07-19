<?php

namespace App\Imports;

use App\Models\ThesaurusScheme;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusConceptNote;
use App\Models\ThesaurusConceptRelation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThesaurusCsvImport
{
    /**
     * Importer un fichier CSV
     *
     * @param string $path Chemin du fichier stocké
     * @param int|null $schemeId ID du schéma existant ou null pour en créer un nouveau
     * @param string $language Langue par défaut des labels
     * @param string $mergeMode Mode de fusion (replace, merge, append)
     * @return array Résultats de l'import
     */
    public function import(string $path, ?int $schemeId = null, string $language = 'fr-fr', string $mergeMode = 'append')
    {
        // Structure de base du résultat
        $result = [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'relationships' => 0,
            'message' => 'Import CSV en cours...'
        ];

        try {
            // Obtenir le chemin complet du fichier
            $filePath = Storage::path($path);

            // Vérifier si le fichier existe
            if (!file_exists($filePath)) {
                throw new \Exception("Le fichier CSV n'existe pas.");
            }

            // Ouvrir le fichier
            $file = fopen($filePath, 'r');
            if (!$file) {
                throw new \Exception("Impossible d'ouvrir le fichier CSV.");
            }

            // Lire les en-têtes
            $headers = fgetcsv($file);
            if (!$headers) {
                throw new \Exception("Impossible de lire les en-têtes du CSV.");
            }

            // Analyser les en-têtes pour identifier les colonnes
            $columnMap = $this->mapColumns($headers);

            if (!isset($columnMap['id']) && !isset($columnMap['uri']) && !isset($columnMap['notation'])) {
                throw new \Exception("Le CSV doit contenir au moins une colonne 'ID', 'URI' ou 'Notation'.");
            }

            if (!isset($columnMap['label'])) {
                throw new \Exception("Le CSV doit contenir une colonne 'Label' ou 'Label Préféré'.");
            }

            // Obtenir ou créer le schéma de thésaurus
            $scheme = $this->getOrCreateScheme($schemeId, $language);

            // Si mode 'replace', supprimer tous les concepts existants
            if ($mergeMode === 'replace' && $scheme) {
                ThesaurusConcept::where('scheme_id', $scheme->id)->delete();
            }

            // Compter le nombre total de lignes (approximatif pour les grands fichiers)
            $lineCount = $this->countCsvLines($filePath);
            $result['total'] = $lineCount - 1; // -1 pour les en-têtes

            // Créer des tableaux pour stocker temporairement les informations de relations
            $broaderRelations = [];
            $narrowerRelations = [];
            $relatedRelations = [];
            $conceptIdMap = []; // Pour mapper les notations/uris vers les IDs internes

            // Lire et traiter les lignes
            while (($row = fgetcsv($file)) !== false) {
                $result['processed']++;

                try {
                    // Extraire les données
                    $data = [];
                    foreach ($columnMap as $field => $index) {
                        $data[$field] = isset($row[$index]) ? $row[$index] : null;
                    }

                    // Valider les données minimales requises
                    if (empty($data['label'])) {
                        $result['errors']++;
                        continue;
                    }

                    // Identifier de manière unique le concept
                    $identifier = null;
                    $identifierType = null;

                    if (!empty($data['id'])) {
                        $identifier = $data['id'];
                        $identifierType = 'custom_id';
                    } elseif (!empty($data['uri'])) {
                        $identifier = $data['uri'];
                        $identifierType = 'uri';
                    } elseif (!empty($data['notation'])) {
                        $identifier = $data['notation'];
                        $identifierType = 'notation';
                    } else {
                        // Générer un identifiant basé sur le label
                        $identifier = Str::slug($data['label']) . '-' . Str::random(8);
                        $identifierType = 'generated';
                    }

                    // Vérifier si le concept existe déjà
                    $concept = null;
                    switch ($identifierType) {
                        case 'uri':
                            $concept = ThesaurusConcept::where('uri', $identifier)
                                ->where('scheme_id', $scheme->id)
                                ->first();
                            break;
                        case 'notation':
                            $concept = ThesaurusConcept::where('notation', $identifier)
                                ->where('scheme_id', $scheme->id)
                                ->first();
                            break;
                        case 'custom_id':
                        case 'generated':
                        default:
                            // Chercher par libellé préféré
                            $concept = ThesaurusConcept::whereHas('labels', function ($query) use ($data, $language) {
                                $query->where('type', 'prefLabel')
                                    ->where('language', $language)
                                    ->where('literal_form', $data['label']);
                            })->where('scheme_id', $scheme->id)->first();
                            break;
                    }

                    // Gérer le mode de fusion
                    if ($concept && $mergeMode === 'append') {
                        // En mode append, on saute les concepts existants
                        continue;
                    }

                    // Créer ou mettre à jour le concept
                    if (!$concept) {
                        $concept = new ThesaurusConcept();
                        $concept->scheme_id = $scheme->id;

                        if ($identifierType === 'uri') {
                            $concept->uri = $identifier;
                        } elseif ($identifierType === 'notation') {
                            $concept->notation = $identifier;
                        } else {
                            // Générer une URI basée sur le label et un identifiant aléatoire
                            $concept->uri = 'urn:concept:' . Str::slug($data['label']) . ':' . Str::random(8);
                        }

                        if (!empty($data['notation']) && $identifierType !== 'notation') {
                            $concept->notation = $data['notation'];
                        }

                        $concept->save();
                        $result['created']++;
                    } else {
                        // Mettre à jour les champs si nécessaire
                        if (!empty($data['notation']) && empty($concept->notation)) {
                            $concept->notation = $data['notation'];
                            $concept->save();
                        }
                        if (!empty($data['uri']) && empty($concept->uri)) {
                            $concept->uri = $data['uri'];
                            $concept->save();
                        }
                        $result['updated']++;
                    }

                    // Stocker l'ID interne pour les relations
                    $conceptIdMap[$identifier] = $concept->id;

                    // Créer le label préféré
                    if ($mergeMode !== 'append' || !$concept->labels()->where('type', 'prefLabel')->exists()) {
                        ThesaurusLabel::updateOrCreate(
                            [
                                'concept_id' => $concept->id,
                                'type' => 'prefLabel',
                                'language' => $language
                            ],
                            [
                                'literal_form' => $data['label']
                            ]
                        );
                    }

                    // Traiter les labels alternatifs s'ils existent
                    if (!empty($data['alt_labels'])) {
                        $altLabels = explode(';', $data['alt_labels']);
                        foreach ($altLabels as $altLabel) {
                            $altLabel = trim($altLabel);
                            if (!empty($altLabel)) {
                                ThesaurusLabel::firstOrCreate(
                                    [
                                        'concept_id' => $concept->id,
                                        'type' => 'altLabel',
                                        'language' => $language,
                                        'literal_form' => $altLabel
                                    ]
                                );
                            }
                        }
                    }

                    // Traiter la définition si elle existe
                    if (!empty($data['definition'])) {
                        ThesaurusConceptNote::updateOrCreate(
                            [
                                'concept_id' => $concept->id,
                                'type' => 'definition',
                                'language' => $language
                            ],
                            [
                                'content' => $data['definition']
                            ]
                        );
                    }

                    // Stocker les relations pour traitement ultérieur
                    if (!empty($data['broader'])) {
                        $broaderTerms = explode(';', $data['broader']);
                        foreach ($broaderTerms as $term) {
                            $term = trim($term);
                            if (!empty($term)) {
                                $broaderRelations[] = [
                                    'source_id' => $concept->id,
                                    'target_term' => $term
                                ];
                            }
                        }
                    }

                    if (!empty($data['narrower'])) {
                        $narrowerTerms = explode(';', $data['narrower']);
                        foreach ($narrowerTerms as $term) {
                            $term = trim($term);
                            if (!empty($term)) {
                                $narrowerRelations[] = [
                                    'source_id' => $concept->id,
                                    'target_term' => $term
                                ];
                            }
                        }
                    }

                    if (!empty($data['related'])) {
                        $relatedTerms = explode(';', $data['related']);
                        foreach ($relatedTerms as $term) {
                            $term = trim($term);
                            if (!empty($term)) {
                                $relatedRelations[] = [
                                    'source_id' => $concept->id,
                                    'target_term' => $term
                                ];
                            }
                        }
                    }

                } catch (\Exception $e) {
                    Log::error('Erreur lors du traitement de la ligne CSV: ' . $e->getMessage());
                    $result['errors']++;
                }
            }

            // Fermer le fichier
            fclose($file);

            // Traiter les relations une fois que tous les concepts sont créés
            $this->processRelations($broaderRelations, $narrowerRelations, $relatedRelations, $conceptIdMap, $scheme, $result);

            $result['message'] = "Import terminé avec succès. {$result['created']} concepts créés, {$result['updated']} mis à jour, {$result['relationships']} relations créées.";
            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import CSV: ' . $e->getMessage());
            $result['errors']++;
            $result['message'] = 'Erreur lors de l\'import: ' . $e->getMessage();
            return $result;
        }
    }

    /**
     * Mapper les colonnes du CSV aux champs attendus
     */
    protected function mapColumns(array $headers)
    {
        $columnMap = [];
        $normalizedHeaders = array_map('strtolower', $headers);

        $possibleMappings = [
            'id' => ['id', 'concept_id', 'conceptid'],
            'uri' => ['uri', 'url', 'iri'],
            'notation' => ['notation', 'code', 'identifier'],
            'label' => ['label', 'préféré', 'prefere', 'prefere', 'preflabel', 'label préféré', 'label prefere', 'preferred label'],
            'alt_labels' => ['alt_labels', 'labels alternatifs', 'labels_alternatifs', 'alternative labels', 'altlabel', 'alt label', 'synonymes'],
            'definition' => ['definition', 'définition', 'desc', 'description', 'note'],
            'broader' => ['broader', 'générique', 'generique', 'terme générique', 'terme generique', 'broader term'],
            'narrower' => ['narrower', 'spécifique', 'specifique', 'terme spécifique', 'terme specifique', 'narrower term'],
            'related' => ['related', 'associé', 'associe', 'terme associé', 'terme associe', 'related term']
        ];

        foreach ($possibleMappings as $field => $possibleNames) {
            foreach ($possibleNames as $name) {
                $index = array_search($name, $normalizedHeaders);
                if ($index !== false) {
                    $columnMap[$field] = $index;
                    break;
                }
            }
        }

        return $columnMap;
    }

    /**
     * Obtenir ou créer un schéma de thésaurus
     */
    protected function getOrCreateScheme(?int $schemeId, string $language)
    {
        if ($schemeId) {
            $scheme = ThesaurusScheme::find($schemeId);
            if (!$scheme) {
                throw new \Exception("Le schéma de thésaurus spécifié n'existe pas.");
            }
            return $scheme;
        }

        // Créer un nouveau schéma
        $scheme = new ThesaurusScheme();
        $scheme->identifier = 'csv-import-' . date('YmdHis');
        $scheme->title = 'Thésaurus importé depuis CSV le ' . date('Y-m-d H:i:s');
        $scheme->language = $language;
        $scheme->save();

        return $scheme;
    }

    /**
     * Compter approximativement le nombre de lignes dans un fichier CSV
     */
    protected function countCsvLines(string $filePath)
    {
        $lineCount = 0;
        $handle = fopen($filePath, 'r');
        while (!feof($handle)) {
            $line = fgets($handle);
            $lineCount++;
        }
        fclose($handle);
        return $lineCount;
    }

    /**
     * Traiter les relations entre concepts
     */
    protected function processRelations(array $broaderRelations, array $narrowerRelations, array $relatedRelations, array $conceptIdMap, ThesaurusScheme $scheme, array &$result)
    {
        // Créer une map inversée des labels vers les IDs
        $labelToIdMap = [];
        $concepts = ThesaurusConcept::where('scheme_id', $scheme->id)
            ->with(['labels' => function ($query) {
                $query->where('type', 'prefLabel');
            }])
            ->get();

        foreach ($concepts as $concept) {
            foreach ($concept->labels as $label) {
                $labelToIdMap[strtolower($label->literal_form)] = $concept->id;
            }
            if ($concept->notation) {
                $labelToIdMap[strtolower($concept->notation)] = $concept->id;
            }
        }

        // Traiter les relations broader
        foreach ($broaderRelations as $relation) {
            $sourceId = $relation['source_id'];
            $targetTerm = $relation['target_term'];

            // Essayer de trouver l'ID du concept cible
            $targetId = null;
            if (isset($conceptIdMap[$targetTerm])) {
                $targetId = $conceptIdMap[$targetTerm];
            } elseif (isset($labelToIdMap[strtolower($targetTerm)])) {
                $targetId = $labelToIdMap[strtolower($targetTerm)];
            }

            if ($targetId) {
                ThesaurusConceptRelation::firstOrCreate([
                    'source_concept_id' => $sourceId,
                    'target_concept_id' => $targetId,
                    'relation_type' => 'broader'
                ]);
                $result['relationships']++;
            }
        }

        // Traiter les relations narrower
        foreach ($narrowerRelations as $relation) {
            $sourceId = $relation['source_id'];
            $targetTerm = $relation['target_term'];

            // Essayer de trouver l'ID du concept cible
            $targetId = null;
            if (isset($conceptIdMap[$targetTerm])) {
                $targetId = $conceptIdMap[$targetTerm];
            } elseif (isset($labelToIdMap[strtolower($targetTerm)])) {
                $targetId = $labelToIdMap[strtolower($targetTerm)];
            }

            if ($targetId) {
                ThesaurusConceptRelation::firstOrCreate([
                    'source_concept_id' => $targetId,
                    'target_concept_id' => $sourceId,
                    'relation_type' => 'broader'
                ]);
                $result['relationships']++;
            }
        }

        // Traiter les relations related
        foreach ($relatedRelations as $relation) {
            $sourceId = $relation['source_id'];
            $targetTerm = $relation['target_term'];

            // Essayer de trouver l'ID du concept cible
            $targetId = null;
            if (isset($conceptIdMap[$targetTerm])) {
                $targetId = $conceptIdMap[$targetTerm];
            } elseif (isset($labelToIdMap[strtolower($targetTerm)])) {
                $targetId = $labelToIdMap[strtolower($targetTerm)];
            }

            if ($targetId) {
                ThesaurusConceptRelation::firstOrCreate([
                    'source_concept_id' => $sourceId,
                    'target_concept_id' => $targetId,
                    'relation_type' => 'related'
                ]);
                $result['relationships']++;
            }
        }
    }
}
