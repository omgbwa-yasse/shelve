<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThesaurusSeeder extends Seeder
{
    private const DEFAULT_CREATED_DATE = '2013-07-05 10:06:35';
    private const DEFAULT_MODIFIED_DATE = '2012-11-22 00:00:00';

    /**
     * Seed the thesaurus tables with example data based on RDF structure
     */
    public function run(): void
    {
        $schemeId = $this->createDocumentaryTypologyScheme();
        $conceptIds = $this->createDocumentaryTypologyConcepts($schemeId);
        $this->createXLLabels($conceptIds);
        $this->createAlternativeLabels($conceptIds);
        $this->createAssociativeRelations($conceptIds);
        $this->createGeographicThesaurus();
    }

    private function createDocumentaryTypologyScheme(): int
    {
        $uri = 'http://data.culture.fr/thesaurus/resource/ark:/67717/T3';
        return DB::table('concept_schemes')->insertGetId([
            'uri' => $uri,
            'uri_hash' => hash('sha256', $uri),
            'identifier' => 'T3',
            'title' => 'Liste d\'autorité Typologie documentaire pour l\'indexation des archives locales',
            'description' => 'La liste d\'autorité "Typologie documentaire" rassemble des termes renvoyant à des catégories facilement identifiables de documents...',
            'creator' => 'Ministère de la culture et de la communication',
            'publisher' => 'Ministère de la culture et de la communication',
            'type' => 'Liste contrôlée',
            'rights' => 'CC-BY-SA',
            'language' => 'fr',
            'status' => 'active',
            'metadata' => json_encode([
                'modified' => '2014-03-19T11:40:04+0100',
                'coverage' => null,
                'subject' => null
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDocumentaryTypologyConcepts(int $schemeId): array
    {
        $concepts = [
            [
                'uri' => 'http://data.culture.fr/thesaurus/resource/ark:/67717/T3-34',
                'notation' => 'T3-34',
                'preferred_label' => 'bulle pontificale',
                'status' => 'approved',
                'iso_status' => 1,
                'is_top_concept' => true,
            ],
            [
                'uri' => 'http://data.culture.fr/thesaurus/resource/ark:/67717/T3-118',
                'notation' => 'T3-118',
                'preferred_label' => 'livre comptable',
                'status' => 'approved',
                'iso_status' => 1,
                'is_top_concept' => true,
            ],
            [
                'uri' => 'http://data.culture.fr/thesaurus/resource/ark:/67717/T3-56',
                'notation' => 'T3-56',
                'preferred_label' => 'chanson',
                'scope_note' => 'Correspond à la fois au texte des paroles et aux partitions musicales.',
                'status' => 'approved',
                'iso_status' => 1,
                'is_top_concept' => true,
            ],
            [
                'uri' => 'http://data.culture.fr/thesaurus/resource/ark:/67717/T3-41',
                'notation' => 'T3-41',
                'preferred_label' => 'carnet de santé',
                'scope_note' => 'Désigne le carnet médical d\'un individu.',
                'status' => 'approved',
                'iso_status' => 1,
                'is_top_concept' => false,
            ],
        ];

        $conceptIds = [];
        foreach ($concepts as $concept) {
            $conceptId = DB::table('concepts')->insertGetId([
                'concept_scheme_id' => $schemeId,
                'uri' => $concept['uri'],
                'uri_hash' => hash('sha256', $concept['uri']),
                'notation' => $concept['notation'],
                'preferred_label' => $concept['preferred_label'],
                'language' => 'fr',
                'scope_note' => $concept['scope_note'] ?? null,
                'status' => $concept['status'],
                'iso_status' => $concept['iso_status'],
                'is_top_concept' => $concept['is_top_concept'],
                'date_created' => self::DEFAULT_CREATED_DATE,
                'date_modified' => self::DEFAULT_MODIFIED_DATE,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $conceptIds[$concept['notation']] = $conceptId;
        }

        return $conceptIds;
    }

    private function createXLLabels(array $conceptIds): void
    {
        $labels = [
            [
                'uri' => 'http://data.culture.fr/thesaurus/resource/ark:/67717/a6c43a01-b378-42e5-8919-7e95d8a7c82a',
                'concept_id' => $conceptIds['T3-34'],
                'label_type' => 'prefLabel',
                'literal_form' => 'bulle pontificale',
                'language' => 'fr',
            ],
            [
                'uri' => 'http://data.culture.fr/thesaurus/resource/ark:/67717/f1c02cb8-3fe5-4600-a514-15ba9eef8db6',
                'concept_id' => $conceptIds['T3-118'],
                'label_type' => 'prefLabel',
                'literal_form' => 'livre comptable',
                'language' => 'fr',
            ],
        ];

        foreach ($labels as $label) {
            DB::table('xl_labels')->insert([
                'uri' => $label['uri'],
                'uri_hash' => hash('sha256', $label['uri']),
                'concept_id' => $label['concept_id'],
                'label_type' => $label['label_type'],
                'literal_form' => $label['literal_form'],
                'language' => $label['language'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createAlternativeLabels(array $conceptIds): void
    {
        DB::table('alternative_labels')->insert([
            [
                'concept_id' => $conceptIds['T3-118'],
                'label' => 'grand livre',
                'label_type' => 'altLabel',
                'language' => 'fr',
                'relation_type' => 'synonym',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concept_id' => $conceptIds['T3-118'],
                'label' => 'livre journal',
                'label_type' => 'altLabel',
                'language' => 'fr',
                'relation_type' => 'synonym',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concept_id' => $conceptIds['T3-118'],
                'label' => 'journal centralisateur',
                'label_type' => 'altLabel',
                'language' => 'fr',
                'relation_type' => 'synonym',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function createAssociativeRelations(array $conceptIds): void
    {
        // Exemple de relation associative (skos:related)
        // Note: Dans le RDF, on trouve "minute notariale" relié à un autre concept
        if (isset($conceptIds['T3-139']) && isset($conceptIds['T3-2'])) {
            DB::table('associative_relations')->insert([
                'concept1_id' => $conceptIds['T3-139'],
                'concept2_id' => $conceptIds['T3-2'],
                'relation_subtype' => 'general',
                'relation_uri' => 'http://data.culture.fr/thesaurus/ginco/ns/TermeAssocie',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createGeographicThesaurus(): void
    {
        // Exemple de schéma géographique (pour démontrer la multi-thésaurus)
        $geoSchemeUri = 'http://example.org/thesaurus/geographic';
        $geoSchemeId = DB::table('concept_schemes')->insertGetId([
            'uri' => $geoSchemeUri,
            'uri_hash' => hash('sha256', $geoSchemeUri),
            'identifier' => 'GEO',
            'title' => 'Thésaurus géographique',
            'description' => 'Vocabulaire contrôlé pour l\'indexation géographique',
            'creator' => 'Institution archivistique',
            'type' => 'Thésaurus',
            'language' => 'fr',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Concepts géographiques exemples
        $franceUri = 'http://example.org/geo/france';
        $geoConceptId = DB::table('concepts')->insertGetId([
            'concept_scheme_id' => $geoSchemeId,
            'uri' => $franceUri,
            'uri_hash' => hash('sha256', $franceUri),
            'notation' => 'GEO-FR',
            'preferred_label' => 'France',
            'language' => 'fr',
            'status' => 'approved',
            'is_top_concept' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $occitanieUri = 'http://example.org/geo/occitanie';
        $regionConceptId = DB::table('concepts')->insertGetId([
            'concept_scheme_id' => $geoSchemeId,
            'uri' => $occitanieUri,
            'uri_hash' => hash('sha256', $occitanieUri),
            'notation' => 'GEO-OCC',
            'preferred_label' => 'Occitanie',
            'language' => 'fr',
            'status' => 'approved',
            'is_top_concept' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Relation hiérarchique : Occitanie est plus spécifique que France
        DB::table('hierarchical_relations')->insert([
            'broader_concept_id' => $geoConceptId,
            'narrower_concept_id' => $regionConceptId,
            'relation_type' => 'generic',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
