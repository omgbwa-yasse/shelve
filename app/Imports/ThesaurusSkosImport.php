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

class ThesaurusSkosImport
{
    /**
     * Importer un fichier SKOS RDF
     *
     * @param string $path Chemin du fichier stocké
     * @param int|null $schemeId ID du schéma existant ou null pour en créer un nouveau
     * @param string $language Langue par défaut des labels
     * @param string $mergeMode Mode de fusion (replace, merge, append)
     * @return array Résultats de l'import
     */
    public function import(string $path, ?int $schemeId = null, string $language = 'fr-fr', string $mergeMode = 'append')
    {
        // Initialiser les compteurs
        $result = [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'relationships' => 0,
            'message' => 'Import en cours...'
        ];

        try {
            // Vérifier si le fichier existe
            $filePath = Storage::path($path);
            if (!file_exists($filePath)) {
                throw new \Exception("Le fichier n'existe pas à l'emplacement spécifié: {$filePath}");
            }

            // Vérifier si le fichier est lisible
            if (!is_readable($filePath)) {
                throw new \Exception("Le fichier n'est pas accessible en lecture: {$filePath}");
            }

            // Désactiver les avertissements XML pour capturer les erreurs proprement
            libxml_use_internal_errors(true);
            
            // Charger le fichier XML
            $xml = simplexml_load_file($filePath);

            // Vérifier si le chargement a réussi
            if ($xml === false) {
                $errors = libxml_get_errors();
                $errorMessages = [];
                
                foreach ($errors as $error) {
                    $errorMessages[] = "Ligne {$error->line}: {$error->message}";
                }
                
                libxml_clear_errors();
                throw new \Exception("Impossible de charger le fichier SKOS RDF. Erreurs XML: " . implode(", ", $errorMessages));
            }

            // Enregistrer les namespaces
            $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            $xml->registerXPathNamespace('skos', 'http://www.w3.org/2004/02/skos/core#');
            $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');

            // Trouver le ConceptScheme
            $conceptSchemes = $xml->xpath('//skos:ConceptScheme');

            // Gérer le schéma de thésaurus
            $scheme = null;
            if ($schemeId) {
                $scheme = ThesaurusScheme::find($schemeId);
                if (!$scheme) {
                    throw new \Exception("Le schéma de thésaurus spécifié n'existe pas.");
                }
            } else if (count($conceptSchemes) > 0) {
                // Créer un nouveau schéma à partir des données du fichier
                $schemeData = $conceptSchemes[0];
                $schemeUri = (string) $schemeData->attributes('rdf', true)->about;

                $title = $schemeData->xpath('./dc:title');
                $description = $schemeData->xpath('./dc:description');

                $scheme = new ThesaurusScheme();
                $scheme->identifier = $schemeUri;
                $scheme->title = count($title) > 0 ? (string) $title[0] : 'Thésaurus importé';
                $scheme->description = count($description) > 0 ? (string) $description[0] : null;
                $scheme->language = $language;
                $scheme->save();
            } else {
                // Créer un schéma par défaut
                $scheme = new ThesaurusScheme();
                $scheme->identifier = 'imported-thesaurus-' . date('YmdHis');
                $scheme->title = 'Thésaurus importé le ' . date('Y-m-d H:i:s');
                $scheme->language = $language;
                $scheme->save();
            }

            // Si mode 'replace', supprimer tous les concepts existants
            if ($mergeMode === 'replace') {
                ThesaurusConcept::where('scheme_id', $scheme->id)->delete();
            }

            // Trouver tous les concepts
            $concepts = $xml->xpath('//skos:Concept');
            $result['total'] = count($concepts);

            // Première passe : créer/mettre à jour les concepts
            $conceptUriMap = []; // Correspondance entre URI et IDs de concepts

            foreach ($concepts as $conceptData) {
                $result['processed']++;

                try {
                    $conceptUri = (string) $conceptData->attributes('rdf', true)->about;

                    // Chercher si le concept existe déjà
                    $concept = ThesaurusConcept::where('uri', $conceptUri)
                        ->where('scheme_id', $scheme->id)
                        ->first();

                    // Gérer le mode de fusion
                    if ($concept && $mergeMode === 'append') {
                        // En mode append, on saute les concepts existants
                        continue;
                    }

                    // Créer ou mettre à jour le concept
                    if (!$concept) {
                        $concept = new ThesaurusConcept();
                        $concept->uri = $conceptUri;
                        $concept->scheme_id = $scheme->id;

                        // Chercher la notation
                        $notation = $conceptData->xpath('./skos:notation');
                        $concept->notation = count($notation) > 0 ? (string) $notation[0] : null;

                        $concept->save();
                        $result['created']++;
                    } else {
                        $result['updated']++;
                    }

                    // Mémoriser la correspondance URI -> ID
                    $conceptUriMap[$conceptUri] = $concept->id;

                    // Traiter les labels préférés (prefLabel)
                    $prefLabels = $conceptData->xpath('./skos:prefLabel');
                    foreach ($prefLabels as $prefLabel) {
                        $lang = (string) $prefLabel->attributes('xml', true)->lang ?: $language;

                        // Supprimer les anciens labels préférés dans cette langue si en mode merge/replace
                        if ($mergeMode !== 'append') {
                            ThesaurusLabel::where('concept_id', $concept->id)
                                ->where('type', 'prefLabel')
                                ->where('language', $lang)
                                ->delete();
                        }

                        // Créer le nouveau label
                        ThesaurusLabel::create([
                            'concept_id' => $concept->id,
                            'type' => 'prefLabel',
                            'language' => $lang,
                            'literal_form' => (string) $prefLabel
                        ]);
                    }

                    // Traiter les labels alternatifs (altLabel)
                    $altLabels = $conceptData->xpath('./skos:altLabel');
                    foreach ($altLabels as $altLabel) {
                        $lang = (string) $altLabel->attributes('xml', true)->lang ?: $language;

                        // Vérifier si ce label existe déjà pour éviter les doublons
                        $labelExists = ThesaurusLabel::where('concept_id', $concept->id)
                            ->where('type', 'altLabel')
                            ->where('language', $lang)
                            ->where('literal_form', (string) $altLabel)
                            ->exists();

                        if (!$labelExists) {
                            ThesaurusLabel::create([
                                'concept_id' => $concept->id,
                                'type' => 'altLabel',
                                'language' => $lang,
                                'literal_form' => (string) $altLabel
                            ]);
                        }
                    }

                    // Traiter les notes
                    $noteTypes = ['definition', 'scopeNote', 'example', 'historyNote', 'editorialNote', 'changeNote', 'note'];
                    foreach ($noteTypes as $noteType) {
                        $notes = $conceptData->xpath('./skos:' . $noteType);
                        foreach ($notes as $note) {
                            $lang = (string) $note->attributes('xml', true)->lang ?: $language;

                            // Vérifier si cette note existe déjà
                            $noteExists = ThesaurusConceptNote::where('concept_id', $concept->id)
                                ->where('type', $noteType)
                                ->where('language', $lang)
                                ->where('content', (string) $note)
                                ->exists();

                            if (!$noteExists) {
                                ThesaurusConceptNote::create([
                                    'concept_id' => $concept->id,
                                    'type' => $noteType,
                                    'language' => $lang,
                                    'content' => (string) $note
                                ]);
                            }
                        }
                    }

                } catch (\Exception $e) {
                    Log::error('Erreur lors du traitement du concept: ' . $e->getMessage());
                    $result['errors']++;
                }
            }

            // Deuxième passe : créer les relations
            foreach ($concepts as $conceptData) {
                try {
                    $conceptUri = (string) $conceptData->attributes('rdf', true)->about;

                    // Vérifier si on a bien mappé ce concept
                    if (!isset($conceptUriMap[$conceptUri])) {
                        continue;
                    }

                    $sourceId = $conceptUriMap[$conceptUri];

                    // Traiter les relations broader
                    $broaderRelations = $conceptData->xpath('./skos:broader');
                    foreach ($broaderRelations as $broaderRelation) {
                        $targetUri = (string) $broaderRelation->attributes('rdf', true)->resource;

                        // Vérifier si le concept cible existe
                        if (isset($conceptUriMap[$targetUri])) {
                            $targetId = $conceptUriMap[$targetUri];

                            // Créer la relation si elle n'existe pas déjà
                            $relationExists = ThesaurusConceptRelation::where('source_concept_id', $sourceId)
                                ->where('target_concept_id', $targetId)
                                ->where('relation_type', 'broader')
                                ->exists();

                            if (!$relationExists) {
                                ThesaurusConceptRelation::create([
                                    'source_concept_id' => $sourceId,
                                    'target_concept_id' => $targetId,
                                    'relation_type' => 'broader'
                                ]);
                                $result['relationships']++;
                            }
                        }
                    }

                    // Traiter les relations related
                    $relatedRelations = $conceptData->xpath('./skos:related');
                    foreach ($relatedRelations as $relatedRelation) {
                        $targetUri = (string) $relatedRelation->attributes('rdf', true)->resource;

                        // Vérifier si le concept cible existe
                        if (isset($conceptUriMap[$targetUri])) {
                            $targetId = $conceptUriMap[$targetUri];

                            // Créer la relation si elle n'existe pas déjà
                            $relationExists = ThesaurusConceptRelation::where('source_concept_id', $sourceId)
                                ->where('target_concept_id', $targetId)
                                ->where('relation_type', 'related')
                                ->exists();

                            if (!$relationExists) {
                                ThesaurusConceptRelation::create([
                                    'source_concept_id' => $sourceId,
                                    'target_concept_id' => $targetId,
                                    'relation_type' => 'related'
                                ]);
                                $result['relationships']++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur lors du traitement des relations: ' . $e->getMessage());
                }
            }

            $result['message'] = "Import terminé avec succès. {$result['created']} concepts créés, {$result['updated']} mis à jour, {$result['relationships']} relations créées.";
            return $result;

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import SKOS: ' . $e->getMessage());
            $result['errors']++;
            $result['message'] = 'Erreur lors de l\'import: ' . $e->getMessage();
            return $result;
        }
    }
}
