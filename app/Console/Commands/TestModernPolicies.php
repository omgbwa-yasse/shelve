<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Organisation;
use App\Models\Record;
use App\Services\PolicyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestModernPolicies extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:modern-policies {user_id?}';

    /**
     * The console command description.
     */
    protected $description = 'Tester le système de policies modernisé avec Gate et Spatie';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Test du système de policies modernisé');
        $this->line('================================================');

        // Récupérer l'utilisateur à tester
        $userId = $this->argument('user_id') ?? 1;
        $user = User::find($userId);

        if (!$user) {
            $this->error("Utilisateur avec l'ID {$userId} non trouvé");
            return Command::FAILURE;
        }

        $this->info("👤 Test avec l'utilisateur: {$user->name} {$user->surname} ({$user->email})");
        $this->line('');

        // Test 1: Vérification des rôles Spatie
        $this->testRoles($user);

        // Test 2: Vérification des permissions de base
        $this->testBasicPermissions($user);

        // Test 3: Vérification des Gates personnalisés
        $this->testCustomGates($user);

        // Test 4: Test des policies sur des modèles
        $this->testModelPolicies($user);

        // Test 5: Test d'accès aux organisations
        $this->testOrganisationAccess($user);

        // Test 6: Test du service PolicyService
        $this->testPolicyService($user);

        $this->line('');
        $this->info('✅ Tests terminés avec succès');

        return Command::SUCCESS;
    }

    private function testRoles(User $user)
    {
        $this->info('📋 Test des rôles Spatie');
        $this->line('========================');

        $roles = $user->getRoleNames();
        $this->line("Rôles assignés: " . $roles->implode(', '));

        $this->line("Est superadmin (hasRole): " . ($user->hasRole('superadmin') ? '✅ OUI' : '❌ NON'));
        $this->line("Est superadmin (Gate): " . (Gate::forUser($user)->allows('is-superadmin') ? '✅ OUI' : '❌ NON'));

        $this->line('');
    }

    private function testBasicPermissions(User $user)
    {
        $this->info('🔐 Test des permissions de base');
        $this->line('===============================');

        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'organisations.view',
            'organisations.create',
            'records.view',
            'records.create',
            'system.settings',
            'access.admin'
        ];

        foreach ($permissions as $permission) {
            $hasPermission = $user->hasPermissionTo($permission);
            $gateAllows = Gate::forUser($user)->allows($permission);

            $status = $hasPermission ? '✅' : '❌';
            $gateStatus = $gateAllows ? '✅' : '❌';

            $this->line("{$status} {$gateStatus} {$permission}");
        }

        $this->line('');
    }

    private function testCustomGates(User $user)
    {
        $this->info('🚪 Test des Gates personnalisés');
        $this->line('===============================');

        $gates = [
            'is-superadmin',
            'manage-users',
            'manage-organisations',
            'access-admin',
        ];

        foreach ($gates as $gate) {
            $allows = Gate::forUser($user)->allows($gate);
            $status = $allows ? '✅' : '❌';
            $this->line("{$status} {$gate}");
        }

        $this->line('');
    }

    private function testModelPolicies(User $user)
    {
        $this->info('📄 Test des policies sur les modèles');
        $this->line('====================================');

        // Test User policy
        $testUser = User::first();
        if ($testUser) {
            $this->line("Test UserPolicy sur utilisateur ID {$testUser->id}:");
            $this->line("  - viewAny: " . ($user->can('viewAny', User::class) ? '✅' : '❌'));
            $this->line("  - view: " . ($user->can('view', $testUser) ? '✅' : '❌'));
            $this->line("  - create: " . ($user->can('create', User::class) ? '✅' : '❌'));
            $this->line("  - update: " . ($user->can('update', $testUser) ? '✅' : '❌'));
            $this->line("  - delete: " . ($user->can('delete', $testUser) ? '✅' : '❌'));
        }

        // Test Organisation policy
        $testOrg = Organisation::first();
        if ($testOrg) {
            $this->line("Test OrganisationPolicy sur organisation ID {$testOrg->id}:");
            $this->line("  - viewAny: " . ($user->can('viewAny', Organisation::class) ? '✅' : '❌'));
            $this->line("  - view: " . ($user->can('view', $testOrg) ? '✅' : '❌'));
            $this->line("  - create: " . ($user->can('create', Organisation::class) ? '✅' : '❌'));
            $this->line("  - update: " . ($user->can('update', $testOrg) ? '✅' : '❌'));
            $this->line("  - delete: " . ($user->can('delete', $testOrg) ? '✅' : '❌'));
        }

        $this->line('');
    }

    private function testOrganisationAccess(User $user)
    {
        $this->info('🏢 Test d\'accès aux organisations');
        $this->line('=================================');

        $this->line("Organisation courante: " . ($user->currentOrganisation ? $user->currentOrganisation->name : 'Aucune'));

        if ($user->currentOrganisation) {
            $this->line("ID organisation courante: {$user->current_organisation_id}");
        }

        // Test d'accès à différents modèles dans l'organisation
        $testUser = User::where('id', '!=', $user->id)->first();
        if ($testUser) {
            $hasAccess = Gate::forUser($user)->allows('access-in-organisation', $testUser);
            $this->line("Accès à l'utilisateur ID {$testUser->id}: " . ($hasAccess ? '✅' : '❌'));
        }

        $this->line('');
    }

    private function testPolicyService(User $user)
    {
        $this->info('⚙️ Test du PolicyService');
        $this->line('========================');

        // Test canUserPerformAction
        $actions = ['users.view', 'users.create', 'organisations.create'];

        foreach ($actions as $action) {
            $canPerform = PolicyService::canUserPerformAction($user, $action);
            $status = $canPerform ? '✅' : '❌';
            $this->line("{$status} canUserPerformAction('{$action}')");
        }

        // Test getUserPermissions
        $userPermissions = PolicyService::getUserPermissions($user);
        $this->line("Nombre total de permissions: " . count($userPermissions));

        // Test canAccessRoute
        $routes = [
            'admin.users.index',
            'admin.users.create',
            'admin.organisations.index',
            'admin.settings'
        ];

        foreach ($routes as $route) {
            $canAccess = PolicyService::canAccessRoute($user, $route);
            $status = $canAccess ? '✅' : '❌';
            $this->line("{$status} canAccessRoute('{$route}')");
        }

        $this->line('');
    }
}
