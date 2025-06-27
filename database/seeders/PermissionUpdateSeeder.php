<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder updates existing permissions or creates new ones without truncating the table.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $permissions = [
            // Record permissions
            ['id' => 40, 'name' => 'record_update', 'description' => 'Autorisation de mettre à jour un enregistrement'],
            ['id' => 41, 'name' => 'record_create', 'description' => 'Autorisation de créer un nouvel enregistrement'],
            ['id' => 42, 'name' => 'record_view', 'description' => 'Autorisation de voir un enregistrement spécifique'],
            ['id' => 43, 'name' => 'record_viewAny', 'description' => 'Autorisation de voir tous les enregistrements'],
            ['id' => 44, 'name' => 'record_delete', 'description' => 'Autorisation de supprimer un enregistrement'],
            ['id' => 45, 'name' => 'record_force_delete', 'description' => 'Autorisation de supprimer définitivement un enregistrement'],

            // Mail permissions
            ['id' => 46, 'name' => 'mail_update', 'description' => 'Autorisation de mettre à jour un mail'],
            ['id' => 47, 'name' => 'mail_create', 'description' => 'Autorisation de créer un nouveau mail'],
            ['id' => 48, 'name' => 'mail_view', 'description' => 'Autorisation de voir un mail spécifique'],
            ['id' => 49, 'name' => 'mail_viewAny', 'description' => 'Autorisation de voir tous les mails'],
            ['id' => 50, 'name' => 'mail_delete', 'description' => 'Autorisation de supprimer un mail'],
            ['id' => 51, 'name' => 'mail_force_delete', 'description' => 'Autorisation de supprimer définitivement un mail'],

            // Slip permissions
            ['id' => 52, 'name' => 'slip_update', 'description' => 'Autorisation de mettre à jour un bordereau'],
            ['id' => 53, 'name' => 'slip_create', 'description' => 'Autorisation de créer un nouveau bordereau'],
            ['id' => 54, 'name' => 'slip_view', 'description' => 'Autorisation de voir un bordereau spécifique'],
            ['id' => 55, 'name' => 'slip_viewAny', 'description' => 'Autorisation de voir tous les bordereaux'],
            ['id' => 56, 'name' => 'slip_delete', 'description' => 'Autorisation de supprimer un bordereau'],
            ['id' => 57, 'name' => 'slip_force_delete', 'description' => 'Autorisation de supprimer définitivement un bordereau'],

            // Slip record permissions
            ['id' => 58, 'name' => 'slip_record_update', 'description' => 'Autorisation de mettre à jour un enregistrement de bordereau'],
            ['id' => 59, 'name' => 'slip_record_create', 'description' => 'Autorisation de créer un nouvel enregistrement de bordereau'],
            ['id' => 60, 'name' => 'slip_record_view', 'description' => 'Autorisation de voir un enregistrement de bordereau spécifique'],
            ['id' => 61, 'name' => 'slip_record_viewAny', 'description' => 'Autorisation de voir tous les enregistrements de bordereau'],
            ['id' => 62, 'name' => 'slip_record_delete', 'description' => 'Autorisation de supprimer un enregistrement de bordereau'],
            ['id' => 63, 'name' => 'slip_record_force_delete', 'description' => 'Autorisation de supprimer définitivement un enregistrement de bordereau'],

            // Communication permissions
            ['id' => 64, 'name' => 'communication_update', 'description' => 'Autorisation de mettre à jour une communication'],
            ['id' => 65, 'name' => 'communication_create', 'description' => 'Autorisation de créer une nouvelle communication'],
            ['id' => 66, 'name' => 'communication_view', 'description' => 'Autorisation de voir une communication spécifique'],
            ['id' => 67, 'name' => 'communication_viewAny', 'description' => 'Autorisation de voir toutes les communications'],
            ['id' => 68, 'name' => 'communication_delete', 'description' => 'Autorisation de supprimer une communication'],
            ['id' => 69, 'name' => 'communication_force_delete', 'description' => 'Autorisation de supprimer définitivement une communication'],

            // Tool permissions
            ['id' => 70, 'name' => 'tool_update', 'description' => 'Autorisation de mettre à jour un outil'],
            ['id' => 71, 'name' => 'tool_create', 'description' => 'Autorisation de créer un nouvel outil'],
            ['id' => 72, 'name' => 'tool_view', 'description' => 'Autorisation de voir un outil spécifique'],
            ['id' => 73, 'name' => 'tool_viewAny', 'description' => 'Autorisation de voir tous les outils'],
            ['id' => 74, 'name' => 'tool_delete', 'description' => 'Autorisation de supprimer un outil'],
            ['id' => 75, 'name' => 'tool_force_delete', 'description' => 'Autorisation de supprimer définitivement un outil'],

            // Transferring permissions
            ['id' => 76, 'name' => 'transferring_update', 'description' => 'Autorisation de mettre à jour un transfert'],
            ['id' => 77, 'name' => 'transferring_create', 'description' => 'Autorisation de créer un nouveau transfert'],
            ['id' => 78, 'name' => 'transferring_view', 'description' => 'Autorisation de voir un transfert spécifique'],
            ['id' => 79, 'name' => 'transferring_viewAny', 'description' => 'Autorisation de voir tous les transferts'],
            ['id' => 80, 'name' => 'transferring_delete', 'description' => 'Autorisation de supprimer un transfert'],
            ['id' => 81, 'name' => 'transferring_force_delete', 'description' => 'Autorisation de supprimer définitivement un transfert'],

            // Task permissions
            ['id' => 82, 'name' => 'task_update', 'description' => 'Autorisation de mettre à jour une tâche'],
            ['id' => 83, 'name' => 'task_create', 'description' => 'Autorisation de créer une nouvelle tâche'],
            ['id' => 84, 'name' => 'task_view', 'description' => 'Autorisation de voir une tâche spécifique'],
            ['id' => 85, 'name' => 'task_viewAny', 'description' => 'Autorisation de voir toutes les tâches'],
            ['id' => 86, 'name' => 'task_delete', 'description' => 'Autorisation de supprimer une tâche'],
            ['id' => 87, 'name' => 'task_force_delete', 'description' => 'Autorisation de supprimer définitivement une tâche'],

            // Deposit permissions
            ['id' => 88, 'name' => 'deposit_update', 'description' => 'Autorisation de mettre à jour un dépôt'],
            ['id' => 89, 'name' => 'deposit_create', 'description' => 'Autorisation de créer un nouveau dépôt'],
            ['id' => 90, 'name' => 'deposit_view', 'description' => 'Autorisation de voir un dépôt spécifique'],
            ['id' => 91, 'name' => 'deposit_viewAny', 'description' => 'Autorisation de voir tous les dépôts'],
            ['id' => 92, 'name' => 'deposit_delete', 'description' => 'Autorisation de supprimer un dépôt'],
            ['id' => 93, 'name' => 'deposit_force_delete', 'description' => 'Autorisation de supprimer définitivement un dépôt'],

            // Dolly permissions
            ['id' => 94, 'name' => 'dolly_update', 'description' => 'Autorisation de mettre à jour un chariot'],
            ['id' => 95, 'name' => 'dolly_create', 'description' => 'Autorisation de créer un nouveau chariot'],
            ['id' => 96, 'name' => 'dolly_view', 'description' => 'Autorisation de voir un chariot spécifique'],
            ['id' => 97, 'name' => 'dolly_viewAny', 'description' => 'Autorisation de voir tous les chariots'],
            ['id' => 98, 'name' => 'dolly_delete', 'description' => 'Autorisation de supprimer un chariot'],
            ['id' => 99, 'name' => 'dolly_force_delete', 'description' => 'Autorisation de supprimer définitivement un chariot'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['id' => $permission['id']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $this->command->info('Permissions updated/inserted successfully!');
    }
}
