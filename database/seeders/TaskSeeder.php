<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les IDs des catégories de tâches
        $categories = DB::table('task_categories')->pluck('id', 'name')->toArray();

        // Récupérer l'ID du superadmin
        $superAdminId = DB::table('users')->where('email', 'superadmin@example.com')->first()->id;

        // Récupérer les IDs des organisations
        $organisations = DB::table('organisations')->pluck('id', 'name')->toArray();

        // Définir des dates relatives pour les tâches
        $today = Carbon::now();
        $yesterday = Carbon::now()->subDay();
        $tomorrow = Carbon::now()->addDay();
        $nextWeek = Carbon::now()->addWeek();
        $lastWeek = Carbon::now()->subWeek();

        // Tâches liées au traitement du courrier
        $tasks = [
            [
                'title' => 'Traiter courrier urgent du Ministère',
                'description' => 'Courrier reçu concernant la mise en place des nouvelles directives',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => $tomorrow,
                'start_date' => $today,
                'estimated_hours' => 2,
                'progress_percentage' => 0,
                'category_id' => $categories['Traitement de courrier'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['urgent', 'ministère', 'directives']),
                'created_at' => $today,
                'updated_at' => $today,
            ],
            [
                'title' => 'Préparer réponse au courrier référence A2025-156',
                'description' => 'Rédiger un projet de réponse au courrier de demande d\'information',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => $nextWeek,
                'start_date' => $yesterday,
                'estimated_hours' => 4,
                'progress_percentage' => 30,
                'category_id' => $categories['Traitement de courrier'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['réponse', 'demande d\'information']),
                'created_at' => $yesterday,
                'updated_at' => $today,
            ],

            // Tâches liées à la documentation
            [
                'title' => 'Mettre à jour la procédure d\'archivage',
                'description' => 'Mise à jour suite aux nouvelles règles de conservation',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => $nextWeek->addDays(3),
                'start_date' => null,
                'estimated_hours' => 6,
                'progress_percentage' => 0,
                'category_id' => $categories['Documentation'],
                'assigned_to_user_id' => $superAdminId,
                'assigned_to_organisation_id' => isset($organisations['Direction des Archives']) ? $organisations['Direction des Archives'] : null,
                'assignment_type' => 'both',
                'created_by' => $superAdminId,
                'tags' => json_encode(['procédure', 'mise à jour', 'archivage']),
                'created_at' => $yesterday->subDays(2),
                'updated_at' => $yesterday->subDays(2),
            ],

            // Tâches liées à l'archivage
            [
                'title' => 'Préparer bordereau d\'élimination archives 2020',
                'description' => 'Identifier les documents arrivés à échéance de conservation',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => $nextWeek->addDays(5),
                'start_date' => $lastWeek,
                'estimated_hours' => 8,
                'progress_percentage' => 45,
                'category_id' => $categories['Archivage'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['élimination', 'archives 2020', 'bordereau']),
                'created_at' => $lastWeek,
                'updated_at' => $yesterday,
            ],

            // Tâches liées aux réunions
            [
                'title' => 'Préparation réunion comité de direction',
                'description' => 'Préparer l\'ordre du jour et les documents pour la réunion du 10 août',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => Carbon::parse('2025-08-09'),
                'start_date' => null,
                'estimated_hours' => 3,
                'progress_percentage' => 0,
                'category_id' => $categories['Réunions'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['réunion', 'comité', 'préparation']),
                'created_at' => $today->subDays(1),
                'updated_at' => $today->subDays(1),
            ],

            // Tâches administratives
            [
                'title' => 'Valider les demandes de congés en attente',
                'description' => 'Traiter les 5 demandes de congés en attente',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => $tomorrow->addDays(2),
                'start_date' => null,
                'estimated_hours' => 1,
                'progress_percentage' => 0,
                'category_id' => $categories['Administration'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['congés', 'validation', 'RH']),
                'created_at' => $today,
                'updated_at' => $today,
            ],

            // Tâches informatiques
            [
                'title' => 'Configurer les nouveaux postes de travail',
                'description' => 'Installation et configuration des 3 nouveaux postes pour les nouveaux arrivants',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => $nextWeek,
                'start_date' => null,
                'estimated_hours' => 6,
                'progress_percentage' => 0,
                'category_id' => $categories['Informatique'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['informatique', 'configuration', 'nouveaux postes']),
                'created_at' => $today->subDays(3),
                'updated_at' => $today->subDays(3),
            ],

            // Tâches de formation
            [
                'title' => 'Préparer formation sur le nouveau système d\'archivage',
                'description' => 'Créer les supports de formation pour la session du 20 août',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => Carbon::parse('2025-08-15'),
                'start_date' => null,
                'estimated_hours' => 10,
                'progress_percentage' => 0,
                'category_id' => $categories['Formation'],
                'assigned_to_organisation_id' => isset($organisations['Direction des Archives']) ? $organisations['Direction des Archives'] : null,
                'assignment_type' => 'organisation',
                'created_by' => $superAdminId,
                'tags' => json_encode(['formation', 'archivage', 'supports']),
                'created_at' => $today->subDays(5),
                'updated_at' => $today->subDays(5),
            ],

            // Tâches RH
            [
                'title' => 'Finaliser l\'intégration des nouveaux employés',
                'description' => 'Compléter les dossiers administratifs et vérifier l\'accès aux systèmes',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => $tomorrow,
                'start_date' => $yesterday,
                'estimated_hours' => 4,
                'progress_percentage' => 50,
                'category_id' => $categories['Ressources Humaines'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['RH', 'intégration', 'nouveaux employés']),
                'created_at' => $yesterday,
                'updated_at' => $today,
            ],

            // Tâches finances
            [
                'title' => 'Préparer les bons de commande matériel informatique',
                'description' => 'Créer les bons de commande pour le matériel validé lors du dernier comité',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => $nextWeek->addDays(2),
                'start_date' => null,
                'estimated_hours' => 2,
                'progress_percentage' => 0,
                'category_id' => $categories['Finances'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['finances', 'commandes', 'matériel informatique']),
                'created_at' => $today->subDay(),
                'updated_at' => $today->subDay(),
            ],

            // Tâche complétée
            [
                'title' => 'Revue du rapport annuel d\'activité',
                'description' => 'Relecture et correction du rapport annuel avant publication',
                'status' => 'done',
                'priority' => 'high',
                'due_date' => $lastWeek,
                'start_date' => $lastWeek->subDays(3),
                'completed_at' => $lastWeek->addDays(2),
                'estimated_hours' => 5,
                'actual_hours' => 6,
                'progress_percentage' => 100,
                'category_id' => $categories['Documentation'],
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['rapport', 'relecture', 'publication']),
                'completion_notes' => 'Rapport relu et corrigé, envoyé pour impression le ' . $lastWeek->addDays(2)->format('d/m/Y'),
                'created_at' => $lastWeek->subDays(3),
                'updated_at' => $lastWeek->addDays(2),
            ],
        ];

        // Insérer les tâches une par une pour éviter les problèmes de colonnes
        foreach ($tasks as $task) {
            DB::table('tasks')->insert($task);
        }
    }
}
