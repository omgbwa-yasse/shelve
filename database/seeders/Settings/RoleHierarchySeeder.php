<?php

namespace Database\Seeders\Settings;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;

/**
 * Rôles métier hiérarchiques et utilisateurs de démonstration du circuit courrier.
 *
 * Circuit incarné par ces comptes :
 *   1. Courrier entrant : déposé à l'accueil (accueil@example.com, Service Courrier
 *      de la DAG) → secrétariat du DG (secretariat@example.com) → coté par le DG
 *      (dg@example.com) vers une direction → réception validée par le directeur.
 *   2. Courrier sortant : initié par un agent (agent.*) → validé par son N+1
 *      (le directeur de sa direction) → signé par le DG. Un directeur peut initier
 *      sans validation intermédiaire.
 *
 * Idempotent : rejouable sans dupliquer (firstOrCreate + attach conditionnel).
 * Mot de passe commun : « password ».
 */
class RoleHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Rôles métier : permissions courrier + accès aux modules + permissions
        //    granulaires de consultation des modules exposés.
        //    IMPORTANT : `module_*_access` contrôle seulement l'affichage du menu ;
        //    les pages vérifient en plus des permissions fines (ex. RecordController
        //    exige `records_view`). Il faut donc accorder les deux, sinon la page
        //    renvoie « 403 This action is unauthorized ».
        //    Les accès sont volontairement différenciés par rôle : le DG voit large,
        //    l'agent se limite au courrier.
        $rolePermissions = [
            'DG' => [
                // Courrier (écriture complète)
                'mail_viewAny', 'mail_view', 'mail_create', 'mail_update', 'mail_delete', 'mail_config',
                // Accès aux modules (menu)
                'module_mails_access', 'module_repositories_access', 'module_communications_access',
                'module_tools_access', 'module_deposits_access', 'module_workflow_access',
                // Consultation fine des modules exposés
                'records_view', 'records_create', 'records_edit', 'records_search', 'records_export', 'authors_view',
                'communications_view', 'communications_create',
                // Outils / référentiels d'archivage (plan de classement, rétention, etc.)
                'activity_viewAny', 'activity_view', 'activity_create', 'activity_update', 'activity_delete',
                'retention_viewAny', 'retention_view', 'retention_create',
                'communicability_viewAny', 'communicability_view', 'communicability_create',
                'law_viewAny', 'law_view',
                'organisations_view', 'organisations_create', 'organisations_update',
            ],
            'directeur' => [
                'mail_viewAny', 'mail_view', 'mail_create', 'mail_update',
                'module_mails_access', 'module_repositories_access', 'module_communications_access',
                'module_tools_access',
                'records_view', 'records_create', 'records_edit', 'records_search', 'authors_view',
                'communications_view',
                // Consultation des référentiels d'archivage
                'activity_viewAny', 'activity_view',
                'retention_viewAny', 'retention_view',
                'communicability_viewAny', 'communicability_view',
                'organisations_view',
            ],
            'responsable' => [
                'mail_viewAny', 'mail_view', 'mail_create', 'mail_update',
                'module_mails_access', 'module_repositories_access',
                'records_view', 'records_search', 'authors_view',
            ],
            'agent' => [
                'mail_viewAny', 'mail_view', 'mail_create',
                'module_mails_access',
            ],
        ];

        // S'assurer que les permissions des référentiels Tools existent (elles ne
        // sont pas créées par PermissionCategorySeeder) afin que les gates dynamiques
        // soient enregistrés et que le sous-menu Tools s'affiche.
        $toolPermissions = [
            'activity_viewAny' => 'Voir le plan de classement',
            'activity_view' => 'Voir une activité',
            'activity_create' => 'Créer une activité',
            'activity_update' => 'Modifier une activité',
            'activity_delete' => 'Supprimer une activité',
            'retention_viewAny' => 'Voir le référentiel de conservation',
            'retention_view' => 'Voir une règle de conservation',
            'retention_create' => 'Créer une règle de conservation',
            'communicability_viewAny' => 'Voir les règles de communicabilité',
            'communicability_view' => 'Voir une règle de communicabilité',
            'communicability_create' => 'Créer une règle de communicabilité',
            'law_viewAny' => 'Voir les lois',
            'law_view' => 'Voir une loi',
        ];
        foreach ($toolPermissions as $name => $desc) {
            Permission::firstOrCreate(['name' => $name], ['category' => 'tools', 'description' => $desc]);
        }

        $roles = [];
        foreach ($rolePermissions as $roleName => $permNames) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => 'Rôle métier ' . $roleName]
            );

            $permIds = Permission::whereIn('name', $permNames)->pluck('id');
            $role->permissions()->syncWithoutDetaching($permIds);

            $roles[$roleName] = $role;
        }

        $this->command->info('Rôles métier créés : ' . implode(', ', array_keys($roles)));

        // 2. Organisations de référence (robuste à d'éventuels doublons de code :
        //    pour la DG on retient la racine effective, celle qui porte les directions).
        $dg = Organisation::where('code', 'DG')
            ->withCount('children')
            ->orderByDesc('children_count')
            ->first();

        if (!$dg) {
            $this->command->warn('Organisation DG absente : lancez d\'abord OrganisationSeeder.');
            return;
        }

        $dsi     = Organisation::where('code', 'DSI')->first();
        $drh     = Organisation::where('code', 'DRH')->first();
        $dag     = Organisation::where('code', 'DAG')->first();
        $accueil = Organisation::where('code', 'DAG-COUR')->first(); // Service Courrier & Accueil

        // 3. Utilisateurs de démonstration : [email, nom, prénom, organisation, rôle]
        $demoUsers = [
            // Direction Générale : le DG signe tout courrier sortant et cote l'entrant.
            ['dg@example.com',          'Directeur',   'Général',     $dg,      'DG'],
            ['secretariat@example.com', 'Secrétariat', 'DG',          $dg,      'agent'],

            // Accueil : point de dépôt obligatoire du courrier externe entrant.
            ['accueil@example.com',     'Agent',       'Accueil',     $accueil, 'responsable'],

            // Direction des Systèmes d'Information
            ['dir.dsi@example.com',     'Directeur',   'DSI',         $dsi,     'directeur'],
            ['agent.dsi@example.com',   'Agent',       'DSI',         $dsi,     'agent'],

            // Direction des Ressources Humaines
            ['dir.drh@example.com',     'Directeur',   'DRH',         $drh,     'directeur'],
            ['agent.drh@example.com',   'Agent',       'DRH',         $drh,     'agent'],

            // Direction des Affaires Générales
            ['dir.dag@example.com',     'Directeur',   'DAG',         $dag,     'directeur'],
            ['agent.dag@example.com',   'Agent',       'DAG',         $dag,     'agent'],
        ];

        $created = 0;
        foreach ($demoUsers as [$email, $name, $surname, $org, $roleName]) {
            if (!$org) {
                $this->command->warn("Organisation absente pour {$email} : utilisateur ignoré.");
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'surname' => $surname,
                    'password' => Hash::make('password'),
                    'birthday' => Carbon::parse('1985-01-01'),
                    'current_organisation_id' => $org->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Rôle global (permissions courrier via le système natif).
            $user->assignRole($roleName);

            // Rôle contextuel dans l'organisation (pivot user_organisation_role).
            if (!$user->organisations()->where('organisation_id', $org->id)->exists()) {
                $user->organisations()->attach($org->id, [
                    'role_id' => $roles[$roleName]->id,
                    'creator_id' => $user->id,
                ]);
            }

            // La DSI (service informatique) a accès à presque tous les modules :
            // on complète ses utilisateurs par des permissions directes d'accès module.
            if (in_array($email, ['dir.dsi@example.com', 'agent.dsi@example.com'], true)) {
                $this->grantDsiBroadAccess($user);
            }

            $created++;
        }

        $this->command->info("Utilisateurs de démonstration hiérarchiques créés : {$created} (mot de passe : password)");
    }

    /**
     * Accorde à un utilisateur DSI un accès large (presque tous les modules),
     * cohérent avec le rôle transverse du service informatique.
     */
    private function grantDsiBroadAccess(User $user): void
    {
        $moduleAccess = Permission::where('name', 'like', 'module_%_access')->pluck('id');
        // Permissions de consultation transverses pour éviter les 403 sur les index.
        $views = Permission::whereIn('name', [
            'records_view', 'records_create', 'records_edit', 'records_search', 'records_export', 'authors_view',
            'communications_view', 'communications_create',
            'dashboard_view',
        ])->pluck('id');

        $user->permissions()->syncWithoutDetaching($moduleAccess->merge($views)->unique());
    }
}
