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
        $permissions = array_merge(
            $this->getUserManagementPermissions(),
            $this->getContentManagementPermissions(),
            $this->getCommunicationPermissions(),
            $this->getLocationManagementPermissions(),
            $this->getSystemManagementPermissions(),
            $this->getPortalPermissions(),
            $this->getTechnicalPermissions(),
            $this->getModuleAccessPermissions()
        );

        // Use updateOrInsert for each permission to preserve existing data
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('Permissions updated successfully! Total: ' . count($permissions) . ' permissions');
    }

    /**
     * Get user and role management permissions
     *
     * @return array
     */
    private function getUserManagementPermissions(): array
    {
        $now = Carbon::now();

        return [
            // User permissions (IDs 1-6)
            ['id' => 1, 'name' => 'user_viewAny', 'description' => 'Autorisation de voir tous les utilisateurs', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'user_view', 'description' => 'Autorisation de voir un utilisateur spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'user_create', 'description' => 'Autorisation de créer un nouvel utilisateur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'user_update', 'description' => 'Autorisation de mettre à jour un utilisateur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'user_delete', 'description' => 'Autorisation de supprimer un utilisateur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'user_force_delete', 'description' => 'Autorisation de supprimer définitivement un utilisateur', 'created_at' => $now, 'updated_at' => $now],

            // Role permissions (IDs 7-12)
            ['id' => 7, 'name' => 'role_viewAny', 'description' => 'Autorisation de voir tous les rôles', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'role_view', 'description' => 'Autorisation de voir un rôle spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'name' => 'role_create', 'description' => 'Autorisation de créer un nouveau rôle', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'role_update', 'description' => 'Autorisation de mettre à jour un rôle', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'name' => 'role_delete', 'description' => 'Autorisation de supprimer un rôle', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'name' => 'role_force_delete', 'description' => 'Autorisation de supprimer définitivement un rôle', 'created_at' => $now, 'updated_at' => $now],

            // Organisation permissions (IDs 13-18)
            ['id' => 13, 'name' => 'organisation_viewAny', 'description' => 'Autorisation de voir toutes les organisations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'name' => 'organisation_view', 'description' => 'Autorisation de voir une organisation spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'name' => 'organisation_create', 'description' => 'Autorisation de créer une nouvelle organisation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'name' => 'organisation_update', 'description' => 'Autorisation de mettre à jour une organisation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 17, 'name' => 'organisation_delete', 'description' => 'Autorisation de supprimer une organisation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'name' => 'organisation_force_delete', 'description' => 'Autorisation de supprimer définitivement une organisation', 'created_at' => $now, 'updated_at' => $now],

            // Activity permissions (IDs 19-24)
            ['id' => 19, 'name' => 'activity_viewAny', 'description' => 'Autorisation de voir toutes les activités', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'name' => 'activity_view', 'description' => 'Autorisation de voir une activité spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 21, 'name' => 'activity_create', 'description' => 'Autorisation de créer une nouvelle activité', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'name' => 'activity_update', 'description' => 'Autorisation de mettre à jour une activité', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 23, 'name' => 'activity_delete', 'description' => 'Autorisation de supprimer une activité', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 24, 'name' => 'activity_force_delete', 'description' => 'Autorisation de supprimer définitivement une activité', 'created_at' => $now, 'updated_at' => $now],

            // Author permissions (IDs 25-30)
            ['id' => 25, 'name' => 'author_viewAny', 'description' => 'Autorisation de voir tous les auteurs', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 26, 'name' => 'author_view', 'description' => 'Autorisation de voir un auteur spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 27, 'name' => 'author_create', 'description' => 'Autorisation de créer un nouvel auteur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 28, 'name' => 'author_update', 'description' => 'Autorisation de mettre à jour un auteur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 29, 'name' => 'author_delete', 'description' => 'Autorisation de supprimer un auteur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 30, 'name' => 'author_force_delete', 'description' => 'Autorisation de supprimer définitivement un auteur', 'created_at' => $now, 'updated_at' => $now],

            // Language permissions (IDs 31-36)
            ['id' => 31, 'name' => 'language_viewAny', 'description' => 'Autorisation de voir toutes les langues', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 32, 'name' => 'language_view', 'description' => 'Autorisation de voir une langue spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 33, 'name' => 'language_create', 'description' => 'Autorisation de créer une nouvelle langue', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 34, 'name' => 'language_update', 'description' => 'Autorisation de mettre à jour une langue', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 35, 'name' => 'language_delete', 'description' => 'Autorisation de supprimer une langue', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 36, 'name' => 'language_force_delete', 'description' => 'Autorisation de supprimer définitivement une langue', 'created_at' => $now, 'updated_at' => $now],

            // Term permissions (IDs 37-42)
            ['id' => 37, 'name' => 'term_viewAny', 'description' => 'Autorisation de voir tous les termes', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 38, 'name' => 'term_view', 'description' => 'Autorisation de voir un terme spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 39, 'name' => 'term_create', 'description' => 'Autorisation de créer un nouveau terme', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 40, 'name' => 'term_update', 'description' => 'Autorisation de mettre à jour un terme', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 41, 'name' => 'term_delete', 'description' => 'Autorisation de supprimer un terme', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 42, 'name' => 'term_force_delete', 'description' => 'Autorisation de supprimer définitivement un terme', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    /**
     * Get content management permissions (records, mail, slip)
     *
     * @return array
     */
    private function getContentManagementPermissions(): array
    {
        $now = Carbon::now();

        return [
            // Record permissions (IDs 43-48)
            ['id' => 43, 'name' => 'record_viewAny', 'description' => 'Autorisation de voir tous les enregistrements', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 44, 'name' => 'record_view', 'description' => 'Autorisation de voir un enregistrement spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 45, 'name' => 'record_create', 'description' => 'Autorisation de créer un nouvel enregistrement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 46, 'name' => 'record_update', 'description' => 'Autorisation de mettre à jour un enregistrement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 47, 'name' => 'record_delete', 'description' => 'Autorisation de supprimer un enregistrement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 48, 'name' => 'record_force_delete', 'description' => 'Autorisation de supprimer définitivement un enregistrement', 'created_at' => $now, 'updated_at' => $now],

            // Mail permissions (IDs 49-54)
            ['id' => 49, 'name' => 'mail_viewAny', 'description' => 'Autorisation de voir tous les mails', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 50, 'name' => 'mail_view', 'description' => 'Autorisation de voir un mail spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 51, 'name' => 'mail_create', 'description' => 'Autorisation de créer un nouveau mail', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 52, 'name' => 'mail_update', 'description' => 'Autorisation de mettre à jour un mail', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 53, 'name' => 'mail_delete', 'description' => 'Autorisation de supprimer un mail', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 54, 'name' => 'mail_force_delete', 'description' => 'Autorisation de supprimer définitivement un mail', 'created_at' => $now, 'updated_at' => $now],

            // Slip permissions (IDs 55-60)
            ['id' => 55, 'name' => 'slip_viewAny', 'description' => 'Autorisation de voir tous les bordereaux', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 56, 'name' => 'slip_view', 'description' => 'Autorisation de voir un bordereau spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 57, 'name' => 'slip_create', 'description' => 'Autorisation de créer un nouveau bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 58, 'name' => 'slip_update', 'description' => 'Autorisation de mettre à jour un bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 59, 'name' => 'slip_delete', 'description' => 'Autorisation de supprimer un bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 60, 'name' => 'slip_force_delete', 'description' => 'Autorisation de supprimer définitivement un bordereau', 'created_at' => $now, 'updated_at' => $now],

            // Slip record permissions (IDs 61-66)
            ['id' => 61, 'name' => 'slip_record_viewAny', 'description' => 'Autorisation de voir tous les enregistrements de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 62, 'name' => 'slip_record_view', 'description' => 'Autorisation de voir un enregistrement de bordereau spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 63, 'name' => 'slip_record_create', 'description' => 'Autorisation de créer un nouvel enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 64, 'name' => 'slip_record_update', 'description' => 'Autorisation de mettre à jour un enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 65, 'name' => 'slip_record_delete', 'description' => 'Autorisation de supprimer un enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 66, 'name' => 'slip_record_force_delete', 'description' => 'Autorisation de supprimer définitivement un enregistrement de bordereau', 'created_at' => $now, 'updated_at' => $now],

            // Tool permissions (IDs 73-78)
            ['id' => 73, 'name' => 'tool_viewAny', 'description' => 'Autorisation de voir tous les outils', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 74, 'name' => 'tool_view', 'description' => 'Autorisation de voir un outil spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 75, 'name' => 'tool_create', 'description' => 'Autorisation de créer un nouvel outil', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 76, 'name' => 'tool_update', 'description' => 'Autorisation de mettre à jour un outil', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 77, 'name' => 'tool_delete', 'description' => 'Autorisation de supprimer un outil', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 78, 'name' => 'tool_force_delete', 'description' => 'Autorisation de supprimer définitivement un outil', 'created_at' => $now, 'updated_at' => $now],

            // Transferring permissions (IDs 79-84)
            ['id' => 79, 'name' => 'transferring_viewAny', 'description' => 'Autorisation de voir tous les transferts', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 80, 'name' => 'transferring_view', 'description' => 'Autorisation de voir un transfert spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 81, 'name' => 'transferring_create', 'description' => 'Autorisation de créer un nouveau transfert', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 82, 'name' => 'transferring_update', 'description' => 'Autorisation de mettre à jour un transfert', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 83, 'name' => 'transferring_delete', 'description' => 'Autorisation de supprimer un transfert', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 84, 'name' => 'transferring_force_delete', 'description' => 'Autorisation de supprimer définitivement un transfert', 'created_at' => $now, 'updated_at' => $now],

            // Task permissions (IDs 85-90)
            ['id' => 85, 'name' => 'task_viewAny', 'description' => 'Autorisation de voir toutes les tâches', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 86, 'name' => 'task_view', 'description' => 'Autorisation de voir une tâche spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 87, 'name' => 'task_create', 'description' => 'Autorisation de créer une nouvelle tâche', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 88, 'name' => 'task_update', 'description' => 'Autorisation de mettre à jour une tâche', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 89, 'name' => 'task_delete', 'description' => 'Autorisation de supprimer une tâche', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 90, 'name' => 'task_force_delete', 'description' => 'Autorisation de supprimer définitivement une tâche', 'created_at' => $now, 'updated_at' => $now],

            // Deposit permissions (IDs 91-96)
            ['id' => 91, 'name' => 'deposit_viewAny', 'description' => 'Autorisation de voir tous les dépôts', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 92, 'name' => 'deposit_view', 'description' => 'Autorisation de voir un dépôt spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 93, 'name' => 'deposit_create', 'description' => 'Autorisation de créer un nouveau dépôt', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 94, 'name' => 'deposit_update', 'description' => 'Autorisation de mettre à jour un dépôt', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 95, 'name' => 'deposit_delete', 'description' => 'Autorisation de supprimer un dépôt', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 96, 'name' => 'deposit_force_delete', 'description' => 'Autorisation de supprimer définitivement un dépôt', 'created_at' => $now, 'updated_at' => $now],

            // Dolly permissions (IDs 97-102)
            ['id' => 97, 'name' => 'dolly_viewAny', 'description' => 'Autorisation de voir tous les chariots', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 98, 'name' => 'dolly_view', 'description' => 'Autorisation de voir un chariot spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 99, 'name' => 'dolly_create', 'description' => 'Autorisation de créer un nouveau chariot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 100, 'name' => 'dolly_update', 'description' => 'Autorisation de mettre à jour un chariot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 101, 'name' => 'dolly_delete', 'description' => 'Autorisation de supprimer un chariot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 102, 'name' => 'dolly_force_delete', 'description' => 'Autorisation de supprimer définitivement un chariot', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    /**
     * Get communication permissions
     *
     * @return array
     */
    private function getCommunicationPermissions(): array
    {
        $now = Carbon::now();

        return [
            // Communication permissions (IDs 67-72)
            ['id' => 67, 'name' => 'communication_viewAny', 'description' => 'Autorisation de voir toutes les communications', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 68, 'name' => 'communication_view', 'description' => 'Autorisation de voir une communication spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 69, 'name' => 'communication_create', 'description' => 'Autorisation de créer une nouvelle communication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 70, 'name' => 'communication_update', 'description' => 'Autorisation de mettre à jour une communication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 71, 'name' => 'communication_delete', 'description' => 'Autorisation de supprimer une communication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 72, 'name' => 'communication_force_delete', 'description' => 'Autorisation de supprimer définitivement une communication', 'created_at' => $now, 'updated_at' => $now],

            // Reservation permissions (IDs 103-108)
            ['id' => 103, 'name' => 'reservation_viewAny', 'description' => 'Autorisation de voir toutes les réservations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 104, 'name' => 'reservation_view', 'description' => 'Autorisation de voir une réservation spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 105, 'name' => 'reservation_create', 'description' => 'Autorisation de créer une nouvelle réservation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 106, 'name' => 'reservation_update', 'description' => 'Autorisation de mettre à jour une réservation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 107, 'name' => 'reservation_delete', 'description' => 'Autorisation de supprimer une réservation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 108, 'name' => 'reservation_force_delete', 'description' => 'Autorisation de supprimer définitivement une réservation', 'created_at' => $now, 'updated_at' => $now],

            // Batch permissions (IDs 109-114)
            ['id' => 109, 'name' => 'batch_viewAny', 'description' => 'Autorisation de voir tous les lots', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 110, 'name' => 'batch_view', 'description' => 'Autorisation de voir un lot spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 111, 'name' => 'batch_create', 'description' => 'Autorisation de créer un nouveau lot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 112, 'name' => 'batch_update', 'description' => 'Autorisation de mettre à jour un lot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 113, 'name' => 'batch_delete', 'description' => 'Autorisation de supprimer un lot', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 114, 'name' => 'batch_force_delete', 'description' => 'Autorisation de supprimer définitivement un lot', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    /**
     * Get location management permissions (building, floor, room, shelf, container)
     *
     * @return array
     */
    private function getLocationManagementPermissions(): array
    {
        $now = Carbon::now();

        return [
            // Building permissions (IDs 115-120)
            ['id' => 115, 'name' => 'building_viewAny', 'description' => 'Autorisation de voir tous les bâtiments', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 116, 'name' => 'building_view', 'description' => 'Autorisation de voir un bâtiment spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 117, 'name' => 'building_create', 'description' => 'Autorisation de créer un nouveau bâtiment', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 118, 'name' => 'building_update', 'description' => 'Autorisation de mettre à jour un bâtiment', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 119, 'name' => 'building_delete', 'description' => 'Autorisation de supprimer un bâtiment', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 120, 'name' => 'building_force_delete', 'description' => 'Autorisation de supprimer définitivement un bâtiment', 'created_at' => $now, 'updated_at' => $now],

            // Floor permissions (IDs 121-126)
            ['id' => 121, 'name' => 'floor_viewAny', 'description' => 'Autorisation de voir tous les étages', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 122, 'name' => 'floor_view', 'description' => 'Autorisation de voir un étage spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 123, 'name' => 'floor_create', 'description' => 'Autorisation de créer un nouvel étage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 124, 'name' => 'floor_update', 'description' => 'Autorisation de mettre à jour un étage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 125, 'name' => 'floor_delete', 'description' => 'Autorisation de supprimer un étage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 126, 'name' => 'floor_force_delete', 'description' => 'Autorisation de supprimer définitivement un étage', 'created_at' => $now, 'updated_at' => $now],

            // Room permissions (IDs 127-132)
            ['id' => 127, 'name' => 'room_viewAny', 'description' => 'Autorisation de voir toutes les salles', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 128, 'name' => 'room_view', 'description' => 'Autorisation de voir une salle spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 129, 'name' => 'room_create', 'description' => 'Autorisation de créer une nouvelle salle', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 130, 'name' => 'room_update', 'description' => 'Autorisation de mettre à jour une salle', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 131, 'name' => 'room_delete', 'description' => 'Autorisation de supprimer une salle', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 132, 'name' => 'room_force_delete', 'description' => 'Autorisation de supprimer définitivement une salle', 'created_at' => $now, 'updated_at' => $now],

            // Shelf permissions (IDs 133-138)
            ['id' => 133, 'name' => 'shelf_viewAny', 'description' => 'Autorisation de voir toutes les étagères', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 134, 'name' => 'shelf_view', 'description' => 'Autorisation de voir une étagère spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 135, 'name' => 'shelf_create', 'description' => 'Autorisation de créer une nouvelle étagère', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 136, 'name' => 'shelf_update', 'description' => 'Autorisation de mettre à jour une étagère', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 137, 'name' => 'shelf_delete', 'description' => 'Autorisation de supprimer une étagère', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 138, 'name' => 'shelf_force_delete', 'description' => 'Autorisation de supprimer définitivement une étagère', 'created_at' => $now, 'updated_at' => $now],

            // Container permissions (IDs 139-144)
            ['id' => 139, 'name' => 'container_viewAny', 'description' => 'Autorisation de voir tous les conteneurs', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 140, 'name' => 'container_view', 'description' => 'Autorisation de voir un conteneur spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 141, 'name' => 'container_create', 'description' => 'Autorisation de créer un nouveau conteneur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 142, 'name' => 'container_update', 'description' => 'Autorisation de mettre à jour un conteneur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 143, 'name' => 'container_delete', 'description' => 'Autorisation de supprimer un conteneur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 144, 'name' => 'container_force_delete', 'description' => 'Autorisation de supprimer définitivement un conteneur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 143, 'name' => 'container_delete', 'description' => 'Autorisation de supprimer un conteneur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 144, 'name' => 'container_force_delete', 'description' => 'Autorisation de supprimer définitivement un conteneur', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    /**
     * Get system management permissions (backup, setting, bulletin board, event, post)
     *
     * @return array
     */
    private function getSystemManagementPermissions(): array
    {
        $now = Carbon::now();

        return [
            // Backup permissions (IDs 145-150)
            ['id' => 145, 'name' => 'backup_viewAny', 'description' => 'Autorisation de voir toutes les sauvegardes', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 146, 'name' => 'backup_view', 'description' => 'Autorisation de voir une sauvegarde spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 147, 'name' => 'backup_create', 'description' => 'Autorisation de créer une nouvelle sauvegarde', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 148, 'name' => 'backup_update', 'description' => 'Autorisation de mettre à jour une sauvegarde', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 149, 'name' => 'backup_delete', 'description' => 'Autorisation de supprimer une sauvegarde', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 150, 'name' => 'backup_force_delete', 'description' => 'Autorisation de supprimer définitivement une sauvegarde', 'created_at' => $now, 'updated_at' => $now],

            // Setting permissions (IDs 151-156)
            ['id' => 151, 'name' => 'setting_viewAny', 'description' => 'Autorisation de voir tous les paramètres', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 152, 'name' => 'setting_view', 'description' => 'Autorisation de voir un paramètre spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 153, 'name' => 'setting_create', 'description' => 'Autorisation de créer un nouveau paramètre', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 154, 'name' => 'setting_update', 'description' => 'Autorisation de mettre à jour un paramètre', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 155, 'name' => 'setting_delete', 'description' => 'Autorisation de supprimer un paramètre', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 156, 'name' => 'setting_force_delete', 'description' => 'Autorisation de supprimer définitivement un paramètre', 'created_at' => $now, 'updated_at' => $now],

            // Bulletin Board permissions (IDs 157-162)
            ['id' => 157, 'name' => 'bulletin_board_viewAny', 'description' => 'Autorisation de voir tous les tableaux d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 158, 'name' => 'bulletin_board_view', 'description' => 'Autorisation de voir un tableau d\'affichage spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 159, 'name' => 'bulletin_board_create', 'description' => 'Autorisation de créer un nouveau tableau d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 160, 'name' => 'bulletin_board_update', 'description' => 'Autorisation de mettre à jour un tableau d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 161, 'name' => 'bulletin_board_delete', 'description' => 'Autorisation de supprimer un tableau d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 162, 'name' => 'bulletin_board_force_delete', 'description' => 'Autorisation de supprimer définitivement un tableau d\'affichage', 'created_at' => $now, 'updated_at' => $now],

            // Event permissions (IDs 163-168)
            ['id' => 163, 'name' => 'event_viewAny', 'description' => 'Autorisation de voir tous les événements', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 164, 'name' => 'event_view', 'description' => 'Autorisation de voir un événement spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 165, 'name' => 'event_create', 'description' => 'Autorisation de créer un nouvel événement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 166, 'name' => 'event_update', 'description' => 'Autorisation de mettre à jour un événement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 167, 'name' => 'event_delete', 'description' => 'Autorisation de supprimer un événement', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 168, 'name' => 'event_force_delete', 'description' => 'Autorisation de supprimer définitivement un événement', 'created_at' => $now, 'updated_at' => $now],

            // Post permissions (IDs 169-174)
            ['id' => 169, 'name' => 'post_viewAny', 'description' => 'Autorisation de voir toutes les publications', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 170, 'name' => 'post_view', 'description' => 'Autorisation de voir une publication spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 171, 'name' => 'post_create', 'description' => 'Autorisation de créer une nouvelle publication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 172, 'name' => 'post_update', 'description' => 'Autorisation de mettre à jour une publication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 173, 'name' => 'post_delete', 'description' => 'Autorisation de supprimer une publication', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 174, 'name' => 'post_force_delete', 'description' => 'Autorisation de supprimer définitivement une publication', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    /**
     * Get portal permissions
     *
     * @return array
     */
    private function getPortalPermissions(): array
    {
        $now = Carbon::now();

        return [
            // Public Portal permissions (IDs 175-180)
            ['id' => 175, 'name' => 'public_portal_viewAny', 'description' => 'Autorisation de voir tous les éléments du portail public', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 176, 'name' => 'public_portal_view', 'description' => 'Autorisation de voir un élément du portail public spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 177, 'name' => 'public_portal_create', 'description' => 'Autorisation de créer un nouvel élément du portail public', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 178, 'name' => 'public_portal_update', 'description' => 'Autorisation de mettre à jour un élément du portail public', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 179, 'name' => 'public_portal_delete', 'description' => 'Autorisation de supprimer un élément du portail public', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 180, 'name' => 'public_portal_force_delete', 'description' => 'Autorisation de supprimer définitivement un élément du portail public', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    /**
     * Get technical permissions (AI, barcode, log, report, retention, law, communicability)
     *
     * @return array
     */
    private function getTechnicalPermissions(): array
    {
        $now = Carbon::now();

        return [
            // AI permissions (IDs 181-186)
            ['id' => 181, 'name' => 'ai_viewAny', 'description' => 'Autorisation de voir tous les éléments AI', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 182, 'name' => 'ai_view', 'description' => 'Autorisation de voir un élément AI spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 183, 'name' => 'ai_create', 'description' => 'Autorisation de créer un nouvel élément AI', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 184, 'name' => 'ai_update', 'description' => 'Autorisation de mettre à jour un élément AI', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 185, 'name' => 'ai_delete', 'description' => 'Autorisation de supprimer un élément AI', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 186, 'name' => 'ai_force_delete', 'description' => 'Autorisation de supprimer définitivement un élément AI', 'created_at' => $now, 'updated_at' => $now],

            // Barcode permissions (IDs 187-192)
            ['id' => 187, 'name' => 'barcode_viewAny', 'description' => 'Autorisation de voir tous les codes-barres', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 188, 'name' => 'barcode_view', 'description' => 'Autorisation de voir un code-barre spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 189, 'name' => 'barcode_create', 'description' => 'Autorisation de créer un nouveau code-barre', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 190, 'name' => 'barcode_update', 'description' => 'Autorisation de mettre à jour un code-barre', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 191, 'name' => 'barcode_delete', 'description' => 'Autorisation de supprimer un code-barre', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 192, 'name' => 'barcode_force_delete', 'description' => 'Autorisation de supprimer définitivement un code-barre', 'created_at' => $now, 'updated_at' => $now],

            // Log permissions (IDs 193-198)
            ['id' => 193, 'name' => 'log_viewAny', 'description' => 'Autorisation de voir tous les journaux', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 194, 'name' => 'log_view', 'description' => 'Autorisation de voir un journal spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 195, 'name' => 'log_create', 'description' => 'Autorisation de créer un nouveau journal', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 196, 'name' => 'log_update', 'description' => 'Autorisation de mettre à jour un journal', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 197, 'name' => 'log_delete', 'description' => 'Autorisation de supprimer un journal', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 198, 'name' => 'log_force_delete', 'description' => 'Autorisation de supprimer définitivement un journal', 'created_at' => $now, 'updated_at' => $now],

            // Report permissions (IDs 199-204)
            ['id' => 199, 'name' => 'report_viewAny', 'description' => 'Autorisation de voir tous les rapports', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 200, 'name' => 'report_view', 'description' => 'Autorisation de voir un rapport spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 201, 'name' => 'report_create', 'description' => 'Autorisation de créer un nouveau rapport', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 202, 'name' => 'report_update', 'description' => 'Autorisation de mettre à jour un rapport', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 203, 'name' => 'report_delete', 'description' => 'Autorisation de supprimer un rapport', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 204, 'name' => 'report_force_delete', 'description' => 'Autorisation de supprimer définitivement un rapport', 'created_at' => $now, 'updated_at' => $now],

            // Retention permissions (IDs 205-210)
            ['id' => 205, 'name' => 'retention_viewAny', 'description' => 'Autorisation de voir toutes les rétentions', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 206, 'name' => 'retention_view', 'description' => 'Autorisation de voir une rétention spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 207, 'name' => 'retention_create', 'description' => 'Autorisation de créer une nouvelle rétention', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 208, 'name' => 'retention_update', 'description' => 'Autorisation de mettre à jour une rétention', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 209, 'name' => 'retention_delete', 'description' => 'Autorisation de supprimer une rétention', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 210, 'name' => 'retention_force_delete', 'description' => 'Autorisation de supprimer définitivement une rétention', 'created_at' => $now, 'updated_at' => $now],

            // Law permissions (IDs 211-216)
            ['id' => 211, 'name' => 'law_viewAny', 'description' => 'Autorisation de voir toutes les lois', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 212, 'name' => 'law_view', 'description' => 'Autorisation de voir une loi spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 213, 'name' => 'law_create', 'description' => 'Autorisation de créer une nouvelle loi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 214, 'name' => 'law_update', 'description' => 'Autorisation de mettre à jour une loi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 215, 'name' => 'law_delete', 'description' => 'Autorisation de supprimer une loi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 216, 'name' => 'law_force_delete', 'description' => 'Autorisation de supprimer définitivement une loi', 'created_at' => $now, 'updated_at' => $now],

            // Communicability permissions (IDs 217-222)
            ['id' => 217, 'name' => 'communicability_viewAny', 'description' => 'Autorisation de voir toutes les communicabilités', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 218, 'name' => 'communicability_view', 'description' => 'Autorisation de voir une communicabilité spécifique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 219, 'name' => 'communicability_create', 'description' => 'Autorisation de créer une nouvelle communicabilité', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 220, 'name' => 'communicability_update', 'description' => 'Autorisation de mettre à jour une communicabilité', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 221, 'name' => 'communicability_delete', 'description' => 'Autorisation de supprimer une communicabilité', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 222, 'name' => 'communicability_force_delete', 'description' => 'Autorisation de supprimer définitivement une communicabilité', 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    /**
     * Get module access permissions (pour contrôler l'accès aux 11 modules de navigation principale)
     *
     * @return array
     */
    private function getModuleAccessPermissions(): array
    {
        $now = Carbon::now();

        return [

            // Module de base
            ['id' => 223, 'name' => 'module_bulletin_boards_access', 'description' => 'Autorisation d\'accéder au module tableaux d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 224, 'name' => 'module_mails_access', 'description' => 'Autorisation d\'accéder au module courrier', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 225, 'name' => 'module_repositories_access', 'description' => 'Autorisation d\'accéder au module dossiers/archives', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 226, 'name' => 'module_communications_access', 'description' => 'Autorisation d\'accéder au module communications', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 227, 'name' => 'module_transferrings_access', 'description' => 'Autorisation d\'accéder au module transferts/bordereaux', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 228, 'name' => 'module_deposits_access', 'description' => 'Autorisation d\'accéder au module bâtiments/dépôts', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 229, 'name' => 'module_tools_access', 'description' => 'Autorisation d\'accéder au module outils/activités', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 230, 'name' => 'module_dollies_access', 'description' => 'Autorisation d\'accéder au module chariots', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 231, 'name' => 'module_ai_access', 'description' => 'Autorisation d\'accéder au module intelligence artificielle', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 232, 'name' => 'module_public_access', 'description' => 'Autorisation d\'accéder au module portail public', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 233, 'name' => 'module_settings_access', 'description' => 'Autorisation d\'accéder au module paramètres', 'created_at' => $now, 'updated_at' => $now],

            // Module de gestion des utilisateurs
            ['id' => 234, 'name' => 'module_users_access', 'description' => 'Autorisation d\'accéder au module gestion des utilisateurs', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 241, 'name' => 'module_search_access', 'description' => 'Autorisation d\'utiliser la fonction de recherche globale', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 242, 'name' => 'module_advanced_search_access', 'description' => 'Autorisation d\'utiliser la recherche avancée', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 243, 'name' => 'module_org_switching_access', 'description' => 'Autorisation de changer d\'organisation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 244, 'name' => 'module_language_switching_access', 'description' => 'Autorisation de changer de langue', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 245, 'name' => 'module_profile_access', 'description' => 'Autorisation d\'accéder à la gestion du profil utilisateur', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 246, 'name' => 'module_navigation_access', 'description' => 'Autorisation d\'accéder à la navigation globale', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 247, 'name' => 'module_import_export_access', 'description' => 'Autorisation d\'utiliser les fonctions d\'import/export', 'created_at' => $now, 'updated_at' => $now],
        ];
    }
}
