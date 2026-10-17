<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Jeu de données minimal de la suite de conformité (contracts/conformance).
 *
 * Deux organisations, et un agent rattaché à UNE SEULE d'entre elles : c'est cette
 * asymétrie qui permet de vérifier l'isolation multi-organisation (risque R03).
 * Un jeu mono-organisation rendrait ce contrôle invérifiable.
 *
 * Ce seeder est le point de départ de l'E2ESeeder de la phase 2 (§2.2.2).
 *
 * Usage :
 *   DB_DATABASE=shelve_test php artisan db:seed --class=ConformanceSeeder
 */
class ConformanceSeeder extends Seeder
{
    public const EMAIL = 'conformance@shelve.test';
    public const PASSWORD = 'conformance-secret';

    public function run(): void
    {
        $orgPrincipale = Organisation::firstOrCreate(
            ['code' => 'CONF-A'],
            ['name' => 'Organisation de conformité A', 'description' => 'Jeu de conformité']
        );

        // Organisation à laquelle l'agent n'est PAS rattaché : sert de cible aux
        // tentatives d'accès inter-organisation, qui doivent échouer.
        $orgEtrangere = Organisation::firstOrCreate(
            ['code' => 'CONF-B'],
            ['name' => 'Organisation de conformité B', 'description' => 'Jeu de conformité']
        );

        $role = Role::firstOrCreate(
            ['name' => 'conformance-agent'],
            ['description' => 'Rôle du compte de conformité', 'guard_name' => 'web']
        );

        $user = User::updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Conformité',
                'surname' => 'API',
                // `users.birthday` est NOT NULL sans valeur par défaut.
                'birthday' => '1990-01-01',
                'password' => Hash::make(self::PASSWORD),
                'current_organisation_id' => $orgPrincipale->id,
                'email_verified_at' => now(),
            ]
        );

        // `user_organisation_role` impose role_id ET creator_id (NOT NULL) : la
        // relation User::organisations() ne suffit pas à elle seule.
        if (!$user->organisations()->whereKey($orgPrincipale->id)->exists()) {
            $user->organisations()->attach($orgPrincipale->id, [
                'role_id' => $role->id,
                'creator_id' => $user->id,
            ]);
        }

        $this->grantD01Permissions($user);

        $this->command?->info('Jeu de conformité prêt :');
        $this->command?->line('  API_TEST_EMAIL=' . self::EMAIL);
        $this->command?->line('  API_TEST_PASSWORD=' . self::PASSWORD);
        $this->command?->line('  API_TEST_FOREIGN_ORG_ID=' . $orgEtrangere->id);
    }

    /**
     * Les référentiels D01 sont exposés en API avec des Policies qui refusent par
     * défaut : sans permissions, le compte de conformité recevrait 403 sur chaque
     * ressource et la suite ne testerait que des refus. On lui rattache donc les
     * permissions D01 (user_permissions), comme le ferait la configuration RBAC
     * réelle d'un agent habilité.
     */
    private function grantD01Permissions(User $user): void
    {
        $resources = [
            'activity', 'language', 'sort', 'communicability', 'keyword', 'law',
            'author', 'author_contact', 'external_contact', 'external_organization',
            'setting', 'setting_category', 'reference_list',
            'building', 'floor', 'room', 'shelf', 'container',
            'container_property', 'container_status',
            // D09 — CRUD d'organisation (le pivot `user_organisation_role_*` est
            // VOLONTAIREMENT exclu : la suite de conformité vérifie que le compte
            // ne peut PAS gérer les rattachements hors de son périmètre).
            //
            // Nommage : les Policies D09 attendent des noms PLURIELS pour
            // `organisations_*` et `users_*` (contrairement aux référentiels D01/D03
            // qui sont au singulier). C'est le nom réellement contrôlé par
            // OrganisationPolicy et UserPolicy — seeder le singulier rendrait 403
            // chaque requête de la suite D09.
            'organisations', 'role', 'users',

            // D04–D16 — ressources CRUD portées en phase 3 (vagues 2–6). Chaque
            // ressource reçoit ses 5 permissions pour que les tests d'intégration
            // JUnit et la suite de conformité puissent l'exercer.
            'slip', 'slip_status', 'slip_record', 'communication', 'communication_record',
            'reservation', 'reservation_record', 'retention', 'retention_activity',
            'retention_law_article', 'declassement_list', 'workflow_definition',
            'workflow_instance', 'backup', 'backup_file', 'backup_planning', 'log',
            'mail_action', 'mail_priority', 'mail_typology', 'mail', 'batch',
            'batch_transaction', 'mail_container', 'mail_archive', 'dolly',
            'ai_skill', 'ai_template', 'prompt', 'project', 'project_milestone',
            'project_deliverable', 'project_resource', 'project_risk',
            'project_status_report', 'objective', 'key_result', 'kpi', 'kpi_measurement',
            'task', 'task_dependency', 'workplace', 'workplace_conversation',
            'workplace_template', 'thesaurus_scheme', 'thesaurus_concept',
            'record_status', 'record_support', 'record_level', 'record_confidentiality',
            'ai_conversation', 'ai_routine', 'record', 'record_type', 'record_child',
            'record_author', 'record_container', 'record_attachment', 'search',
            'workplace_activity', 'workplace_bookmark', 'workplace_member',
            'workplace_document',
        ];

        foreach ($resources as $resource) {
            foreach (['viewAny', 'view', 'create', 'update', 'delete'] as $ability) {
                $permission = Permission::firstOrCreate(
                    ['name' => "{$resource}_{$ability}"],
                    ['category' => 'settings', 'description' => "D01 {$resource}_{$ability} (conformit�)", 'guard_name' => 'web']
                );
                $user->permissions()->syncWithoutDetaching($permission->id);
            }
        }

        // D14 - sandbox Python de l'assistant IA : permissions `ai_sandbox_*`
        // (actions, pas de viewAny/view) accordées au compte de conformité.
        foreach (['ai_sandbox_viewAny', 'ai_sandbox_view', 'ai_sandbox_open', 'ai_sandbox_write', 'ai_sandbox_run', 'ai_sandbox_close'] as $name) {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['category' => 'ai', 'description' => "D14 {$name} (conformit�)", 'guard_name' => 'web']
            );
            $user->permissions()->syncWithoutDetaching($permission->id);
        }
    }
}
