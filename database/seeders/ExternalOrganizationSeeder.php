<?php

namespace Database\Seeders;

use App\Models\ExternalOrganization;
use Illuminate\Database\Seeder;

class ExternalOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'Ministère de l\'Économie',
                'legal_form' => 'Administration publique',
                'registration_number' => 'MIN-ECO-01',
                'email' => 'contact@economie.gouv.fr',
                'phone' => '+33 1 40 04 04 04',
                'website' => 'https://www.economie.gouv.fr',
                'address' => '139 rue de Bercy',
                'city' => 'Paris',
                'postal_code' => '75012',
                'country' => 'France',
                'is_verified' => true,
                'notes' => 'Ministère de l\'Économie, des Finances et de la Souveraineté industrielle et numérique'
            ],
            [
                'name' => 'Mairie de Lyon',
                'legal_form' => 'Collectivité territoriale',
                'registration_number' => 'LYON-01',
                'email' => 'contact@mairie-lyon.fr',
                'phone' => '+33 4 72 10 30 30',
                'website' => 'https://www.lyon.fr',
                'address' => '1 place de la Comédie',
                'city' => 'Lyon',
                'postal_code' => '69001',
                'country' => 'France',
                'is_verified' => true,
                'notes' => 'Hôtel de Ville de Lyon'
            ],
            [
                'name' => 'Techno Innovations SA',
                'legal_form' => 'Société anonyme',
                'registration_number' => 'RCS Paris B 123 456 789',
                'email' => 'contact@techno-innovations.com',
                'phone' => '+33 1 23 45 67 89',
                'website' => 'https://www.techno-innovations.com',
                'address' => '15 rue de l\'Innovation',
                'city' => 'Paris',
                'postal_code' => '75008',
                'country' => 'France',
                'is_verified' => true,
                'notes' => 'Entreprise spécialisée dans les solutions informatiques'
            ],
            [
                'name' => 'Eco Constructions SARL',
                'legal_form' => 'Société à responsabilité limitée',
                'registration_number' => 'RCS Nantes 987 654 321',
                'email' => 'info@eco-constructions.fr',
                'phone' => '+33 2 40 12 34 56',
                'website' => 'https://www.eco-constructions.fr',
                'address' => '42 boulevard des Écologistes',
                'city' => 'Nantes',
                'postal_code' => '44000',
                'country' => 'France',
                'is_verified' => false,
                'notes' => 'Entreprise de construction écologique'
            ],
            [
                'name' => 'Université de Bordeaux',
                'legal_form' => 'Établissement public',
                'registration_number' => 'SIRET 130 018 351 00010',
                'email' => 'contact@u-bordeaux.fr',
                'phone' => '+33 5 40 00 60 00',
                'website' => 'https://www.u-bordeaux.fr',
                'address' => '35 place Pey Berland',
                'city' => 'Bordeaux',
                'postal_code' => '33000',
                'country' => 'France',
                'is_verified' => true,
                'notes' => 'Université pluridisciplinaire'
            ]
        ];

        foreach ($organizations as $org) {
            ExternalOrganization::create($org);
        }
    }
}
