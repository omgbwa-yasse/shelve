<?php

namespace App\Exports;

use App\Models\ThesaurusScheme;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ThesaurusSkosExport
{
    /**
     * Exporter un schéma de thésaurus au format SKOS
     *
     * @param ThesaurusScheme $scheme Le schéma à exporter
     * @param bool $includeRelations Inclure les relations entre concepts
     * @param string $language Langue des labels à inclure prioritairement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export(ThesaurusScheme $scheme, bool $includeRelations = true, string $language = 'fr-fr')
    {
        // Préparer le nom du fichier
        $filename = Str::slug($scheme->title) . '-skos-' . date('Y-m-d') . '.rdf';

        // Créer le contenu SKOS XML
        $xml = $this->generateSkosXml($scheme, $includeRelations, $language);

        // Retourner la réponse pour le téléchargement
        return response($xml)
            ->header('Content-Type', 'application/rdf+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Générer le contenu XML SKOS
     */
    protected function generateSkosXml(ThesaurusScheme $scheme, bool $includeRelations, string $language)
    {
        // Implémentation de la génération XML SKOS ici
        // Cet exemple est un modèle de base à compléter

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <rdf:RDF
                    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                    xmlns:skos="http://www.w3.org/2004/02/skos/core#"
                    xmlns:dc="http://purl.org/dc/elements/1.1/">

                    <skos:ConceptScheme rdf:about="' . $scheme->uri . '">
                        <dc:title>' . htmlspecialchars($scheme->title) . '</dc:title>
                        <dc:description>' . htmlspecialchars($scheme->description ?? '') . '</dc:description>
                        <dc:creator>' . htmlspecialchars($scheme->creator ?? '') . '</dc:creator>
                    </skos:ConceptScheme>';

        // Ajouter les concepts
        foreach ($scheme->concepts as $concept) {
            $xml .= '
                    <skos:Concept rdf:about="' . $concept->uri . '">
                        <skos:inScheme rdf:resource="' . $scheme->uri . '"/>';

            // Ajouter les labels préférés dans la langue demandée
            $prefLabel = $concept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                ?? $concept->labels->where('type', 'prefLabel')->first();

            if ($prefLabel) {
                $xml .= '
        <skos:prefLabel xml:lang="' . $prefLabel->language . '">' .
                    htmlspecialchars($prefLabel->literal_form) .
                '</skos:prefLabel>';
            }

            // Ajouter les labels alternatifs
            foreach ($concept->labels->where('type', 'altLabel') as $altLabel) {
                $xml .= '
        <skos:altLabel xml:lang="' . $altLabel->language . '">' .
                    htmlspecialchars($altLabel->literal_form) .
                '</skos:altLabel>';
            }

            // Ajouter les notes
            foreach ($concept->notes as $note) {
                $noteType = match($note->type) {
                    'definition' => 'definition',
                    'scopeNote' => 'scopeNote',
                    'example' => 'example',
                    'historyNote' => 'historyNote',
                    'editorialNote' => 'editorialNote',
                    'changeNote' => 'changeNote',
                    default => 'note'
                };

                $xml .= '
        <skos:' . $noteType . ' xml:lang="' . $note->language . '">' .
                    htmlspecialchars($note->content) .
                '</skos:' . $noteType . '>';
            }

            // Ajouter les relations si demandé
            if ($includeRelations) {
                // Relations hiérarchiques (broader/narrower)
                foreach ($concept->sourceRelations->where('relation_type', 'broader') as $relation) {
                    $xml .= '
        <skos:broader rdf:resource="' . $relation->targetConcept->uri . '"/>';
                }

                foreach ($concept->targetRelations->where('relation_type', 'broader') as $relation) {
                    $xml .= '
        <skos:narrower rdf:resource="' . $relation->sourceConcept->uri . '"/>';
                }

                // Relations associatives (related)
                foreach ($concept->sourceRelations->where('relation_type', 'related') as $relation) {
                    $xml .= '
        <skos:related rdf:resource="' . $relation->targetConcept->uri . '"/>';
                }
            }

            $xml .= '
    </skos:Concept>';
        }

        $xml .= '
</rdf:RDF>';

        return $xml;
    }
}
