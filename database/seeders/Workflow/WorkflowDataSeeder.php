<?php

namespace Database\Seeders\Workflow;

use Illuminate\Database\Seeder;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowTransition;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\TaskComment;
use App\Models\User;

class WorkflowDataSeeder extends Seeder
{
    /**
     * Seed test data for the Workflow module.
     * Creates workflow definitions, instances, transitions, tasks with lifecycle data.
     * Idempotent: uses firstOrCreate/updateOrCreate.
     */
    public function run(): void
    {
        $this->command->info('⚙️  Seeding Workflow module test data...');

        $users = User::take(4)->get();
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No users found. Run SuperAdminSeeder first.');
            return;
        }
        $user = $users->first();

        // --- 1. Workflow Definitions ---
        $defApproval = WorkflowDefinition::firstOrCreate(
            ['name' => 'Approbation de document'],
            [
                'description' => 'Processus d\'approbation standard pour les documents. Soumission → Révision → Approbation/Rejet.',
                'bpmn_xml' => '<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL"><bpmn:process id="doc_approval"><bpmn:startEvent id="start"/><bpmn:task id="submit" name="Soumettre"/><bpmn:task id="review" name="Réviser"/><bpmn:task id="approve" name="Approuver"/><bpmn:endEvent id="end"/></bpmn:process></bpmn:definitions>',
                'version' => 1,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        $defTransfer = WorkflowDefinition::firstOrCreate(
            ['name' => 'Versement d\'archives'],
            [
                'description' => 'Processus de versement d\'archives : préparation du bordereau, validation, intégration.',
                'bpmn_xml' => '<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL"><bpmn:process id="transfer"><bpmn:startEvent id="start"/><bpmn:task id="prepare" name="Préparer bordereau"/><bpmn:task id="validate" name="Valider"/><bpmn:task id="integrate" name="Intégrer"/><bpmn:endEvent id="end"/></bpmn:process></bpmn:definitions>',
                'version' => 1,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        $defMail = WorkflowDefinition::firstOrCreate(
            ['name' => 'Traitement de courrier'],
            [
                'description' => 'Processus de traitement du courrier entrant : réception, enregistrement, distribution, traitement, archivage.',
                'bpmn_xml' => '<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL"><bpmn:process id="mail_process"><bpmn:startEvent id="start"/><bpmn:task id="register" name="Enregistrer"/><bpmn:task id="distribute" name="Distribuer"/><bpmn:task id="process" name="Traiter"/><bpmn:task id="archive" name="Archiver"/><bpmn:endEvent id="end"/></bpmn:process></bpmn:definitions>',
                'version' => 2,
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        // --- 2. Workflow Transitions ---
        $transitions = [
            // Approval workflow
            ['definition_id' => $defApproval->id, 'from_task_key' => 'submit', 'to_task_key' => 'review', 'name' => 'Soumettre pour révision', 'sequence_order' => 1, 'is_default' => true],
            ['definition_id' => $defApproval->id, 'from_task_key' => 'review', 'to_task_key' => 'approve', 'name' => 'Approuver', 'sequence_order' => 2, 'is_default' => true],
            ['definition_id' => $defApproval->id, 'from_task_key' => 'review', 'to_task_key' => 'submit', 'name' => 'Renvoyer pour correction', 'sequence_order' => 3, 'is_default' => false],
            // Transfer workflow
            ['definition_id' => $defTransfer->id, 'from_task_key' => 'prepare', 'to_task_key' => 'validate', 'name' => 'Soumettre pour validation', 'sequence_order' => 1, 'is_default' => true],
            ['definition_id' => $defTransfer->id, 'from_task_key' => 'validate', 'to_task_key' => 'integrate', 'name' => 'Valider et intégrer', 'sequence_order' => 2, 'is_default' => true],
            // Mail workflow
            ['definition_id' => $defMail->id, 'from_task_key' => 'register', 'to_task_key' => 'distribute', 'name' => 'Distribuer le courrier', 'sequence_order' => 1, 'is_default' => true],
            ['definition_id' => $defMail->id, 'from_task_key' => 'distribute', 'to_task_key' => 'process', 'name' => 'Traiter le courrier', 'sequence_order' => 2, 'is_default' => true],
            ['definition_id' => $defMail->id, 'from_task_key' => 'process', 'to_task_key' => 'archive', 'name' => 'Archiver le courrier', 'sequence_order' => 3, 'is_default' => true],
        ];

        foreach ($transitions as $t) {
            WorkflowTransition::firstOrCreate(
                ['definition_id' => $t['definition_id'], 'from_task_key' => $t['from_task_key'], 'to_task_key' => $t['to_task_key']],
                array_merge($t, ['created_by' => $user->id, 'updated_by' => $user->id])
            );
        }

        // --- 3. Workflow Instances ---
        $instances = [
            ['definition_id' => $defApproval->id, 'name' => 'Approbation - Rapport annuel 2025', 'status' => 'completed', 'started_at' => now()->subDays(30), 'completed_at' => now()->subDays(25), 'completed_by' => $user->id],
            ['definition_id' => $defApproval->id, 'name' => 'Approbation - Note de frais février', 'status' => 'running', 'started_at' => now()->subDays(5)],
            ['definition_id' => $defApproval->id, 'name' => 'Approbation - Contrat prestataire', 'status' => 'running', 'started_at' => now()->subDays(2)],
            ['definition_id' => $defTransfer->id, 'name' => 'Versement - Archives DRH 2020-2024', 'status' => 'completed', 'started_at' => now()->subDays(45), 'completed_at' => now()->subDays(40), 'completed_by' => $users->count() > 1 ? $users[1]->id : $user->id],
            ['definition_id' => $defTransfer->id, 'name' => 'Versement - Comptabilité exercice 2023', 'status' => 'running', 'started_at' => now()->subDays(10)],
            ['definition_id' => $defMail->id, 'name' => 'Traitement courrier #IN-2026-001', 'status' => 'completed', 'started_at' => now()->subDays(20), 'completed_at' => now()->subDays(18), 'completed_by' => $user->id],
            ['definition_id' => $defMail->id, 'name' => 'Traitement courrier #IN-2026-003', 'status' => 'paused', 'started_at' => now()->subDays(7)],
        ];

        $createdInstances = [];
        foreach ($instances as $inst) {
            $wi = WorkflowInstance::firstOrCreate(
                ['name' => $inst['name']],
                array_merge($inst, [
                    'current_state' => ['step' => $inst['status'] === 'completed' ? 'end' : 'in_progress'],
                    'started_by' => $user->id,
                    'updated_by' => $user->id,
                ])
            );
            $createdInstances[] = $wi;
        }

        // --- 4. Tasks ---
        $taskData = [
            // Tasks for completed approval
            ['title' => 'Soumettre le rapport annuel', 'status' => 'completed', 'priority' => 'high', 'task_key' => 'submit', 'workflow_instance_id' => $createdInstances[0]->id, 'completed_at' => now()->subDays(28)],
            ['title' => 'Réviser le rapport annuel', 'status' => 'completed', 'priority' => 'high', 'task_key' => 'review', 'workflow_instance_id' => $createdInstances[0]->id, 'completed_at' => now()->subDays(26)],
            ['title' => 'Approuver le rapport annuel', 'status' => 'completed', 'priority' => 'high', 'task_key' => 'approve', 'workflow_instance_id' => $createdInstances[0]->id, 'completed_at' => now()->subDays(25)],
            // Tasks for running approval
            ['title' => 'Soumettre la note de frais', 'status' => 'completed', 'priority' => 'medium', 'task_key' => 'submit', 'workflow_instance_id' => $createdInstances[1]->id, 'completed_at' => now()->subDays(4)],
            ['title' => 'Réviser la note de frais', 'status' => 'in_progress', 'priority' => 'medium', 'task_key' => 'review', 'workflow_instance_id' => $createdInstances[1]->id],
            // Tasks for transfer workflow
            ['title' => 'Préparer bordereau DRH', 'status' => 'completed', 'priority' => 'high', 'task_key' => 'prepare', 'workflow_instance_id' => $createdInstances[3]->id, 'completed_at' => now()->subDays(43)],
            ['title' => 'Valider bordereau DRH', 'status' => 'completed', 'priority' => 'high', 'task_key' => 'validate', 'workflow_instance_id' => $createdInstances[3]->id, 'completed_at' => now()->subDays(41)],
            ['title' => 'Intégrer archives DRH', 'status' => 'completed', 'priority' => 'high', 'task_key' => 'integrate', 'workflow_instance_id' => $createdInstances[3]->id, 'completed_at' => now()->subDays(40)],
            // Standalone tasks (no workflow)
            ['title' => 'Inventaire salle d\'archives RDC', 'status' => 'pending', 'priority' => 'low', 'task_key' => null, 'workflow_instance_id' => null, 'due_date' => now()->addDays(15)],
            ['title' => 'Mise à jour du plan de classement', 'status' => 'in_progress', 'priority' => 'medium', 'task_key' => null, 'workflow_instance_id' => null, 'due_date' => now()->addDays(7)],
            ['title' => 'Formation utilisateurs module courrier', 'status' => 'pending', 'priority' => 'low', 'task_key' => null, 'workflow_instance_id' => null, 'due_date' => now()->addDays(30)],
        ];

        $createdTasks = [];
        foreach ($taskData as $i => $td) {
            $assignee = $users[$i % $users->count()];
            $task = Task::firstOrCreate(
                ['title' => $td['title']],
                array_merge($td, [
                    'description' => 'Tâche de test : ' . $td['title'],
                    'assigned_to' => $assignee->id,
                    'sequence_order' => $i + 1,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'completed_by' => isset($td['completed_at']) ? $assignee->id : null,
                ])
            );
            $createdTasks[] = $task;
        }

        // --- 5. Task History ---
        foreach (array_slice($createdTasks, 0, 6) as $task) {
            TaskHistory::firstOrCreate(
                ['task_id' => $task->id, 'action' => 'created'],
                [
                    'user_id' => $user->id,
                    'description' => "Tâche '{$task->title}' créée",
                    'old_value' => null,
                    'new_value' => $task->status,
                    'created_at' => now()->subDays(rand(5, 30)),
                ]
            );
        }

        // --- 6. Task Comments ---
        $comments = [
            'Document conforme, prêt pour validation.',
            'Merci de vérifier les pages 12 à 15.',
            'J\'ai ajouté les pièces jointes manquantes.',
            'Point à discuter en réunion.',
        ];
        foreach (array_slice($createdTasks, 0, 4) as $i => $task) {
            TaskComment::firstOrCreate(
                ['task_id' => $task->id, 'user_id' => $users[$i % $users->count()]->id],
                [
                    'content' => $comments[$i],
                    'created_at' => now()->subDays(rand(1, 10)),
                ]
            );
        }

        $this->command->info('✅ Workflow module: ' . count($createdInstances) . ' instances, ' . count($createdTasks) . ' tasks seeded.');
    }
}
