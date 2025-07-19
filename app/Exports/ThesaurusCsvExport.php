<?php

namespace App\Exports;

use App\Models\ThesaurusScheme;
use Illuminate\Support\Str;

class ThesaurusCsvExport
{
    /**
     * Exporter un schéma de thésaurus au format CSV
     *
     * @param ThesaurusScheme $scheme Le schéma à exporter
     * @param bool $includeRelations Inclure les relations entre concepts
     * @param string $language Langue des labels à inclure prioritairement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export(ThesaurusScheme $scheme, bool $includeRelations = true, string $language = 'fr-fr')
    {
        // Préparer le nom du fichier
        $filename = Str::slug($scheme->identifier) . '-csv-' . date('Y-m-d') . '.csv';

        // Créer le contenu CSV
        $csv = $this->generateCsv($scheme, $includeRelations, $language);

        // Retourner la réponse pour le téléchargement
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Générer le contenu CSV
     */
    protected function generateCsv(ThesaurusScheme $scheme, bool $includeRelations, string $language)
    {
        $output = fopen('php://temp', 'r+');

        // En-têtes du CSV
        $headers = ['ID', 'URI', 'Notation', 'Label Préféré', 'Labels Alternatifs', 'Définition'];

        if ($includeRelations) {
            $headers[] = 'Termes Génériques';
            $headers[] = 'Termes Spécifiques';
            $headers[] = 'Termes Associés';
        }

        fputcsv($output, $headers);

        // Ajouter chaque concept
        foreach ($scheme->concepts as $concept) {
            // Récupérer le label préféré
            $prefLabel = $concept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                ?? $concept->labels->where('type', 'prefLabel')->first();
            $prefLabelText = $prefLabel ? $prefLabel->literal_form : '';

            // Récupérer les labels alternatifs
            $altLabels = $concept->labels->where('type', 'altLabel')->where('language', $language)
                ->pluck('literal_form')->join('; ');

            // Récupérer la définition
            $definition = $concept->notes->where('type', 'definition')->where('language', $language)->first()
                ?? $concept->notes->where('type', 'definition')->first();
            $definitionText = $definition ? $definition->content : '';

            $row = [
                $concept->id,
                $concept->uri,
                $concept->notation,
                $prefLabelText,
                $altLabels,
                $definitionText
            ];

            if ($includeRelations) {
                // Termes génériques
                $broaderTerms = $concept->sourceRelations->where('relation_type', 'broader')
                    ->map(function ($relation) use ($language) {
                        $targetConcept = $relation->targetConcept;
                        $targetLabel = $targetConcept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                            ?? $targetConcept->labels->where('type', 'prefLabel')->first();
                        return $targetLabel ? $targetLabel->literal_form : $targetConcept->notation;
                    })->join('; ');

                // Termes spécifiques
                $narrowerTerms = $concept->targetRelations->where('relation_type', 'broader')
                    ->map(function ($relation) use ($language) {
                        $sourceConcept = $relation->sourceConcept;
                        $sourceLabel = $sourceConcept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                            ?? $sourceConcept->labels->where('type', 'prefLabel')->first();
                        return $sourceLabel ? $sourceLabel->literal_form : $sourceConcept->notation;
                    })->join('; ');

                // Termes associés
                $relatedTerms = $concept->sourceRelations->where('relation_type', 'related')
                    ->map(function ($relation) use ($language) {
                        $targetConcept = $relation->targetConcept;
                        $targetLabel = $targetConcept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                            ?? $targetConcept->labels->where('type', 'prefLabel')->first();
                        return $targetLabel ? $targetLabel->literal_form : $targetConcept->notation;
                    })->join('; ');

                $row[] = $broaderTerms;
                $row[] = $narrowerTerms;
                $row[] = $relatedTerms;
            }

            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
