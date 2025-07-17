<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // Import Schema facade
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
        // Disable foreign key checks for seeding
        Schema::disableForeignKeyConstraints();

        // Truncate tables in reverse order of creation
        DB::table('thesaurus_concept_properties')->truncate();
        DB::table('thesaurus_concept_relations')->truncate();
        DB::table('thesaurus_concept_notes')->truncate();
        DB::table('thesaurus_labels')->truncate();
        DB::table('thesaurus_concepts')->truncate();
        DB::table('thesaurus_schemes')->truncate();
        DB::table('thesaurus_organizations')->truncate();
        DB::table('thesaurus_namespaces')->truncate();

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        // 1. Create Organization
        $organization = ThesaurusOrganization::create([
            [cite_start]'name' => 'SERVICE INTERMINISTERIEL DES ARCHIVES DE FRANCE', [cite: 1]
            'homepage' => 'http://www.culture.gouv.fr/Thesaurus-pour-l-indexation-des-archives-locales', // A plausible URL for the thesaurus publisher
            'email' => 'contact@archivesdefrance.culture.gouv.fr', // Example email
        ]);

        // 2. Create Namespace for SKOS URIs
        $namespace = ThesaurusNamespace::create([
            'prefix' => 'th',
            'namespace_uri' => 'http://data.archivesdefrance.culture.gouv.fr/thesaurus/local/', // Example base URI
        ]);

        // 3. Create Thesaurus Scheme
        $scheme = ThesaurusScheme::create([
            'uri' => $namespace->namespace_uri . 'thesaurus-matiere-local',
            [cite_start]'identifier' => 'THESAURUS-MATIERE', [cite: 1]
            [cite_start]'title' => 'THESAURUS POUR LA DESCRIPTION ET L\'INDEXATION DES ARCHIVES LOCALES', [cite: 1]
            [cite_start]'description' => 'Thésaurus pour la description et l\'indexation des archives locales, anciennes, modernes et contemporaines, liste méthodique.', [cite: 1]
            'language' => 'fr-fr',
        ]);

        // 4. Populate Concepts, Labels, Notes, and Relations

        [cite_start]// Top-level categories from "SOMMAIRE" [cite: 1]
        $administration = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'administration',
            [cite_start]'notation' => '1', [cite: 1]
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $administration->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'Administration', [cite: 1]
            'language' => 'fr-fr',
        ]);

        $agriculture = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'agriculture',
            [cite_start]'notation' => '2', [cite: 1]
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $agriculture->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'Agriculture', [cite: 1]
            'language' => 'fr-fr',
        ]);

        $communications = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'communications',
            [cite_start]'notation' => '3', [cite: 1]
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $communications->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'Communications', [cite: 1]
            'language' => 'fr-fr',
        ]);

        // Example: Sub-concepts and relations for 'Administration'
        $droitPublic = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'droit-public',
            [cite_start]'notation' => '1.1', [cite: 1]
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $droitPublic->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'Droit public', [cite: 1]
            'language' => 'fr-fr',
        ]);
        // Relation: Droit public is narrower than Administration
        ThesaurusConceptRelation::create([
            'concept_id' => $administration->id,
            'related_concept_id' => $droitPublic->id,
            'relation_type' => 'narrower',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $droitPublic->id,
            'related_concept_id' => $administration->id,
            'relation_type' => 'broader',
        ]);

        $constitution = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'constitution',
            'notation' => null,
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $constitution->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'CONSTITUTION', [cite: 5]
            'language' => 'fr-fr',
        ]);
        // Relation: CONSTITUTION is narrower than Droit public
        ThesaurusConceptRelation::create([
            'concept_id' => $droitPublic->id,
            'related_concept_id' => $constitution->id,
            'relation_type' => 'narrower',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $constitution->id,
            'related_concept_id' => $droitPublic->id,
            'relation_type' => 'broader',
        ]);

        $democratie = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'democratie',
            'notation' => null,
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $democratie->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'DEMOCRATIE', [cite: 5]
            'language' => 'fr-fr',
        ]);
        ThesaurusConceptNote::create([
            'concept_id' => $democratie->id,
            'type' => 'scopeNote',
            [cite_start]'note' => "S'utilise pour un mode de gouvernement et de représentation.", [cite: 7]
            'language' => 'fr-fr',
        ]);
        // Relation: DEMOCRATIE is narrower than Droit public
        ThesaurusConceptRelation::create([
            'concept_id' => $droitPublic->id,
            'related_concept_id' => $democratie->id,
            'relation_type' => 'narrower',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $democratie->id,
            'related_concept_id' => $droitPublic->id,
            'relation_type' => 'broader',
        ]);

        $administrationGenerale = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'administration-generale',
            [cite_start]'notation' => '1.2', [cite: 1]
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $administrationGenerale->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'Administration générale', [cite: 1]
            'language' => 'fr-fr',
        ]);
        // Relation: Administration générale is narrower than Administration
        ThesaurusConceptRelation::create([
            'concept_id' => $administration->id,
            'related_concept_id' => $administrationGenerale->id,
            'relation_type' => 'narrower',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $administrationGenerale->id,
            'related_concept_id' => $administration->id,
            'relation_type' => 'broader',
        ]);

        $hotelDeVille = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'hotel-de-ville',
            'notation' => null,
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $hotelDeVille->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'HOTEL DE VILLE', [cite: 6]
            'language' => 'fr-fr',
        ]);
        ThesaurusLabel::create([
            'concept_id' => $hotelDeVille->id,
            'type' => 'altLabel',
            [cite_start]'literal_form' => 'mairie', [cite: 6]
            'language' => 'fr-fr',
        ]);
        // Relation: HOTEL DE VILLE is narrower than Administration générale
        ThesaurusConceptRelation::create([
            'concept_id' => $administrationGenerale->id,
            'related_concept_id' => $hotelDeVille->id,
            'relation_type' => 'narrower',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $hotelDeVille->id,
            'related_concept_id' => $administrationGenerale->id,
            'relation_type' => 'broader',
        ]);

        $batimentAdministratif = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'batiment-administratif',
            'notation' => null,
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $batimentAdministratif->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'BATIMENT ADMINISTRATIF', [cite: 6]
            'language' => 'fr-fr',
        ]);
        ThesaurusConceptNote::create([
            'concept_id' => $batimentAdministratif->id,
            'type' => 'definition',
            [cite_start]'note' => "Bâtiment abritant des services administratifs ou à usage de l'administration. S'entend aussi pour le siège d'une administration quel que soit son contexte historique.", [cite: 9, 10]
            'language' => 'fr-fr',
        ]);
        // Relation: BATIMENT ADMINISTRATIF is narrower than Administration générale
        ThesaurusConceptRelation::create([
            'concept_id' => $administrationGenerale->id,
            'related_concept_id' => $batimentAdministratif->id,
            'relation_type' => 'narrower',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $batimentAdministratif->id,
            'related_concept_id' => $administrationGenerale->id,
            'relation_type' => 'broader',
        ]);

        $etablissementRecevantDuPublic = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'etablissement-recevant-du-public',
            [cite_start]'notation' => '1.6', [cite: 1]
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $etablissementRecevantDuPublic->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'ETABLISSEMENT RECEVANT DU PUBLIC', [cite: 54]
            'language' => 'fr-fr',
        ]);
        // Relation: BATIMENT ADMINISTRATIF is related to ETABLISSEMENT RECEVANT DU PUBLIC (TA)
        ThesaurusConceptRelation::create([
            'concept_id' => $batimentAdministratif->id,
            'related_concept_id' => $etablissementRecevantDuPublic->id,
            'relation_type' => 'related',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $etablissementRecevantDuPublic->id,
            'related_concept_id' => $batimentAdministratif->id,
            'relation_type' => 'related',
        ]);

        // Example: Related term for Agriculture (ECONOMIE RURALE)
        $economieRurale = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'economie-rurale',
            [cite_start]'notation' => '2.1', [cite: 1]
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $economieRurale->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'ECONOMIE RURALE', [cite: 65]
            'language' => 'fr-fr',
        ]);
        // Relation: ECONOMIE RURALE is narrower than Agriculture
        ThesaurusConceptRelation::create([
            'concept_id' => $agriculture->id,
            'related_concept_id' => $economieRurale->id,
            'relation_type' => 'narrower',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $economieRurale->id,
            'related_concept_id' => $agriculture->id,
            'relation_type' => 'broader',
        ]);

        $gardeParticulier = ThesaurusConcept::create([
            'scheme_id' => $scheme->id,
            'uri' => $namespace->namespace_uri . 'garde-particulier',
            'notation' => null,
            'status' => 1,
        ]);
        ThesaurusLabel::create([
            'concept_id' => $gardeParticulier->id,
            'type' => 'prefLabel',
            [cite_start]'literal_form' => 'GARDE PARTICULIER', [cite: 40]
            'language' => 'fr-fr',
        ]);
        ThesaurusLabel::create([
            'concept_id' => $gardeParticulier->id,
            'type' => 'altLabel',
            [cite_start]'literal_form' => 'garde champêtre', [cite: 40]
            'language' => 'fr-fr',
        ]);
        // Relation: GARDE PARTICULIER is related to ECONOMIE RURALE (TA)
        ThesaurusConceptRelation::create([
            'concept_id' => $gardeParticulier->id,
            'related_concept_id' => $economieRurale->id,
            'relation_type' => 'related',
        ]);
        ThesaurusConceptRelation::create([
            'concept_id' => $economieRurale->id,
            'related_concept_id' => $gardeParticulier->id,
            'relation_type' => 'related',
        ]);
    }
}
