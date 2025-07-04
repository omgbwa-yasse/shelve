<?php

namespace Database\Seeders;

use App\Models\ExternalContact;
use App\Models\ExternalOrganization;
use Illuminate\Database\Seeder;

class ExternalContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les IDs des organisations pour les associer aux contacts
        $orgs = ExternalOrganization::all();

        if ($orgs->count() == 0) {
            // Si aucune organisation n'existe, exécuter d'abord le seeder d'organisations
            $this->call(ExternalOrganizationSeeder::class);
            $orgs = ExternalOrganization::all();
        }

        $ministereId = $orgs->where('name', 'Ministère de l\'Économie')->first()?->id ?? null;
        $mairieId = $orgs->where('name', 'Mairie de Lyon')->first()?->id ?? null;
        $technoId = $orgs->where('name', 'Techno Innovations SA')->first()?->id ?? null;
        $ecoId = $orgs->where('name', 'Eco Constructions SARL')->first()?->id ?? null;
        $universiteId = $orgs->where('name', 'Université de Bordeaux')->first()?->id ?? null;

        $contacts = [
            [
                'first_name' => 'Thomas',
                'last_name' => 'Dupont',
                'email' => 'thomas.dupont@economie.gouv.fr',
                'phone' => '+33 1 40 04 12 34',
                'position' => 'Directeur de Cabinet',
                'external_organization_id' => $ministereId,
                'is_primary_contact' => true,
                'is_verified' => true,
                'notes' => 'Contact principal pour les dossiers importants'
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Martin',
                'email' => 'sophie.martin@economie.gouv.fr',
                'phone' => '+33 1 40 04 56 78',
                'position' => 'Chargée de mission',
                'external_organization_id' => $ministereId,
                'is_primary_contact' => false,
                'is_verified' => true,
                'notes' => 'À contacter pour les questions techniques'
            ],
            [
                'first_name' => 'Marc',
                'last_name' => 'Bernard',
                'email' => 'maire@mairie-lyon.fr',
                'phone' => '+33 4 72 10 30 40',
                'position' => 'Maire',
                'external_organization_id' => $mairieId,
                'is_primary_contact' => true,
                'is_verified' => true,
                'notes' => 'Maire de Lyon'
            ],
            [
                'first_name' => 'Claire',
                'last_name' => 'Dubois',
                'email' => 'c.dubois@techno-innovations.com',
                'phone' => '+33 1 23 45 67 80',
                'position' => 'Directrice Générale',
                'external_organization_id' => $technoId,
                'is_primary_contact' => true,
                'is_verified' => true,
                'notes' => 'Très réactive par email'
            ],
            [
                'first_name' => 'Jean',
                'last_name' => 'Leroy',
                'email' => 'j.leroy@eco-constructions.fr',
                'phone' => '+33 2 40 12 34 50',
                'position' => 'Gérant',
                'external_organization_id' => $ecoId,
                'is_primary_contact' => true,
                'is_verified' => false,
                'notes' => 'Préfère être contacté par téléphone'
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Rousseau',
                'email' => 'marie.rousseau@u-bordeaux.fr',
                'phone' => '+33 5 40 00 60 10',
                'position' => 'Présidente',
                'external_organization_id' => $universiteId,
                'is_primary_contact' => true,
                'is_verified' => true,
                'notes' => 'Disponible uniquement les matins'
            ],
            [
                'first_name' => 'Paul',
                'last_name' => 'Lefèvre',
                'email' => 'p.lefevre@gmail.com',
                'phone' => '+33 6 12 34 56 78',
                'position' => 'Consultant indépendant',
                'external_organization_id' => null,
                'is_primary_contact' => false,
                'is_verified' => true,
                'notes' => 'Expert en gestion documentaire, sans organisation affiliée'
            ]
        ];

        foreach ($contacts as $contact) {
            ExternalContact::create($contact);
        }
    }
}
