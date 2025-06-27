<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        // Truncate the permissions table to avoid conflicts
        DB::table('permissions')->truncate();

        $permissions = [
            // Record permissions
            ['id' => 40, 'name' => 'record_update', 'description' => 'Autorisation de mettre à jour un enregistrement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 41, 'name' => 'record_create', 'description' => 'Autorisation de créer un nouvel enregistrement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 42, 'name' => 'record_view', 'description' => 'Autorisation de voir un enregistrement spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 43, 'name' => 'record_viewAny', 'description' => 'Autorisation de voir tous les enregistrements', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 44, 'name' => 'record_delete', 'description' => 'Autorisation de supprimer un enregistrement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 45, 'name' => 'record_force_delete', 'description' => 'Autorisation de supprimer définitivement un enregistrement', 'created_at' => $now, 'updated_at' => $now],

            // Mail permissions
            ['id' => 46, 'name' => 'mail_update', 'description' => 'Autorisation de mettre à jour un mail', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 47, 'name' => 'mail_create', 'description' => 'Autorisation de créer un nouveau mail', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 48, 'name' => 'mail_view', 'description' => 'Autorisation de voir un mail spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 49, 'name' => 'mail_viewAny', 'description' => 'Autorisation de voir tous les mails', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 50, 'name' => 'mail_delete', 'description' => 'Autorisation de supprimer un mail', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 51, 'name' => 'mail_force_delete', 'description' => 'Autorisation de supprimer définitivement un mail', 'created_at' => $now, 'updated_at' => $now],

            // Slip permissions
            ['id' => 52, 'name' => 'slip_update', 'description' => 'Autorisation de mettre à jour un bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 53, 'name' => 'slip_create', 'description' => 'Autorisation de créer un nouveau bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 54, 'name' => 'slip_view', 'description' => 'Autorisation de voir un bordereau spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 55, 'name' => 'slip_viewAny', 'description' => 'Autorisation de voir tous les bordereaux', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 56, 'name' => 'slip_delete', 'description' => 'Autorisation de supprimer un bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 57, 'name' => 'slip_force_delete', 'description' => 'Autorisation de supprimer définitivement un bordereau', 'created_at' => $now, 'updated_at' => $now],

            // Slip record permissions
            ['id' => 58, 'name' => 'slip_record_update', 'description' => 'Autorisation de mettre à jour un enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 59, 'name' => 'slip_record_create', 'description' => 'Autorisation de créer un nouvel enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 60, 'name' => 'slip_record_view', 'description' => 'Autorisation de voir un enregistrement de bordereau spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 61, 'name' => 'slip_record_viewAny', 'description' => 'Autorisation de voir tous les enregistrements de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 62, 'name' => 'slip_record_delete', 'description' => 'Autorisation de supprimer un enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 63, 'name' => 'slip_record_force_delete', 'description' => 'Autorisation de supprimer définitivement un enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],

            // Communication permissions
            ['id' => 64, 'name' => 'communication_update', 'description' => 'Autorisation de mettre à jour une communication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 65, 'name' => 'communication_create', 'description' => 'Autorisation de créer une nouvelle communication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 66, 'name' => 'communication_view', 'description' => 'Autorisation de voir une communication spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 67, 'name' => 'communication_viewAny', 'description' => 'Autorisation de voir toutes les communications', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 68, 'name' => 'communication_delete', 'description' => 'Autorisation de supprimer une communication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 69, 'name' => 'communication_force_delete', 'description' => 'Autorisation de supprimer définitivement une communication', 'created_at' => $now, 'updated_at' => $now],

            // Tool permissions
            ['id' => 70, 'name' => 'tool_update', 'description' => 'Autorisation de mettre à jour un outil', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 71, 'name' => 'tool_create', 'description' => 'Autorisation de créer un nouvel outil', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 72, 'name' => 'tool_view', 'description' => 'Autorisation de voir un outil spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 73, 'name' => 'tool_viewAny', 'description' => 'Autorisation de voir tous les outils', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 74, 'name' => 'tool_delete', 'description' => 'Autorisation de supprimer un outil', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 75, 'name' => 'tool_force_delete', 'description' => 'Autorisation de supprimer définitivement un outil', 'created_at' => $now, 'updated_at' => $now],

            // Transferring permissions
            ['id' => 76, 'name' => 'transferring_update', 'description' => 'Autorisation de mettre à jour un transfert', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 77, 'name' => 'transferring_create', 'description' => 'Autorisation de créer un nouveau transfert', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 78, 'name' => 'transferring_view', 'description' => 'Autorisation de voir un transfert spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 79, 'name' => 'transferring_viewAny', 'description' => 'Autorisation de voir tous les transferts', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 80, 'name' => 'transferring_delete', 'description' => 'Autorisation de supprimer un transfert', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 81, 'name' => 'transferring_force_delete', 'description' => 'Autorisation de supprimer définitivement un transfert', 'created_at' => $now, 'updated_at' => $now],

            // Task permissions
            ['id' => 82, 'name' => 'task_update', 'description' => 'Autorisation de mettre à jour une tâche', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 83, 'name' => 'task_create', 'description' => 'Autorisation de créer une nouvelle tâche', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 84, 'name' => 'task_view', 'description' => 'Autorisation de voir une tâche spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 85, 'name' => 'task_viewAny', 'description' => 'Autorisation de voir toutes les tâches', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 86, 'name' => 'task_delete', 'description' => 'Autorisation de supprimer une tâche', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 87, 'name' => 'task_force_delete', 'description' => 'Autorisation de supprimer définitivement une tâche', 'created_at' => $now, 'updated_at' => $now],

            // Deposit permissions
            ['id' => 88, 'name' => 'deposit_update', 'description' => 'Autorisation de mettre à jour un dépôt', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 89, 'name' => 'deposit_create', 'description' => 'Autorisation de créer un nouveau dépôt', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 90, 'name' => 'deposit_view', 'description' => 'Autorisation de voir un dépôt spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 91, 'name' => 'deposit_viewAny', 'description' => 'Autorisation de voir tous les dépôts', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 92, 'name' => 'deposit_delete', 'description' => 'Autorisation de supprimer un dépôt', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 93, 'name' => 'deposit_force_delete', 'description' => 'Autorisation de supprimer définitivement un dépôt', 'created_at' => $now, 'updated_at' => $now],

            // Dolly permissions
            ['id' => 94, 'name' => 'dolly_update', 'description' => 'Autorisation de mettre à jour un chariot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 95, 'name' => 'dolly_create', 'description' => 'Autorisation de créer un nouveau chariot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 96, 'name' => 'dolly_view', 'description' => 'Autorisation de voir un chariot spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 97, 'name' => 'dolly_viewAny', 'description' => 'Autorisation de voir tous les chariots', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 98, 'name' => 'dolly_delete', 'description' => 'Autorisation de supprimer un chariot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 99, 'name' => 'dolly_force_delete', 'description' => 'Autorisation de supprimer définitivement un chariot', 'created_at' => $now, 'updated_at' => $now],
        ];

        // Insert permissions in batches for better performance
        $chunks = array_chunk($permissions, 50);
        foreach ($chunks as $chunk) {
            DB::table('permissions')->insert($chunk);
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
