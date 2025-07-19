<?php

namespace App\Exports;

use App\Models\ThesaurusScheme;
use Illuminate\Support\Str;

class ThesaurusJsonExport
{
    /**
     * Exporter un schéma de thésaurus au format JSON
     *
     * @param ThesaurusScheme $scheme Le schéma à exporter
     * @param bool $includeRelations Inclure les relations entre concepts
     * @param string $language Langue des labels à inclure prioritairement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export(ThesaurusScheme $scheme, bool $includeRelations = true, string $language = 'fr-fr')
    {
        // Préparer le nom du fichier
        $filename = Str::slug($scheme->identifier) . '-json-' . date('Y-m-d') . '.json';

        // Créer le contenu JSON
        $json = $this->generateJson($scheme, $includeRelations, $language);

        // Retourner la réponse pour le téléchargement
        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Générer le contenu JSON
     */
    protected function generateJson(ThesaurusScheme $scheme, bool $includeRelations, string $language)
    {
        $data = [
            'scheme' => [
                'id' => $scheme->id,
                'identifier' => $scheme->identifier,
                'title' => $scheme->title,
                'description' => $scheme->description,
                'language' => $scheme->language,
                'created_at' => $scheme->created_at->toIso8601String(),
                'updated_at' => $scheme->updated_at->toIso8601String(),
                'organization' => $scheme->organizations->first() ? [
                    'id' => $scheme->organizations->first()->id,
                    'name' => $scheme->organizations->first()->name
                ] : null
            ],
            'concepts' => []
        ];

        // Ajouter les concepts
        foreach ($scheme->concepts as $concept) {
            $conceptData = [
                'id' => $concept->id,
                'uri' => $concept->uri,
                'notation' => $concept->notation,
                'labels' => []
            ];

            // Ajouter les labels
            foreach ($concept->labels as $label) {
                $conceptData['labels'][] = [
                    'type' => $label->type,
                    'language' => $label->language,
                    'literal_form' => $label->literal_form
                ];
            }

            // Ajouter les notes
            $conceptData['notes'] = [];
            foreach ($concept->notes as $note) {
                $conceptData['notes'][] = [
                    'type' => $note->type,
                    'language' => $note->language,
                    'content' => $note->content
                ];
            }

            // Ajouter les relations si demandé
            if ($includeRelations) {
                // Relations hiérarchiques (broader/narrower)
                $conceptData['relations'] = [
                    'broader' => [],
                    'narrower' => [],
                    'related' => []
                ];

                // Termes génériques (broader)
                foreach ($concept->sourceRelations->where('relation_type', 'broader') as $relation) {
                    $targetConcept = $relation->targetConcept;
                    $targetLabel = $targetConcept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                        ?? $targetConcept->labels->where('type', 'prefLabel')->first();

                    $conceptData['relations']['broader'][] = [
                        'id' => $targetConcept->id,
                        'uri' => $targetConcept->uri,
                        'notation' => $targetConcept->notation,
                        'label' => $targetLabel ? $targetLabel->literal_form : null
                    ];
                }

                // Termes spécifiques (narrower)
                foreach ($concept->targetRelations->where('relation_type', 'broader') as $relation) {
                    $sourceConcept = $relation->sourceConcept;
                    $sourceLabel = $sourceConcept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                        ?? $sourceConcept->labels->where('type', 'prefLabel')->first();

                    $conceptData['relations']['narrower'][] = [
                        'id' => $sourceConcept->id,
                        'uri' => $sourceConcept->uri,
                        'notation' => $sourceConcept->notation,
                        'label' => $sourceLabel ? $sourceLabel->literal_form : null
                    ];
                }

                // Termes associés (related)
                foreach ($concept->sourceRelations->where('relation_type', 'related') as $relation) {
                    $targetConcept = $relation->targetConcept;
                    $targetLabel = $targetConcept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                        ?? $targetConcept->labels->where('type', 'prefLabel')->first();

                    $conceptData['relations']['related'][] = [
                        'id' => $targetConcept->id,
                        'uri' => $targetConcept->uri,
                        'notation' => $targetConcept->notation,
                        'label' => $targetLabel ? $targetLabel->literal_form : null
                    ];
                }
            }

            $data['concepts'][] = $conceptData;
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
