<?php

namespace App\Exports;

use App\Models\ThesaurusScheme;
use Illuminate\Support\Str;

class ThesaurusRdfExport
{
    /**
     * Exporter un schéma de thésaurus au format RDF
     *
     * @param ThesaurusScheme $scheme Le schéma à exporter
     * @param bool $includeRelations Inclure les relations entre concepts
     * @param string $language Langue des labels à inclure prioritairement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export(ThesaurusScheme $scheme, bool $includeRelations = true, string $language = 'fr-fr')
    {
        // Préparer le nom du fichier
        $filename = Str::slug($scheme->identifier) . '-rdf-' . date('Y-m-d') . '.rdf';

        // Créer le contenu RDF
        $rdf = $this->generateRdf($scheme, $includeRelations, $language);

        // Retourner la réponse pour le téléchargement
        return response($rdf)
            ->header('Content-Type', 'application/rdf+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Générer le contenu RDF
     */
    protected function generateRdf(ThesaurusScheme $scheme, bool $includeRelations, string $language)
    {
        // Implémentation de la génération RDF ici
        // Pour le moment, nous retournons un exemple minimal

        $rdf = '<?xml version="1.0" encoding="UTF-8"?>
            <rdf:RDF
                xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
                xmlns:dc="http://purl.org/dc/elements/1.1/">

                <rdfs:Class rdf:about="' . $scheme->identifier . '">
                    <rdfs:label>' . htmlspecialchars($scheme->title ?? $scheme->identifier) . '</rdfs:label>
                    <rdfs:comment>' . htmlspecialchars($scheme->description ?? '') . '</rdfs:comment>
                </rdfs:Class>';

        // Ajouter les concepts
        foreach ($scheme->concepts as $concept) {
            // Récupérer le label préféré
            $prefLabel = $concept->labels->where('type', 'prefLabel')->where('language', $language)->first()
                ?? $concept->labels->where('type', 'prefLabel')->first();

            $rdf .= '
    <rdf:Description rdf:about="' . $concept->uri . '">
        <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"/>
        <rdfs:label>' . htmlspecialchars($prefLabel ? $prefLabel->literal_form : $concept->notation) . '</rdfs:label>
    </rdf:Description>';
        }

        $rdf .= '
</rdf:RDF>';

        return $rdf;
    }
}
