<?php

namespace Database\Seeders\Workflow;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;

class WorkflowPhase3PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Ajoute les permissions spÃ©cifiques au workflow Phase 3 des documents numÃ©riques
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('ðŸ” CrÃ©ation des permissions Workflow Phase 3...');

        // Permissions pour le workflow des documents numÃ©riques
        $this->createDigitalDocumentWorkflowPermissions();

        // Assigner automatiquement toutes les nouvelles permissions au superadmin
        $this->assignPermissionsToSuperAdmin();

        $this->command->info('âœ… Permissions Workflow Phase 3 crÃ©Ã©es et attribuÃ©es au superadmin!');
    }

    /**
     * CrÃ©er les permissions pour le workflow des documents numÃ©riques
     */
    private function createDigitalDocumentWorkflowPermissions()
    {
        $permissions = [
            // Checkout/Checkin (RÃ©servation documents)
            [
                'name' => 'digital_records.checkout',
                'category' => 'digital_records',
                'description' => 'RÃ©server un document numÃ©rique pour Ã©dition'
            ],
            [
                'name' => 'digital_records.checkin',
                'category' => 'digital_records',
                'description' => 'DÃ©poser une nouvelle version d\'un document rÃ©servÃ©'
            ],
            [
                'name' => 'digital_records.cancel_checkout',
                'category' => 'digital_records',
                'description' => 'Annuler la rÃ©servation d\'un document'
            ],

            // Signature Ã©lectronique
            [
                'name' => 'digital_records.sign',
                'category' => 'digital_records',
                'description' => 'Signer Ã©lectroniquement un document'
            ],
            [
                'name' => 'digital_records.verify_signature',
                'category' => 'digital_records',
                'description' => 'VÃ©rifier l\'intÃ©gritÃ© d\'une signature Ã©lectronique'
            ],
            [
                'name' => 'digital_records.revoke_signature',
                'category' => 'digital_records',
                'description' => 'RÃ©voquer une signature Ã©lectronique'
            ],

            // Gestion des versions
            [
                'name' => 'digital_records.restore',
                'category' => 'digital_records',
                'description' => 'Restaurer une version antÃ©rieure d\'un document'
            ],
            [
                'name' => 'digital_records.download',
                'category' => 'digital_records',
                'description' => 'TÃ©lÃ©charger un document ou une version'
            ],
            [
                'name' => 'digital_records.view_versions',
                'category' => 'digital_records',
                'description' => 'Voir l\'historique des versions d\'un document'
            ],

            // Approbation
            [
                'name' => 'digital_records.approve',
                'category' => 'digital_records',
                'description' => 'Approuver un document nÃ©cessitant validation'
            ],
            [
                'name' => 'digital_records.reject',
                'category' => 'digital_records',
                'description' => 'Rejeter un document en attente d\'approbation'
            ],

            // Administration workflow
            [
                'name' => 'digital_records.workflow.admin',
                'category' => 'digital_records',
                'description' => 'Administration complÃ¨te du workflow (bypass toutes restrictions)'
            ],
            [
                'name' => 'digital_records.force_unlock',
                'category' => 'digital_records',
                'description' => 'Forcer le dÃ©verrouillage d\'un document rÃ©servÃ© par un autre utilisateur'
            ],
            [
                'name' => 'digital_records.force_revoke_signature',
                'category' => 'digital_records',
                'description' => 'RÃ©voquer la signature d\'un autre utilisateur (admin)'
            ],

            // Permissions de base documents numÃ©riques (si pas dÃ©jÃ  crÃ©Ã©es)
            [
                'name' => 'digital_records.view',
                'category' => 'digital_records',
                'description' => 'Voir les documents numÃ©riques'
            ],
            [
                'name' => 'digital_records.create',
                'category' => 'digital_records',
                'description' => 'CrÃ©er des documents numÃ©riques'
            ],
            [
                'name' => 'digital_records.edit',
                'category' => 'digital_records',
                'description' => 'Modifier des documents numÃ©riques'
            ],
            [
                'name' => 'digital_records.delete',
                'category' => 'digital_records',
                'description' => 'Supprimer des documents numÃ©riques'
            ],

            // Permissions dossiers numÃ©riques
            [
                'name' => 'digital_folders.view',
                'category' => 'digital_records',
                'description' => 'Voir les dossiers numÃ©riques'
            ],
            [
                'name' => 'digital_folders.create',
                'category' => 'digital_records',
                'description' => 'CrÃ©er des dossiers numÃ©riques'
            ],
            [
                'name' => 'digital_folders.edit',
                'category' => 'digital_records',
                'description' => 'Modifier des dossiers numÃ©riques'
            ],
            [
                'name' => 'digital_folders.delete',
                'category' => 'digital_records',
                'description' => 'Supprimer des dossiers numÃ©riques'
            ],

            // Permissions archives physiques
            [
                'name' => 'physical_records.view',
                'category' => 'records',
                'description' => 'Voir les archives physiques'
            ],
            [
                'name' => 'physical_records.create',
                'category' => 'records',
                'description' => 'CrÃ©er des archives physiques'
            ],
            [
                'name' => 'physical_records.edit',
                'category' => 'records',
                'description' => 'Modifier des archives physiques'
            ],
            [
                'name' => 'physical_records.delete',
                'category' => 'records',
                'description' => 'Supprimer des archives physiques'
            ],
        ];

        $this->insertPermissions($permissions);

        $this->command->info('âœ… ' . count($permissions) . ' permissions Workflow Phase 3 crÃ©Ã©es');
    }

    /**
     * InsÃ©rer les permissions dans la base de donnÃ©es
     */
    private function insertPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    /**
     * Assigner toutes les permissions au rÃ´le superadmin
     */
    private function assignPermissionsToSuperAdmin()
    {
        $this->command->info('ðŸ”‘ Attribution des permissions au superadmin...');

        // RÃ©cupÃ©rer le rÃ´le superadmin
        $superadminRole = Role::where('name', 'superadmin')->first();

        if (!$superadminRole) {
            $this->command->error('âŒ RÃ´le superadmin non trouvÃ©. ExÃ©cutez SuperadminSeeder en premier.');
            return;
        }

        // RÃ©cupÃ©rer toutes les permissions
        $allPermissions = Permission::all();
        $permissionIds = $allPermissions->pluck('id')->toArray();

        // Synchroniser toutes les permissions avec le rÃ´le
        $superadminRole->permissions()->sync($permissionIds);

        $assignedCount = $superadminRole->permissions()->count();

        $this->command->info('âœ… Toutes les permissions (' . $assignedCount . ') attribuÃ©es au superadmin');

        // Afficher les catÃ©gories
        $this->displayPermissionsByCategory($allPermissions);
    }

    /**
     * Afficher les permissions par catÃ©gorie
     */
    private function displayPermissionsByCategory($allPermissions)
    {
        $this->command->info('');
        $this->command->info('ðŸ“Š RÃ©partition des permissions par catÃ©gorie :');

        $categories = $allPermissions->groupBy('category');
        $categoryStats = [];

        foreach ($categories as $category => $permissions) {
            $categoryName = $category ?: 'Non catÃ©gorisÃ©e';
            $categoryStats[$categoryName] = $permissions->count();
        }

        // Trier par nombre de permissions dÃ©croissant
        arsort($categoryStats);

        foreach ($categoryStats as $categoryName => $count) {
            $this->command->line('   â€¢ ' . ucfirst($categoryName) . ': ' . $count . ' permissions');
        }

        $this->command->info('');
        $this->command->line('Total: ' . $allPermissions->count() . ' permissions dans le systÃ¨me');
        $this->command->info('');
    }
}

