<?php

namespace Database\Seeders\Workplaces;

use Illuminate\Database\Seeder;
use App\Models\WorkplaceCategory;

class WorkplaceCategorySeeder extends Seeder
{
    /**
     * Seed the workplace_categories table with default categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'HR',
                'name' => 'Ressources Humaines',
                'description' => 'Espace de travail dédié aux ressources humaines : recrutement, formation, gestion du personnel.',
                'icon' => 'people-fill',
                'color' => '#e74c3c',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'FINANCE',
                'name' => 'Finance & Comptabilité',
                'description' => 'Espace de travail pour la gestion financière, comptabilité et budget.',
                'icon' => 'cash-stack',
                'color' => '#27ae60',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'code' => 'IT',
                'name' => 'Informatique & Technologie',
                'description' => 'Espace de travail pour les projets IT, développement et infrastructure.',
                'icon' => 'pc-display',
                'color' => '#2980b9',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'LEGAL',
                'name' => 'Juridique',
                'description' => 'Espace de travail pour les affaires juridiques, contrats et conformité.',
                'icon' => 'shield-check',
                'color' => '#8e44ad',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'code' => 'COMM',
                'name' => 'Communication',
                'description' => 'Espace de travail pour la communication interne et externe, relations publiques.',
                'icon' => 'megaphone',
                'color' => '#f39c12',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'code' => 'PROJECT',
                'name' => 'Gestion de Projet',
                'description' => 'Espace de travail pour la gestion et le suivi de projets transversaux.',
                'icon' => 'kanban',
                'color' => '#1abc9c',
                'is_active' => true,
                'display_order' => 6,
            ],
            [
                'code' => 'ARCHIVE',
                'name' => 'Archives',
                'description' => 'Espace de travail dédié à la gestion et conservation des archives.',
                'icon' => 'archive',
                'color' => '#95a5a6',
                'is_active' => true,
                'display_order' => 7,
            ],
            [
                'code' => 'ADMIN',
                'name' => 'Administration Générale',
                'description' => 'Espace de travail pour l\'administration et la direction générale.',
                'icon' => 'building',
                'color' => '#34495e',
                'is_active' => true,
                'display_order' => 8,
            ],
            [
                'code' => 'OTHER',
                'name' => 'Autre',
                'description' => 'Espace de travail à usage général ou non classifié.',
                'icon' => 'folder2-open',
                'color' => '#7f8c8d',
                'is_active' => true,
                'display_order' => 99,
            ],
        ];

        foreach ($categories as $category) {
            WorkplaceCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        $this->command->info('✅ ' . count($categories) . ' catégories de workplace créées.');
    }
}
