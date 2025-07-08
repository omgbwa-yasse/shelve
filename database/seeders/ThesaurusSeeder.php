<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ThesaurusScheme;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusConceptNote;
use App\Models\ThesaurusConceptRelation;
use App\Models\ThesaurusOrganization;
use App\Models\ThesaurusNamespace;

class ThesaurusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer une organisation exemple
        $organization = ThesaurusOrganization::firstOrCreate([
            'name' => 'Archives nationales'
        ], [
            'homepage' => 'https://www.archives-nationales.fr',
            'email' => 'contact@archives-nationales.fr'
        ]);

        // Créer des namespaces
        $skosNamespace = ThesaurusNamespace::firstOrCreate([
            'prefix' => 'skos'
        ], [
            'namespace_uri' => 'http://www.w3.org/2004/02/skos/core#'
        ]);

        $dcNamespace = ThesaurusNamespace::firstOrCreate([
            'prefix' => 'dc'
        ], [
            'namespace_uri' => 'http://purl.org/dc/elements/1.1/'
        ]);

        // Créer un schéma de thésaurus
        $scheme = ThesaurusScheme::firstOrCreate([
            'uri' => 'http://exemple.fr/thesaurus/archives'
        ], [
            'identifier' => 'THES-ARCH-001',
            'title' => 'Thésaurus des Archives',
            'description' => 'Vocabulaire contrôlé pour la description des archives',
            'language' => 'fr-fr'
        ]);

        // Créer des concepts de base
        $conceptDocuments = ThesaurusConcept::firstOrCreate([
            'uri' => 'http://exemple.fr/thesaurus/archives/c001'
        ], [
            'scheme_id' => $scheme->id,
            'notation' => 'DOC',
            'status' => 1
        ]);

        $conceptArchives = ThesaurusConcept::firstOrCreate([
            'uri' => 'http://exemple.fr/thesaurus/archives/c002'
        ], [
            'scheme_id' => $scheme->id,
            'notation' => 'ARCH',
            'status' => 1
        ]);

        $conceptCorrespondance = ThesaurusConcept::firstOrCreate([
            'uri' => 'http://exemple.fr/thesaurus/archives/c003'
        ], [
            'scheme_id' => $scheme->id,
            'notation' => 'CORR',
            'status' => 1
        ]);

        $conceptRegistres = ThesaurusConcept::firstOrCreate([
            'uri' => 'http://exemple.fr/thesaurus/archives/c004'
        ], [
            'scheme_id' => $scheme->id,
            'notation' => 'REG',
            'status' => 1
        ]);

        // Créer des libellés (labels)
        ThesaurusLabel::firstOrCreate([
            'concept_id' => $conceptDocuments->id,
            'type' => 'prefLabel',
            'literal_form' => 'Documents'
        ], [
            'language' => 'fr-fr'
        ]);

        ThesaurusLabel::firstOrCreate([
            'concept_id' => $conceptDocuments->id,
            'type' => 'altLabel',
            'literal_form' => 'Documentations'
        ], [
            'language' => 'fr-fr'
        ]);

        ThesaurusLabel::firstOrCreate([
            'concept_id' => $conceptArchives->id,
            'type' => 'prefLabel',
            'literal_form' => 'Archives'
        ], [
            'language' => 'fr-fr'
        ]);

        ThesaurusLabel::firstOrCreate([
            'concept_id' => $conceptCorrespondance->id,
            'type' => 'prefLabel',
            'literal_form' => 'Correspondance'
        ], [
            'language' => 'fr-fr'
        ]);

        ThesaurusLabel::firstOrCreate([
            'concept_id' => $conceptRegistres->id,
            'type' => 'prefLabel',
            'literal_form' => 'Registres'
        ], [
            'language' => 'fr-fr'
        ]);

        // Créer des notes conceptuelles
        ThesaurusConceptNote::create([
            'concept_id' => $conceptDocuments->id,
            'type' => 'definition',
            'note' => 'Ensemble de pièces écrites, audiovisuelles ou électroniques servant de preuve ou d\'information.',
            'language' => 'fr-fr'
        ]);

        ThesaurusConceptNote::create([
            'concept_id' => $conceptArchives->id,
            'type' => 'definition',
            'note' => 'Documents conservés pour leur valeur historique, administrative, légale ou culturelle.',
            'language' => 'fr-fr'
        ]);

        ThesaurusConceptNote::create([
            'concept_id' => $conceptCorrespondance->id,
            'type' => 'scopeNote',
            'note' => 'Inclut les lettres, courriels, télégrammes et autres communications écrites.',
            'language' => 'fr-fr'
        ]);

        // Créer des relations hiérarchiques
        ThesaurusConceptRelation::create([
            'concept_id' => $conceptDocuments->id,
            'related_concept_id' => $conceptArchives->id,
            'relation_type' => 'broader'
        ]);

        ThesaurusConceptRelation::create([
            'concept_id' => $conceptDocuments->id,
            'related_concept_id' => $conceptCorrespondance->id,
            'relation_type' => 'broader'
        ]);

        ThesaurusConceptRelation::create([
            'concept_id' => $conceptDocuments->id,
            'related_concept_id' => $conceptRegistres->id,
            'relation_type' => 'broader'
        ]);

        // Relations inverses
        ThesaurusConceptRelation::create([
            'concept_id' => $conceptArchives->id,
            'related_concept_id' => $conceptDocuments->id,
            'relation_type' => 'narrower'
        ]);

        ThesaurusConceptRelation::create([
            'concept_id' => $conceptCorrespondance->id,
            'related_concept_id' => $conceptDocuments->id,
            'relation_type' => 'narrower'
        ]);

        ThesaurusConceptRelation::create([
            'concept_id' => $conceptRegistres->id,
            'related_concept_id' => $conceptDocuments->id,
            'relation_type' => 'narrower'
        ]);

        // Relations associatives
        ThesaurusConceptRelation::create([
            'concept_id' => $conceptCorrespondance->id,
            'related_concept_id' => $conceptRegistres->id,
            'relation_type' => 'related'
        ]);

        ThesaurusConceptRelation::create([
            'concept_id' => $conceptRegistres->id,
            'related_concept_id' => $conceptCorrespondance->id,
            'relation_type' => 'related'
        ]);

        $this->command->info('✅ Données de test du thésaurus créées avec succès !');
        $this->command->info("   - 1 schéma de thésaurus");
        $this->command->info("   - 4 concepts");
        $this->command->info("   - 5 libellés");
        $this->command->info("   - 3 notes conceptuelles");
        $this->command->info("   - 8 relations entre concepts");
        $this->command->info("   - 1 organisation");
        $this->command->info("   - 2 namespaces");
    }
}
