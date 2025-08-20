<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    private const ARCHIVES_ORG_NAME = 'Direction des Archives';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les IDs des catégories de tâches
        $categories = DB::table('task_categories')->pluck('id', 'name')->toArray();

        // Récupérer l'ID du superadmin (sécurisé)
        $superAdmin = DB::table('users')->where('email', 'superadmin@example.com')->first();
        if (!$superAdmin) {
            // Si le superadmin n'existe pas, on arrête proprement ce seeder
            $this->command?->warn('Superadmin introuvable, TaskSeeder sauté.');
            return;
        }
        $superAdminId = $superAdmin->id;

    // Récupérer les IDs des organisations (peut être vide)
    $organisations = DB::table('organisations')->pluck('id', 'name')->toArray();

        // Construire les tâches via méthodes dédiées (réduction de complexité)
        $dates = $this->dates();
        $tasks = array_merge(
            $this->tasksMail($dates, $categories, $superAdminId),
            $this->tasksDocumentation($dates, $categories, $organisations, $superAdminId),
            $this->tasksArchivage($dates, $categories, $superAdminId),
            $this->tasksReunions($dates, $categories, $superAdminId),
            $this->tasksAdministration($dates, $categories, $superAdminId),
            $this->tasksIT($dates, $categories, $superAdminId),
            $this->tasksFormation($dates, $categories, $organisations, $superAdminId),
            $this->tasksRH($dates, $categories, $superAdminId),
            $this->tasksFinances($dates, $categories, $superAdminId),
            $this->tasksCompleted($dates, $categories, $superAdminId)
        );

        // Insérer les tâches une par une pour éviter les problèmes de colonnes
        foreach ($tasks as $task) {
            DB::table('tasks')->insertOrIgnore($task);
        }
    }

    /** Dates relatives standardisées */
    private function dates(): array
    {
        return [
            'today' => Carbon::now(),
            'yesterday' => Carbon::now()->subDay(),
            'tomorrow' => Carbon::now()->addDay(),
            'nextWeek' => Carbon::now()->addWeek(),
            'lastWeek' => Carbon::now()->subWeek(),
        ];
    }

    private function tasksMail(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Traiter courrier urgent du Ministère',
                'description' => 'Courrier reçu concernant la mise en place des nouvelles directives',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => $d['tomorrow'],
                'start_date' => $d['today'],
                'estimated_hours' => 2,
                'progress_percentage' => 0,
                'category_id' => $categories['Traitement de courrier'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['urgent', 'ministère', 'directives']),
                'created_at' => $d['today'],
                'updated_at' => $d['today'],
            ],
            [
                'title' => 'Préparer réponse au courrier référence A2025-156',
                'description' => 'Rédiger un projet de réponse au courrier de demande d\'information',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => $d['nextWeek'],
                'start_date' => $d['yesterday'],
                'estimated_hours' => 4,
                'progress_percentage' => 30,
                'category_id' => $categories['Traitement de courrier'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['réponse', 'demande d\'information']),
                'created_at' => $d['yesterday'],
                'updated_at' => $d['today'],
            ],
        ];
    }

    private function tasksDocumentation(array $d, array $categories, array $organisations, int $superAdminId): array
    {
        return [
            [
                'title' => 'Mettre à jour la procédure d\'archivage',
                'description' => 'Mise à jour suite aux nouvelles règles de conservation',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => (clone $d['nextWeek'])->addDays(3),
                'start_date' => null,
                'estimated_hours' => 6,
                'progress_percentage' => 0,
                'category_id' => $categories['Documentation'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assigned_to_organisation_id' => $organisations[self::ARCHIVES_ORG_NAME] ?? null,
                'assignment_type' => 'both',
                'created_by' => $superAdminId,
                'tags' => json_encode(['procédure', 'mise à jour', 'archivage']),
                'created_at' => (clone $d['yesterday'])->subDays(2),
                'updated_at' => (clone $d['yesterday'])->subDays(2),
            ],
        ];
    }

    private function tasksArchivage(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Préparer bordereau d\'élimination archives 2020',
                'description' => 'Identifier les documents arrivés à échéance de conservation',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => (clone $d['nextWeek'])->addDays(5),
                'start_date' => $d['lastWeek'],
                'estimated_hours' => 8,
                'progress_percentage' => 45,
                'category_id' => $categories['Archivage'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['élimination', 'archives 2020', 'bordereau']),
                'created_at' => $d['lastWeek'],
                'updated_at' => $d['yesterday'],
            ],
        ];
    }

    private function tasksReunions(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Préparation réunion comité de direction',
                'description' => 'Préparer l\'ordre du jour et les documents pour la réunion du 10 août',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => Carbon::parse('2025-08-09'),
                'start_date' => null,
                'estimated_hours' => 3,
                'progress_percentage' => 0,
                'category_id' => $categories['Réunions'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['réunion', 'comité', 'préparation']),
                'created_at' => (clone $d['today'])->subDays(1),
                'updated_at' => (clone $d['today'])->subDays(1),
            ],
        ];
    }

    private function tasksAdministration(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Valider les demandes de congés en attente',
                'description' => 'Traiter les 5 demandes de congés en attente',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => (clone $d['tomorrow'])->addDays(2),
                'start_date' => null,
                'estimated_hours' => 1,
                'progress_percentage' => 0,
                'category_id' => $categories['Administration'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['congés', 'validation', 'RH']),
                'created_at' => $d['today'],
                'updated_at' => $d['today'],
            ],
        ];
    }

    private function tasksIT(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Configurer les nouveaux postes de travail',
                'description' => 'Installation et configuration des 3 nouveaux postes pour les nouveaux arrivants',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => $d['nextWeek'],
                'start_date' => null,
                'estimated_hours' => 6,
                'progress_percentage' => 0,
                'category_id' => $categories['Informatique'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['informatique', 'configuration', 'nouveaux postes']),
                'created_at' => (clone $d['today'])->subDays(3),
                'updated_at' => (clone $d['today'])->subDays(3),
            ],
        ];
    }

    private function tasksFormation(array $d, array $categories, array $organisations, int $superAdminId): array
    {
        return [
            [
                'title' => 'Préparer formation sur le nouveau système d\'archivage',
                'description' => 'Créer les supports de formation pour la session du 20 août',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => Carbon::parse('2025-08-15'),
                'start_date' => null,
                'estimated_hours' => 10,
                'progress_percentage' => 0,
                'category_id' => $categories['Formation'] ?? null,
                'assigned_to_organisation_id' => $organisations[self::ARCHIVES_ORG_NAME] ?? null,
                'assignment_type' => 'organisation',
                'created_by' => $superAdminId,
                'tags' => json_encode(['formation', 'archivage', 'supports']),
                'created_at' => (clone $d['today'])->subDays(5),
                'updated_at' => (clone $d['today'])->subDays(5),
            ],
        ];
    }

    private function tasksRH(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Finaliser l\'intégration des nouveaux employés',
                'description' => 'Compléter les dossiers administratifs et vérifier l\'accès aux systèmes',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => $d['tomorrow'],
                'start_date' => $d['yesterday'],
                'estimated_hours' => 4,
                'progress_percentage' => 50,
                'category_id' => $categories['Ressources Humaines'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['RH', 'intégration', 'nouveaux employés']),
                'created_at' => $d['yesterday'],
                'updated_at' => $d['today'],
            ],
        ];
    }

    private function tasksFinances(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Préparer les bons de commande matériel informatique',
                'description' => 'Créer les bons de commande pour le matériel validé lors du dernier comité',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => (clone $d['nextWeek'])->addDays(2),
                'start_date' => null,
                'estimated_hours' => 2,
                'progress_percentage' => 0,
                'category_id' => $categories['Finances'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['finances', 'commandes', 'matériel informatique']),
                'created_at' => (clone $d['today'])->subDay(),
                'updated_at' => (clone $d['today'])->subDay(),
            ],
        ];
    }

    private function tasksCompleted(array $d, array $categories, int $superAdminId): array
    {
        return [
            [
                'title' => 'Revue du rapport annuel d\'activité',
                'description' => 'Relecture et correction du rapport annuel avant publication',
                'status' => 'done',
                'priority' => 'high',
                'due_date' => $d['lastWeek'],
                'start_date' => (clone $d['lastWeek'])->subDays(3),
                'completed_at' => (clone $d['lastWeek'])->addDays(2),
                'estimated_hours' => 5,
                'actual_hours' => 6,
                'progress_percentage' => 100,
                'category_id' => $categories['Documentation'] ?? null,
                'assigned_to_user_id' => $superAdminId,
                'assignment_type' => 'user',
                'created_by' => $superAdminId,
                'tags' => json_encode(['rapport', 'relecture', 'publication']),
                'completion_notes' => 'Rapport relu et corrigé, envoyé pour impression le ' . (clone $d['lastWeek'])->addDays(2)->format('d/m/Y'),
                'created_at' => (clone $d['lastWeek'])->subDays(3),
                'updated_at' => (clone $d['lastWeek'])->addDays(2),
            ],
        ];
    }
}
