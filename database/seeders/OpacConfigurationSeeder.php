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

        $carouselCategory = OpacConfigurationCategory::firstOrCreate(
            ['name' => 'carousel'],
            [
                'label' => 'Carousel',
                'description' => 'Configuration du carousel de documents sur la page d\'accueil',
                'icon' => 'fas fa-images',
                'sort_order' => 5,
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

        // Configuration du carousel de documents
        OpacConfiguration::firstOrCreate(
            ['key' => 'enable_carousel'],
            [
                'category_id' => $carouselCategory->id,
                'label' => 'Activer le carousel',
                'description' => 'Afficher un carousel de documents sur la page d\'accueil',
                'type' => 'boolean',
                'default_value' => '1',
                'sort_order' => 2,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'carousel_items_count'],
            [
                'category_id' => $carouselCategory->id,
                'label' => 'Nombre d\'éléments dans le carousel',
                'description' => 'Nombre de documents à afficher dans le carousel',
                'type' => 'select',
                'options' => ['3', '4', '5', '6', '8', '10'],
                'default_value' => '6',
                'validation_rules' => ['integer', 'min:3', 'max:20'],
                'sort_order' => 3,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'carousel_auto_slide'],
            [
                'category_id' => $carouselCategory->id,
                'label' => 'Défilement automatique',
                'description' => 'Activer le défilement automatique du carousel',
                'type' => 'boolean',
                'default_value' => '1',
                'sort_order' => 4,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'carousel_slide_interval'],
            [
                'category_id' => $carouselCategory->id,
                'label' => 'Intervalle de défilement (ms)',
                'description' => 'Durée entre chaque changement de diapositive en millisecondes',
                'type' => 'integer',
                'default_value' => '5000',
                'validation_rules' => ['integer', 'min:1000', 'max:15000'],
                'sort_order' => 5,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'carousel_selection_method'],
            [
                'category_id' => $carouselCategory->id,
                'label' => 'Méthode de sélection',
                'description' => 'Comment sélectionner les documents pour le carousel',
                'type' => 'select',
                'options' => [
                    'recent' => 'Documents récents',
                    'featured' => 'Documents mis en avant',
                    'popular' => 'Documents populaires',
                    'random' => 'Documents aléatoires'
                ],
                'default_value' => 'recent',
                'sort_order' => 6,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'carousel_show_metadata'],
            [
                'category_id' => $carouselCategory->id,
                'label' => 'Afficher les métadonnées',
                'description' => 'Afficher les informations détaillées dans le carousel (auteur, date, etc.)',
                'type' => 'boolean',
                'default_value' => '1',
                'sort_order' => 7,
                'is_active' => true
            ]
        );

        OpacConfiguration::firstOrCreate(
            ['key' => 'carousel_title'],
            [
                'category_id' => $carouselCategory->id,
                'label' => 'Titre du carousel',
                'description' => 'Titre affiché au-dessus du carousel',
                'type' => 'string',
                'default_value' => 'Documents à découvrir',
                'sort_order' => 8,
                'is_active' => true
            ]
        );
    }
}
