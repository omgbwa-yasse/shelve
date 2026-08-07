<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D09 — rattachements agent→rôle (`user_roles`), pivot global. Portage finalisé
 * le 2026-08-05.
 *
 * Préfixe des permissions : `user_role_*` (voir UserRolePolicy) — le trait génère
 * `user_role_viewAny`, `user_role_view`, `user_role_create`, `user_role_update`,
 * `user_role_delete`, couverts.
 */
class UserRoleApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['user_role'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRole(string $name = 'archiviste'): Role
    {
        return Role::create(['name' => $name]);
    }

    private function makeTargetUser(): User
    {
        return User::factory()->forOrganisation(Organisation::factory()->create())->create();
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/user-roles')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user-roles')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $role = $this->makeRole();
        $target = $this->makeTargetUser();
        UserRole::create(['user_id' => $target->id, 'role_id' => $role->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user-roles')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $role = $this->makeRole();
        $target = $this->makeTargetUser();
        $userRole = UserRole::create(['user_id' => $target->id, 'role_id' => $role->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/user-roles/{$userRole->id}")
            ->assertOk()
            ->assertJsonPath('data.user_id', $target->id)
            ->assertJsonPath('data.role_id', $role->id);
    }

    public function test_store_cree_la_ressource(): void
    {
        $role = $this->makeRole();
        $target = $this->makeTargetUser();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user-roles', [
                'user_id' => $target->id,
                'role_id' => $role->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $target->id)
            ->assertJsonPath('data.role_id', $role->id);

        $response->assertHeader('Location', "/api/v1/user-roles/{$response->json('data.id')}");

        $this->assertDatabaseHas('user_roles', [
            'user_id' => $target->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user-roles', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'role_id']);
    }

    public function test_store_refuse_une_paire_dupliquee(): void
    {
        $role = $this->makeRole();
        $target = $this->makeTargetUser();
        UserRole::create(['user_id' => $target->id, 'role_id' => $role->id]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user-roles', [
                'user_id' => $target->id,
                'role_id' => $role->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('role_id');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $roleA = $this->makeRole('archiviste');
        $roleB = $this->makeRole('gestionnaire');
        $target = $this->makeTargetUser();
        $userRole = UserRole::create(['user_id' => $target->id, 'role_id' => $roleA->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/user-roles/{$userRole->id}", ['role_id' => $roleB->id])
            ->assertOk()
            ->assertJsonPath('data.role_id', $roleB->id);

        $this->assertDatabaseHas('user_roles', [
            'user_id' => $target->id,
            'role_id' => $roleB->id,
        ]);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $role = $this->makeRole();
        $target = $this->makeTargetUser();
        $userRole = UserRole::create(['user_id' => $target->id, 'role_id' => $role->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/user-roles/{$userRole->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('user_roles', ['id' => $userRole->id]);
    }
}
