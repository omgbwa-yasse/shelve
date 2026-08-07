<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * R03 - les permissions effectives (`effectivePermissionNames`) et la
 * vérification (`hasPermissionToInOrganisation`) sont scopées à l'organisation
 * courante : le rôle attribué via `user_organisation_role` ne doit compter que
 * pour l'org à laquelle il est rattaché.
 */
class UserPermissionOrgScopeTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRoleWith(string $permissionName): Role
    {
        $role = Role::create(['name' => 'role-' . substr(uniqid(), -6), 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['category' => 'ai', 'description' => $permissionName . ' (test)', 'guard_name' => 'web']
        );
        $role->permissions()->syncWithoutDetaching($permission->id);

        return $role;
    }

    public function test_role_attribue_a_lorg_courante_donne_la_permission(): void
    {
        $org = Organisation::factory()->create();
        $role = $this->makeRoleWith('ai_sandbox_run');
        $user = User::factory()->forOrganisation($org)->create();

        UserOrganisationRole::create([
            'user_id' => $user->id,
            'organisation_id' => $org->id,
            'role_id' => $role->id,
            'creator_id' => $user->id,
        ]);

        $this->assertTrue($user->hasPermissionToInOrganisation('ai_sandbox_run', $org->id));
        $this->assertContains('ai_sandbox_run', $user->effectivePermissionNames($org->id));
    }

    public function test_role_dune_autre_org_ne_donne_pas_la_permission(): void
    {
        $orgA = Organisation::factory()->create();
        $orgB = Organisation::factory()->create();
        $role = $this->makeRoleWith('ai_sandbox_run');
        $user = User::factory()->forOrganisation($orgA)->create();

        // Le rôle est attribué à l'org B, pas à l'org A (courante).
        UserOrganisationRole::create([
            'user_id' => $user->id,
            'organisation_id' => $orgB->id,
            'role_id' => $role->id,
            'creator_id' => $user->id,
        ]);

        $this->assertFalse($user->hasPermissionToInOrganisation('ai_sandbox_run', $orgA->id));
        $this->assertNotContains('ai_sandbox_run', $user->effectivePermissionNames($orgA->id));
        // Mais elle est bien vue pour l'org B.
        $this->assertTrue($user->hasPermissionToInOrganisation('ai_sandbox_run', $orgB->id));
    }

    public function test_superadmin_bypasse_le_scoping(): void
    {
        $org = Organisation::factory()->create();
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $user = User::factory()->forOrganisation($org)->create();
        $user->roles()->attach($role->id);

        $this->assertTrue($user->hasPermissionToInOrganisation('ai_sandbox_run', $org->id));
        $this->assertTrue($user->hasPermissionTo('ai_sandbox_run'));
    }
}
