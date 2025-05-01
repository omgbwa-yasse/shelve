<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthorSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Author types
        $authorTypes = [
            [
                'id' => 1,
                'name' => 'Organisation',
                'description' => 'Entité organisationnelle',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Personne physique',
                'description' => 'Personne individuelle',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Collectivité',
                'description' => 'Groupe de personnes',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Institution',
                'description' => 'Entité institutionnelle',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Entreprise',
                'description' => 'Entité commerciale',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Authors
        $authors = [
            // Organisations
            [
                'id' => 1,
                'type_id' => 1,
                'name' => 'Ministère de l\'Intérieur',
                'parallel_name' => 'Ministry of Interior',
                'other_name' => null,
                'lifespan' => null,
                'locations' => 'Paris',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'type_id' => 1,
                'name' => 'Ministère des Finances',
                'parallel_name' => 'Ministry of Finance',
                'other_name' => null,
                'lifespan' => null,
                'locations' => 'Paris',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'type_id' => 1,
                'name' => 'Direction Générale des Archives',
                'parallel_name' => 'General Directorate of Archives',
                'other_name' => null,
                'lifespan' => null,
                'locations' => 'Paris',
                'parent_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Personnes physiques
            [
                'id' => 4,
                'type_id' => 2,
                'name' => 'Dupont, Jean',
                'parallel_name' => null,
                'other_name' => null,
                'lifespan' => '1950-2022',
                'locations' => 'Lyon',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'type_id' => 2,
                'name' => 'Martin, Sophie',
                'parallel_name' => null,
                'other_name' => null,
                'lifespan' => '1965-',
                'locations' => 'Marseille',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'type_id' => 2,
                'name' => 'Bernard, Paul',
                'parallel_name' => null,
                'other_name' => 'BP',
                'lifespan' => '1958-2010',
                'locations' => 'Paris',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Collectivités
            [
                'id' => 7,
                'type_id' => 3,
                'name' => 'Conseil Municipal de Paris',
                'parallel_name' => 'Paris City Council',
                'other_name' => null,
                'lifespan' => null,
                'locations' => 'Paris',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'type_id' => 3,
                'name' => 'Association des Archivistes Français',
                'parallel_name' => 'French Archivists Association',
                'other_name' => 'AAF',
                'lifespan' => '1904-',
                'locations' => 'Paris',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Institutions
            [
                'id' => 9,
                'type_id' => 4,
                'name' => 'Archives Nationales',
                'parallel_name' => 'National Archives',
                'other_name' => null,
                'lifespan' => '1790-',
                'locations' => 'Paris, Pierrefitte-sur-Seine',
                'parent_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 10,
                'type_id' => 4,
                'name' => 'Bibliothèque Nationale de France',
                'parallel_name' => 'National Library of France',
                'other_name' => 'BNF',
                'lifespan' => '1368-',
                'locations' => 'Paris',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Entreprises
            [
                'id' => 11,
                'type_id' => 5,
                'name' => 'Société Générale',
                'parallel_name' => null,
                'other_name' => 'SG',
                'lifespan' => '1864-',
                'locations' => 'Paris',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 12,
                'type_id' => 5,
                'name' => 'Air France',
                'parallel_name' => null,
                'other_name' => 'AF',
                'lifespan' => '1933-',
                'locations' => 'Roissy',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Author contacts
        $authorContacts = [
            [
                'id' => 1,
                'author_id' => 1,
                'phone1' => '+33 1 23 45 67 89',
                'phone2' => null,
                'email' => 'contact@interieur.gouv.fr',
                'address' => 'Place Beauvau, 75008 Paris',
                'website' => 'www.interieur.gouv.fr',
                'fax' => '+33 1 23 45 67 90',
                'other' => null,
                'po_box' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'author_id' => 2,
                'phone1' => '+33 1 34 56 78 90',
                'phone2' => null,
                'email' => 'contact@finances.gouv.fr',
                'address' => '139 rue de Bercy, 75012 Paris',
                'website' => 'www.economie.gouv.fr',
                'fax' => '+33 1 34 56 78 91',
                'other' => null,
                'po_box' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'author_id' => 3,
                'phone1' => '+33 1 23 45 67 00',
                'phone2' => null,
                'email' => 'archives@interieur.gouv.fr',
                'address' => 'Place Beauvau, 75008 Paris',
                'website' => 'www.archives.interieur.gouv.fr',
                'fax' => '+33 1 23 45 67 01',
                'other' => null,
                'po_box' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'author_id' => 8,
                'phone1' => '+33 1 45 67 89 10',
                'phone2' => null,
                'email' => 'contact@archivistes.org',
                'address' => '8 rue Jean-Marie Jégo, 75013 Paris',
                'website' => 'www.archivistes.org',
                'fax' => null,
                'other' => null,
                'po_box' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'author_id' => 9,
                'phone1' => '+33 1 75 47 20 00',
                'phone2' => null,
                'email' => 'contact@archives-nationales.culture.gouv.fr',
                'address' => '59 rue Guynemer, 93383 Pierrefitte-sur-Seine',
                'website' => 'www.archives-nationales.culture.gouv.fr',
                'fax' => null,
                'other' => null,
                'po_box' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('author_types')->insert($authorTypes);
        DB::table('authors')->insert($authors);
        DB::table('author_contacts')->insert($authorContacts);
    }
}
