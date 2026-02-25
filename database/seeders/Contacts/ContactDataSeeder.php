<?php

namespace Database\Seeders\Contacts;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Author;
use App\Models\AuthorType;
use App\Models\AuthorContact;
use App\Models\Contact;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;
use App\Models\Organisation;

class ContactDataSeeder extends Seeder
{
    /**
     * Seed test data for the Contacts module.
     * Creates author types, authors with hierarchy & contacts,
     * external organisations & contacts, and organisation contacts.
     * Idempotent: uses firstOrCreate.
     */
    public function run(): void
    {
        $this->command->info('👤 Seeding Contacts module test data...');

        $org = Organisation::first();

        // --- 1. Author Types ---
        $typeDefs = [
            ['name' => 'Personne physique',     'description' => 'Individu identifié comme auteur ou producteur de documents.'],
            ['name' => 'Personne morale',       'description' => 'Organisation, institution ou entreprise productrice de documents.'],
            ['name' => 'Famille',               'description' => 'Famille dont les archives sont conservées.'],
            ['name' => 'Collectivité',          'description' => 'Collectivité territoriale ou administrative.'],
        ];

        $types = [];
        foreach ($typeDefs as $td) {
            $types[] = AuthorType::firstOrCreate(['name' => $td['name']], $td);
        }

        // --- 2. Authors (with hierarchy) ---
        // Root-level authors
        $authorRoot1 = Author::firstOrCreate(
            ['name' => 'Direction Générale'],
            ['type_id' => $types[1]->id, 'parallel_name' => 'General Directorate', 'lifespan' => '1960-', 'locations' => 'Bâtiment principal']
        );

        $authorRoot2 = Author::firstOrCreate(
            ['name' => 'Ministère de la Culture'],
            ['type_id' => $types[3]->id, 'parallel_name' => 'Ministry of Culture', 'lifespan' => '1962-', 'locations' => 'Capitale']
        );

        // Child authors
        $authorChild1 = Author::firstOrCreate(
            ['name' => 'Service des Ressources Humaines'],
            ['type_id' => $types[1]->id, 'parent_id' => $authorRoot1->id, 'locations' => 'Bâtiment A, 2ème étage']
        );

        $authorChild2 = Author::firstOrCreate(
            ['name' => 'Service Comptabilité et Finances'],
            ['type_id' => $types[1]->id, 'parent_id' => $authorRoot1->id, 'locations' => 'Bâtiment A, 3ème étage']
        );

        $authorChild3 = Author::firstOrCreate(
            ['name' => 'Direction des Archives'],
            ['type_id' => $types[1]->id, 'parent_id' => $authorRoot2->id, 'locations' => 'Capitale, rue des Archives']
        );

        // Individual authors
        $authorPerson1 = Author::firstOrCreate(
            ['name' => 'DUPONT Jean'],
            ['type_id' => $types[0]->id, 'other_name' => 'J. Dupont', 'lifespan' => '1965-', 'locations' => 'Paris']
        );

        $authorPerson2 = Author::firstOrCreate(
            ['name' => 'MARTIN Marie'],
            ['type_id' => $types[0]->id, 'other_name' => 'M. Martin', 'lifespan' => '1972-', 'locations' => 'Lyon']
        );

        $authorFamily = Author::firstOrCreate(
            ['name' => 'Famille BENALI'],
            ['type_id' => $types[2]->id, 'lifespan' => '1850-', 'locations' => 'Alger']
        );

        // --- 3. Author Contacts ---
        $contactDefs = [
            ['author' => $authorRoot1, 'phone1' => '+213 21 00 00 00', 'email' => 'direction@institution.dz', 'address' => '1 Avenue de l\'Indépendance'],
            ['author' => $authorChild1, 'phone1' => '+213 21 00 00 10', 'email' => 'rh@institution.dz', 'address' => 'Bâtiment A, Bureau 201'],
            ['author' => $authorChild2, 'phone1' => '+213 21 00 00 20', 'email' => 'compta@institution.dz', 'address' => 'Bâtiment A, Bureau 301'],
            ['author' => $authorPerson1, 'phone1' => '+33 6 12 34 56 78', 'email' => 'jean.dupont@email.com'],
            ['author' => $authorPerson2, 'phone1' => '+33 6 98 76 54 32', 'email' => 'marie.martin@email.com'],
        ];

        foreach ($contactDefs as $cd) {
            AuthorContact::firstOrCreate(
                ['author_id' => $cd['author']->id, 'email' => $cd['email'] ?? null],
                [
                    'phone1' => $cd['phone1'] ?? null,
                    'phone2' => null,
                    'address' => $cd['address'] ?? null,
                    'website' => null,
                    'fax' => null,
                ]
            );
        }

        // --- 4. Organisation Contacts ---
        $orgContactDefs = [
            ['type' => 'email',      'value' => 'contact@institution.dz',  'label' => 'Email principal'],
            ['type' => 'telephone',  'value' => '+213 21 00 00 00',        'label' => 'Standard'],
            ['type' => 'adresse',    'value' => '1 Avenue de l\'Indépendance, Alger', 'label' => 'Siège'],
            ['type' => 'fax',        'value' => '+213 21 00 00 99',        'label' => 'Fax central'],
            ['type' => 'gps',        'value' => '36.7538,3.0588',          'label' => 'Coordonnées GPS'],
        ];

        if ($org) {
            foreach ($orgContactDefs as $ocd) {
                $contact = Contact::firstOrCreate(
                    ['type' => $ocd['type'], 'value' => $ocd['value']],
                    ['label' => $ocd['label']]
                );
                DB::table('organisation_contact')->updateOrInsert(
                    ['organisation_id' => $org->id, 'contact_id' => $contact->id]
                );
            }
        }

        // --- 5. External Organizations & Contacts ---
        $extOrgDefs = [
            [
                'name' => 'Cabinet HADJ & Associés',
                'email' => 'contact@hadj-associes.dz',
                'phone' => '+213 21 45 67 89',
                'address' => '15 Rue Didouche Mourad, Alger',
                'registration_number' => 'RC-16-001234',
                'is_verified' => true,
                'contacts' => [
                    ['first_name' => 'Ahmed', 'last_name' => 'HADJ', 'email' => 'a.hadj@hadj-associes.dz', 'position' => 'Directeur', 'is_primary' => true],
                    ['first_name' => 'Samira', 'last_name' => 'KACI', 'email' => 's.kaci@hadj-associes.dz', 'position' => 'Secrétaire'],
                ],
            ],
            [
                'name' => 'Imprimerie Nationale',
                'email' => 'commandes@imprimerie-nationale.dz',
                'phone' => '+213 21 56 78 90',
                'address' => 'Zone Industrielle, Rouiba',
                'registration_number' => 'RC-09-005678',
                'is_verified' => true,
                'contacts' => [
                    ['first_name' => 'Mohamed', 'last_name' => 'SLIMANI', 'email' => 'm.slimani@imprimerie.dz', 'position' => 'Commercial', 'is_primary' => true],
                ],
            ],
            [
                'name' => 'Archives Départementales du Rhône',
                'email' => 'archives@rhone.fr',
                'phone' => '+33 4 72 35 35 00',
                'address' => '1 Chemin de Montauban, Lyon',
                'is_verified' => false,
                'notes' => 'Partenaire pour échanges de bonnes pratiques archivistiques.',
                'contacts' => [
                    ['first_name' => 'Claire', 'last_name' => 'DUBOIS', 'email' => 'c.dubois@rhone.fr', 'position' => 'Archiviste en chef', 'is_primary' => true],
                ],
            ],
        ];

        foreach ($extOrgDefs as $eod) {
            $extOrg = ExternalOrganization::firstOrCreate(
                ['name' => $eod['name']],
                [
                    'email' => $eod['email'],
                    'phone' => $eod['phone'],
                    'address' => $eod['address'],
                    'registration_number' => $eod['registration_number'] ?? null,
                    'is_verified' => $eod['is_verified'],
                    'notes' => $eod['notes'] ?? null,
                ]
            );

            foreach ($eod['contacts'] ?? [] as $ec) {
                ExternalContact::firstOrCreate(
                    ['email' => $ec['email']],
                    [
                        'first_name' => $ec['first_name'],
                        'last_name' => $ec['last_name'],
                        'phone' => null,
                        'position' => $ec['position'],
                        'external_organization_id' => $extOrg->id,
                        'is_primary_contact' => $ec['is_primary'] ?? false,
                        'is_verified' => $extOrg->is_verified,
                    ]
                );
            }
        }

        $totalAuthors = 8; // 2 root + 3 children + 2 persons + 1 family
        $this->command->info("✅ Contacts: {$totalAuthors} authors, " . count($orgContactDefs) . " org contacts, " . count($extOrgDefs) . " external organisations seeded.");
    }
}
