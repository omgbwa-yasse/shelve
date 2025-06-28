<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionCheckSeeder extends Seeder
{
    /**
     * Affiche un résumé détaillé de toutes les permissions disponibles
     *
     * @return void
     */
    public function run()
    {
        $this->command->line('=== ANALYSE DES PERMISSIONS DISPONIBLES ===');

        $permissions = DB::table('permissions')->orderBy('id')->get(['id', 'name', 'description']);

        if ($permissions->isEmpty()) {
            $this->command->error('Aucune permission trouvée ! Exécutez d\'abord PermissionSeeder');
            return;
        }

        $this->command->info('Total permissions: ' . count($permissions));
        $this->command->line('');

        // Grouper par catégories
        $categories = [
            'user_' => 'Gestion Utilisateurs',
            'role_' => 'Gestion Rôles',
            'organisation_' => 'Gestion Organisations',
            'record_' => 'Gestion Archives',
            'mail_' => 'Gestion Courrier',
            'communication_' => 'Communications',
            'tool_' => 'Outils',
            'transferring_' => 'Transferts',
            'deposit_' => 'Dépôts/Bâtiments',
            'dolly_' => 'Chariots',
            'building_' => 'Bâtiments',
            'floor_' => 'Étages',
            'room_' => 'Salles',
            'shelf_' => 'Étagères',
            'container_' => 'Conteneurs',
            'backup_' => 'Sauvegardes',
            'setting_' => 'Paramètres',
            'bulletin_board_' => 'Tableaux Affichage',
            'event_' => 'Événements',
            'post_' => 'Publications',
            'public_portal_' => 'Portail Public',
            'ai_' => 'Intelligence Artificielle',
            'barcode_' => 'Codes-barres',
            'log_' => 'Journaux',
            'report_' => 'Rapports',
            'retention_' => 'Rétention',
            'law_' => 'Législation',
            'communicability_' => 'Communicabilité',
            'module_' => 'Accès Modules',
        ];

        foreach ($categories as $prefix => $categoryName) {
            $categoryPermissions = $permissions->filter(function($perm) use ($prefix) {
                return str_starts_with($perm->name, $prefix);
            });

            if ($categoryPermissions->count() > 0) {
                $this->command->comment($categoryName . ' (' . $categoryPermissions->count() . ' permissions):');
                foreach ($categoryPermissions as $perm) {
                    $this->command->line('  ' . $perm->id . '. ' . $perm->name);
                }
                $this->command->line('');
            }
        }

        // Permissions spéciales (modules)
        $modulePermissions = $permissions->filter(function($perm) {
            return str_contains($perm->name, 'module_') && str_contains($perm->name, '_access');
        });

        $this->command->comment('=== PERMISSIONS D\'ACCÈS AUX MODULES ===');
        foreach ($modulePermissions as $perm) {
            $moduleName = str_replace(['module_', '_access'], '', $perm->name);
            $this->command->line('  ✓ Module: ' . ucfirst(str_replace('_', ' ', $moduleName)));
        }

        $this->command->line('');
        $this->command->line('=== RÉSUMÉ ===');
        $this->command->info('Permissions CRUD/métier: ' . ($permissions->count() - $modulePermissions->count()));
        $this->command->info('Permissions d\'accès modules: ' . $modulePermissions->count());
        $this->command->info('TOTAL: ' . $permissions->count() . ' permissions');
    }
}
