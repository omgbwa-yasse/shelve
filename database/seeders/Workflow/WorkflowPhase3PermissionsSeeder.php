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
     * Ajoute les permissions spécifiques au workflow Phase 3 des documents numériques
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🔐 Création des permissions Workflow Phase 3...');

        // Permissions pour le workflow des documents numériques
        $this->createDigitalDocumentWorkflowPermissions();

        // Assigner automatiquement toutes les nouvelles permissions au superadmin
        $this->assignPermissionsToSuperAdmin();

        $this->command->info('✅ Permissions Workflow Phase 3 créées et attribuées au superadmin!');
    }

    /**
     * Créer les permissions pour le workflow des documents numériques
     */
    private function createDigitalDocumentWorkflowPermissions()
    {
        $permissions = [
            // Checkout/Checkin (Réservation documents)
            [
                'name' => 'digital_records.checkout',
                'category' => 'digital_records',
                'description' => 'Réserver un document numérique pour édition'
            ],
            [
                'name' => 'digital_records.checkin',
                'category' => 'digital_records',
                'description' => 'Déposer une nouvelle version d\'un document réservé'
            ],
            [
                'name' => 'digital_records.cancel_checkout',
                'category' => 'digital_records',
                'description' => 'Annuler la réservation d\'un document'
            ],

            // Signature électronique
            [
                'name' => 'digital_records.sign',
                'category' => 'digital_records',
                'description' => 'Signer électroniquement un document'
            ],
            [
                'name' => 'digital_records.verify_signature',
                'category' => 'digital_records',
                'description' => 'Vérifier l\'intégrité d\'une signature électronique'
            ],
            [
                'name' => 'digital_records.revoke_signature',
                'category' => 'digital_records',
                'description' => 'Révoquer une signature électronique'
            ],

            // Gestion des versions
            [
                'name' => 'digital_records.restore',
                'category' => 'digital_records',
                'description' => 'Restaurer une version antérieure d\'un document'
            ],
            [
                'name' => 'digital_records.download',
                'category' => 'digital_records',
                'description' => 'Télécharger un document ou une version'
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
                'description' => 'Approuver un document nécessitant validation'
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
                'description' => 'Administration complète du workflow (bypass toutes restrictions)'
            ],
            [
                'name' => 'digital_records.force_unlock',
                'category' => 'digital_records',
                'description' => 'Forcer le déverrouillage d\'un document réservé par un autre utilisateur'
            ],
            [
                'name' => 'digital_records.force_revoke_signature',
                'category' => 'digital_records',
                'description' => 'Révoquer la signature d\'un autre utilisateur (admin)'
            ],

            // Permissions de base documents numériques (si pas déjà créées)
            [
                'name' => 'digital_records.view',
                'category' => 'digital_records',
                'description' => 'Voir les documents numériques'
            ],
            [
                'name' => 'digital_records.create',
                'category' => 'digital_records',
                'description' => 'Créer des documents numériques'
            ],
            [
                'name' => 'digital_records.edit',
                'category' => 'digital_records',
                'description' => 'Modifier des documents numériques'
            ],
            [
                'name' => 'digital_records.delete',
                'category' => 'digital_records',
                'description' => 'Supprimer des documents numériques'
            ],

            // Permissions dossiers numériques
            [
                'name' => 'digital_folders.view',
                'category' => 'digital_records',
                'description' => 'Voir les dossiers numériques'
            ],
            [
                'name' => 'digital_folders.create',
                'category' => 'digital_records',
                'description' => 'Créer des dossiers numériques'
            ],
            [
                'name' => 'digital_folders.edit',
                'category' => 'digital_records',
                'description' => 'Modifier des dossiers numériques'
            ],
            [
                'name' => 'digital_folders.delete',
                'category' => 'digital_records',
                'description' => 'Supprimer des dossiers numériques'
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
                'description' => 'Créer des archives physiques'
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

        $this->command->info('✅ ' . count($permissions) . ' permissions Workflow Phase 3 créées');
    }

    /**
     * Insérer les permissions dans la base de données
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
     * Assigner toutes les permissions au rôle superadmin
     */
    private function assignPermissionsToSuperAdmin()
    {
        $this->command->info('🔑 Attribution des permissions au superadmin...');

        // Récupérer le rôle superadmin
        $superadminRole = Role::where('name', 'superadmin')->first();

        if (!$superadminRole) {
            $this->command->error('❌ Rôle superadmin non trouvé. Exécutez SuperadminSeeder en premier.');
            return;
        }

        // Récupérer toutes les permissions
        $allPermissions = Permission::all();
        $permissionIds = $allPermissions->pluck('id')->toArray();

        // Synchroniser toutes les permissions avec le rôle
        $superadminRole->permissions()->sync($permissionIds);

        $assignedCount = $superadminRole->permissions()->count();

        $this->command->info('✅ Toutes les permissions (' . $assignedCount . ') attribuées au superadmin');

        // Afficher les catégories
        $this->displayPermissionsByCategory($allPermissions);
    }

    /**
     * Afficher les permissions par catégorie
     */
    private function displayPermissionsByCategory($allPermissions)
    {
        $this->command->info('');
        $this->command->info('📊 Répartition des permissions par catégorie :');

        $categories = $allPermissions->groupBy('category');
        $categoryStats = [];

        foreach ($categories as $category => $permissions) {
            $categoryName = $category ?: 'Non catégorisée';
            $categoryStats[$categoryName] = $permissions->count();
        }

        // Trier par nombre de permissions décroissant
        arsort($categoryStats);

        foreach ($categoryStats as $categoryName => $count) {
            $this->command->line('   • ' . ucfirst($categoryName) . ': ' . $count . ' permissions');
        }

        $this->command->info('');
        $this->command->line('Total: ' . $allPermissions->count() . ' permissions dans le système');
        $this->command->info('');
    }
}

