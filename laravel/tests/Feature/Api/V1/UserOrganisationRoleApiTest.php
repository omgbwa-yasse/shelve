<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D09 — rattachements agent→organisation→rôle (`user_organisation_role`).
 * Portage finalisé le 2026-08-05.
 *
 * Pivot ORG-SCOPÉ avec clé primaire composite (user_id, organisation_id), sans
 * colonne `id`. Préfixe des permissions : `user_organisation_role_*` (voir
 * UserOrganisationRolePolicy). Le cœur du test est l'isolation R03 : un pivot
 * d'une autre organisation répond 404 (jamais 403).
 */
class UserOrganisationRoleApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['user_organisation_role'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makePivot(User $target, Organisation $org, Role $role, ?User $creator = null): UserOrganisationRole
    {
        return UserOrganisationRole::create([
            'user_id' => $target->id,
            'organisation_id' => $org->id,
            'role_id' => $role->id,
            'creator_id' => $creator?->id ?? $this->user->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/user-organisation-roles')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user-organisation-roles')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_pivots_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $role = Role::create(['name' => 'archiviste']);
        $target = User::factory()->forOrganisation($this->organisation)->create();

        $this->makePivot($target, $this->organisation, $role);      // mon org
        $this->makePivot($target, $orgEtrangere, $role);            // autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user-organisation-roles')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_le_pivot(): void
    {
        $role = Role::create(['name' => 'archiviste']);
        $target = User::factory()->forOrganisation($this->organisation)->create();
        $this->makePivot($target, $this->organisation, $role);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/user-organisation-roles/{$target->id}/{$this->organisation->id}")
            ->assertOk()
            ->assertJsonPath('data.user_id', $target->id)
            ->assertJsonPath('data.organisation_id', $this->organisation->id)
            ->assertJsonPath('data.role_id', $role->id)
            ->assertJsonPath('data.creator_id', $this->user->id);
    }

    public function test_store_cree_le_pivot_avec_le_creator_authentifie(): void
    {
        $role = Role::create(['name' => 'archiviste']);
        $target = User::factory()->forOrganisation($this->organisation)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user-organisation-roles', [
                'user_id' => $target->id,
                'organisation_id' => $this->organisation->id,
                'role_id' => $role->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.role_id', $role->id)
            ->assertJsonPath('data.creator_id', $this->user->id);

        $response->assertHeader(
            'Location',
            "/api/v1/user-organisation-roles/{$target->id}/{$this->organisation->id}"
        );

        $this->assertDatabaseHas('user_organisation_role', [
            'user_id' => $target->id,
            'organisation_id' => $this->organisation->id,
            'role_id' => $role->id,
            'creator_id' => $this->user->id,
        ]);
    }

    public function test_store_refuse_une_autre_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $role = Role::create(['name' => 'archiviste']);
        $target = User::factory()->forOrganisation($this->organisation)->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user-organisation-roles', [
                'user_id' => $target->id,
                'organisation_id' => $orgEtrangere->id,
                'role_id' => $role->id,
            ])
            ->assertStatus(404);

        $this->assertDatabaseMissing('user_organisation_role', [
            'user_id' => $target->id,
            'organisation_id' => $orgEtrangere->id,
        ]);
    }

    public function test_store_refuse_un_pivot_duplique(): void
    {
        $role = Role::create(['name' => 'archiviste']);
        $target = User::factory()->forOrganisation($this->organisation)->create();
        $this->makePivot($target, $this->organisation, $role);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user-organisation-roles', [
                'user_id' => $target->id,
                'organisation_id' => $this->organisation->id,
                'role_id' => $role->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('user_id');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/user-organisation-roles', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_id', 'organisation_id', 'role_id']);
    }

    public function test_update_change_le_role(): void
    {
        $roleA = Role::create(['name' => 'archiviste']);
        $roleB = Role::create(['name' => 'gestionnaire']);
        $target = User::factory()->forOrganisation($this->organisation)->create();
        $this->makePivot($target, $this->organisation, $roleA);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/user-organisation-roles/{$target->id}/{$this->organisation->id}", ['role_id' => $roleB->id])
            ->assertOk()
            ->assertJsonPath('data.role_id', $roleB->id);

        $this->assertDatabaseHas('user_organisation_role', [
            'user_id' => $target->id,
            'organisation_id' => $this->organisation->id,
            'role_id' => $roleB->id,
        ]);
    }

    public function test_destroy_supprime_le_pivot(): void
    {
        $role = Role::create(['name' => 'archiviste']);
        $target = User::factory()->forOrganisation($this->organisation)->create();
        $this->makePivot($target, $this->organisation, $role);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/user-organisation-roles/{$target->id}/{$this->organisation->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('user_organisation_role', [
            'user_id' => $target->id,
            'organisation_id' => $this->organisation->id,
        ]);
    }

    /**
     * ⚠️ Cœur du risque R03 : un pivot d'une autre organisation doit renvoyer 404
     * (jamais 403), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $role = Role::create(['name' => 'archiviste']);
        $this->makePivot($userEtranger, $orgEtrangere, $role, $userEtranger);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/user-organisation-roles/{$userEtranger->id}/{$orgEtrangere->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/user-organisation-roles/{$userEtranger->id}/{$orgEtrangere->id}", ['role_id' => $role->id])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/user-organisation-roles/{$userEtranger->id}/{$orgEtrangere->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('user_organisation_role', [
            'user_id' => $userEtranger->id,
            'organisation_id' => $orgEtrangere->id,
        ]);
    }
}
