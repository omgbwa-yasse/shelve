<?php

namespace Database\Seeders;

use App\Models\OpacConfiguration;
use App\Models\OpacConfigurationCategory;
use Illuminate\Database\Seeder;

class OpacConfigurationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les catégories de configuration
        $generalCategory = OpacConfigurationCategory::firstOrCreate(
            ['name' => 'general'],
            [
                'label' => 'Paramètres généraux',
                'description' => 'Configuration générale de l\'OPAC',
                'icon' => 'fas fa-cogs',
                'sort_order' => 1,
                'is_active' => true
            ]
        );

        $displayCategory = OpacConfigurationCategory::firstOrCreate(
            ['name' => 'display'],
            [
                'label' => 'Affichage',
                'description' => 'Configuration de l\'affichage et de l\'interface',
                'icon' => 'fas fa-desktop',
                'sort_order' => 2,
                'is_active' => true
            ]
        );

        $searchCategory = OpacConfigurationCategory::firstOrCreate(
            ['name' => 'search'],
            [
                'label' => 'Recherche',
                'description' => 'Configuration des fonctionnalités de recherche',
                'icon' => 'fas fa-search',
                'sort_order' => 3,
                'is_active' => true
            ]
        );

        $securityCategory = OpacConfigurationCategory::firstOrCreate(
            ['name' => 'security'],
            [
                'label' => 'Sécurité',
                'description' => 'Configuration de la sécurité et des accès',
                'icon' => 'fas fa-shield-alt',
                'sort_order' => 4,
                'is_active' => true
            ]
        );

        // Ajouter quelques configurations de base pour tester
        OpacConfiguration::firstOrCreate(
            ['key' => 'opac_title'],
            [
                'category_id' => $generalCategory->id,
                'label' => 'Titre de l\'OPAC',
                'description' => 'Titre affiché en en-tête de l\'OPAC',
                'type' => 'string',
                'default_value' => 'Catalogue en ligne',
                'is_required' => true,
                'sort_order' => 1,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'records_per_page'],
            [
                'category_id' => $displayCategory->id,
                'label' => 'Enregistrements par page',
                'description' => 'Nombre d\'enregistrements à afficher par page de résultats',
                'type' => 'integer',
                'options' => [5, 10, 15, 20, 25, 50],
                'default_value' => '15',
                'validation_rules' => ['integer', 'min:5', 'max:100'],
                'sort_order' => 1,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'enable_advanced_search'],
            [
                'category_id' => $searchCategory->id,
                'label' => 'Recherche avancée',
                'description' => 'Activer la recherche avancée avec filtres',
                'type' => 'boolean',
                'default_value' => '1',
                'sort_order' => 1,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'public_access'],
            [
                'category_id' => $securityCategory->id,
                'label' => 'Accès public',
                'description' => 'Autoriser l\'accès public sans authentification',
                'type' => 'boolean',
                'default_value' => '1',
                'sort_order' => 1,
                'is_active' => true
            ]
        );
    }
}
