<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DigitalRecordPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Digital Folders permissions
            [
                'name' => 'digital_folders_view',
                'description' => 'View digital folders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_folders_create',
                'description' => 'Create digital folders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_folders_edit',
                'description' => 'Edit digital folders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_folders_delete',
                'description' => 'Delete digital folders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_folders_restore',
                'description' => 'Restore deleted digital folders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_folders_force_delete',
                'description' => 'Permanently delete digital folders',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Digital Documents permissions
            [
                'name' => 'digital_documents_view',
                'description' => 'View digital documents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_documents_create',
                'description' => 'Create digital documents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_documents_edit',
                'description' => 'Edit digital documents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_documents_delete',
                'description' => 'Delete digital documents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_documents_restore',
                'description' => 'Restore deleted digital documents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'digital_documents_force_delete',
                'description' => 'Permanently delete digital documents',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }

        $this->command->info('✅ ' . count($permissions) . ' permissions for digital records created/updated');

        // Assign all permissions to superadmin role
        $superadminRole = DB::table('roles')->where('name', 'superadmin')->first();

        if ($superadminRole) {
            $permissionIds = DB::table('permissions')
                ->whereIn('name', array_column($permissions, 'name'))
                ->pluck('id');

            foreach ($permissionIds as $permissionId) {
                // Use role_permissions table (native system)
                DB::table('role_permissions')->updateOrInsert(
                    [
                        'role_id' => $superadminRole->id,
                        'permission_id' => $permissionId,
                    ]
                );

                // Also use role_has_permissions table (Spatie system)
                DB::table('role_has_permissions')->updateOrInsert(
                    [
                        'role_id' => $superadminRole->id,
                        'permission_id' => $permissionId,
                    ]
                );
            }

            $this->command->info('✅ All digital record permissions assigned to superadmin role');
        } else {
            $this->command->warn('⚠️  Superadmin role not found. Permissions created but not assigned.');
        }
    }
}
