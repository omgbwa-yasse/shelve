<?php

namespace Database\Seeders\Records;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeclassementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Declassement lists
            ['name' => 'declassement_list_viewAny', 'description' => 'View declassement lists'],
            ['name' => 'declassement_list_view', 'description' => 'View a declassement list'],
            ['name' => 'declassement_list_create', 'description' => 'Create declassement lists'],
            ['name' => 'declassement_list_update', 'description' => 'Edit declassement lists'],
            ['name' => 'declassement_list_delete', 'description' => 'Delete declassement lists'],
            ['name' => 'declassement_list_force_delete', 'description' => 'Permanently delete declassement lists'],
            ['name' => 'declassement_list_request_approval', 'description' => 'Submit a declassement list for approval'],
            ['name' => 'declassement_list_approve', 'description' => 'Approve a declassement list'],
            ['name' => 'declassement_list_validate', 'description' => 'Validate a declassement list'],
            ['name' => 'declassement_list_process', 'description' => 'Process (execute) a declassement list'],
            ['name' => 'declassement_list_reject', 'description' => 'Reject a declassement list'],

            // Record reactivations
            ['name' => 'record_reactivation_viewAny', 'description' => 'View record reactivation requests'],
            ['name' => 'record_reactivation_view', 'description' => 'View a record reactivation request'],
            ['name' => 'record_reactivation_create', 'description' => 'Request a record reactivation'],
            ['name' => 'record_reactivation_approve', 'description' => 'Approve/reject a record reactivation request'],
        ];

        foreach ($permissions as &$permission) {
            $permission['created_at'] = now();
            $permission['updated_at'] = now();
        }
        unset($permission);

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info(count($permissions) . ' permissions for declassement/reactivation created/updated');

        $superadminRole = DB::table('roles')->where('name', 'superadmin')->first();

        if ($superadminRole) {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', array_column($permissions, 'name'))
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role_id' => $superadminRole->id,
                        'permission_id' => $permissionId,
                    ]
                );

                DB::table('role_has_permissions')->updateOrInsert(
                    [
                        'role_id' => $superadminRole->id,
                        'permission_id' => $permissionId,
                    ]
                );
            }

            $this->command->info('All declassement/reactivation permissions assigned to superadmin role');
        } else {
            $this->command->warn('Superadmin role not found. Permissions created but not assigned.');
        }
    }
}
