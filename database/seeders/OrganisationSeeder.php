<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;

class OrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Conserver les organisations existantes si nécessaire
        // Puis ajouter les nouvelles directions et bureaux

        // Direction des Finances
        $financeDir = Organisation::create([
            'code' => 'DF',
            'name' => 'Direction des Finances',
            'description' => 'Direction responsable de la gestion financière de l\'organisation'
        ]);

        // Bureaux sous la Direction des Finances
        Organisation::create([
            'code' => 'DF-BB',
            'name' => 'Bureau du Budget',
            'description' => 'Responsable de la planification et gestion du budget',
            'parent_id' => $financeDir->id
        ]);

        Organisation::create([
            'code' => 'DF-BC',
            'name' => 'Bureau de la Comptabilité',
            'description' => 'Responsable de la tenue des comptes et des opérations financières',
            'parent_id' => $financeDir->id
        ]);

        Organisation::create([
            'code' => 'DF-BMP',
            'name' => 'Bureau des Marchés Publics',
            'description' => 'Responsable des procédures d\'appels d\'offres et d\'attribution des marchés',
            'parent_id' => $financeDir->id
        ]);

        // Direction des Ressources Mobilières et Immobilières
        $resourcesDir = Organisation::create([
            'code' => 'DRMI',
            'name' => 'Direction des Ressources Mobilières et Immobilières',
            'description' => 'Direction responsable de la gestion des biens mobiliers et immobiliers'
        ]);

        // Bureaux sous la Direction des Ressources
        Organisation::create([
            'code' => 'DRMI-BM',
            'name' => 'Service des Biens Mobiliers',
            'description' => 'Responsable de la gestion du mobilier et équipements',
            'parent_id' => $resourcesDir->id
        ]);

        Organisation::create([
            'code' => 'DRMI-BI',
            'name' => 'Service des Biens Immobiliers',
            'description' => 'Responsable de la gestion des bâtiments et terrains',
            'parent_id' => $resourcesDir->id
        ]);

        Organisation::create([
            'code' => 'DRMI-MT',
            'name' => 'Service de la Maintenance',
            'description' => 'Responsable de l\'entretien des biens mobiliers et immobiliers',
            'parent_id' => $resourcesDir->id
        ]);

        // Direction de l'Information et de la Communication
        $infoComDir = Organisation::create([
            'code' => 'DIC',
            'name' => 'Direction de l\'Information et de la Communication',
            'description' => 'Direction responsable de la gestion de l\'information et des communications'
        ]);

        // Bureaux sous la Direction de l'Information et de la Communication
        Organisation::create([
            'code' => 'DIC-RP',
            'name' => 'Service des Relations Publiques',
            'description' => 'Responsable des relations avec le public et les médias',
            'parent_id' => $infoComDir->id
        ]);

        Organisation::create([
            'code' => 'DIC-NUM',
            'name' => 'Service du Numérique',
            'description' => 'Responsable des outils numériques et de la présence en ligne',
            'parent_id' => $infoComDir->id
        ]);

        Organisation::create([
            'code' => 'DIC-PUB',
            'name' => 'Service des Publications',
            'description' => 'Responsable des publications et de la documentation',
            'parent_id' => $infoComDir->id
        ]);
    }
}
