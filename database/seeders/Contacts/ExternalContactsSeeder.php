<?php

namespace Database\Seeders\Contacts;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;

class ExternalContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CrÃ©er quelques organisations externes (idempotent par 'name')
        $org1 = ExternalOrganization::firstOrCreate(
            ['name' => 'MinistÃ¨re de l\'Ã‰ducation Nationale'],
            [
                'legal_form' => 'Administration publique',
                'email' => 'contact@education.gouv.fr',
                'phone' => '01 23 45 67 89',
                'address' => '110 rue de Grenelle',
                'city' => 'Paris',
                'postal_code' => '75007',
                'country' => 'France',
                'is_verified' => true,
            ]
        );

        $org2 = ExternalOrganization::firstOrCreate(
            ['name' => 'UniversitÃ© Sorbonne'],
            [
                'legal_form' => 'Ã‰tablissement public',
                'email' => 'contact@sorbonne-universite.fr',
                'phone' => '01 44 27 44 27',
                'address' => '21 rue de l\'Ã‰cole de mÃ©decine',
                'city' => 'Paris',
                'postal_code' => '75006',
                'country' => 'France',
                'is_verified' => true,
            ]
        );

        $org3 = ExternalOrganization::firstOrCreate(
            ['name' => 'Entreprise TechCorp'],
            [
                'legal_form' => 'SAS',
                'registration_number' => '123456789',
                'email' => 'contact@techcorp.fr',
                'phone' => '01 98 76 54 32',
                'website' => 'https://www.techcorp.fr',
                'address' => '15 Avenue des Champs-Ã‰lysÃ©es',
                'city' => 'Paris',
                'postal_code' => '75008',
                'country' => 'France',
                'is_verified' => false,
            ]
        );

        // CrÃ©er des contacts pour ces organisations
        ExternalContact::firstOrCreate(
            ['email' => 'marie.dupont@education.gouv.fr'],
            [
                'first_name' => 'Marie',
                'last_name' => 'DUPONT',
                'phone' => '01 23 45 67 90',
                'position' => 'Directrice de cabinet',
                'external_organization_id' => $org1->id,
                'is_primary_contact' => true,
                'is_verified' => true,
            ]
        );

        ExternalContact::firstOrCreate(
            ['email' => 'jean.martin@education.gouv.fr'],
            [
                'first_name' => 'Jean',
                'last_name' => 'MARTIN',
                'phone' => '01 23 45 67 91',
                'position' => 'SecrÃ©taire gÃ©nÃ©ral',
                'external_organization_id' => $org1->id,
                'is_primary_contact' => false,
                'is_verified' => true,
            ]
        );

        ExternalContact::firstOrCreate(
            ['email' => 'sophie.bernard@sorbonne-universite.fr'],
            [
                'first_name' => 'Sophie',
                'last_name' => 'BERNARD',
                'phone' => '01 44 27 44 28',
                'position' => 'PrÃ©sidente',
                'external_organization_id' => $org2->id,
                'is_primary_contact' => true,
                'is_verified' => true,
            ]
        );

        ExternalContact::firstOrCreate(
            ['email' => 'pierre.rousseau@techcorp.fr'],
            [
                'first_name' => 'Pierre',
                'last_name' => 'ROUSSEAU',
                'phone' => '01 98 76 54 33',
                'position' => 'Directeur gÃ©nÃ©ral',
                'external_organization_id' => $org3->id,
                'is_primary_contact' => true,
                'is_verified' => false,
            ]
        );

        // CrÃ©er quelques contacts sans organisation
        ExternalContact::firstOrCreate(
            ['email' => 'isabelle.legrand@gmail.com'],
            [
                'first_name' => 'Isabelle',
                'last_name' => 'LEGRAND',
                'phone' => '06 12 34 56 78',
                'position' => 'Consultante indÃ©pendante',
                'address' => '25 rue de la RÃ©publique, 69000 Lyon',
                'is_primary_contact' => false,
                'is_verified' => true,
                'notes' => 'Contact expert en Ã©ducation numÃ©rique',
            ]
        );

        ExternalContact::firstOrCreate(
            ['email' => 'marc.petit@avocat.fr'],
            [
                'first_name' => 'Marc',
                'last_name' => 'PETIT',
                'phone' => '04 56 78 90 12',
                'position' => 'Avocat',
                'address' => '12 Place Bellecour, 69002 Lyon',
                'is_primary_contact' => false,
                'is_verified' => true,
                'notes' => 'SpÃ©cialisÃ© en droit de l\'Ã©ducation',
            ]
        );
    }
}

