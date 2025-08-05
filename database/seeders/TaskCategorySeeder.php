<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catégories de tâches
        $categories = [
            [
                'name' => 'Traitement de courrier',
                'color' => '#4A90E2',
                'description' => 'Tâches liées au traitement du courrier entrant et sortant',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Documentation',
                'color' => '#50E3C2',
                'description' => 'Tâches liées à la création et gestion de documents',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Archivage',
                'color' => '#F5A623',
                'description' => 'Tâches liées à l\'archivage et à la gestion des archives',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Réunions',
                'color' => '#9013FE',
                'description' => 'Préparation et suivi de réunions',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administration',
                'color' => '#D0021B',
                'description' => 'Tâches administratives diverses',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Informatique',
                'color' => '#7ED321',
                'description' => 'Tâches liées à l\'informatique et aux systèmes d\'information',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Formation',
                'color' => '#BD10E0',
                'description' => 'Tâches liées à la formation et au développement des compétences',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ressources Humaines',
                'color' => '#8B572A',
                'description' => 'Tâches liées à la gestion des ressources humaines',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finances',
                'color' => '#417505',
                'description' => 'Tâches liées à la gestion financière et budgétaire',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('task_categories')->insertOrIgnore($categories);
    }
}
