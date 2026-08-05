<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D09 — rôles (`roles`), référentiel global. Portage finalisé le 2026-08-05.
 *
 * Préfixe des permissions : `role_*` (voir RolePolicy) — le trait génère
 * `role_viewAny`, `role_view`, `role_create`, `role_update`, `role_delete`, couverts.
 */
class RoleApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['role'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/roles')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/roles')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Role::create(['name' => 'archiviste']);
        Role::create(['name' => 'gestionnaire']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $role = Role::create(['name' => 'archiviste', 'description' => 'Gère les fonds']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/roles/{$role->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'archiviste')
            ->assertJsonPath('data.guard_name', 'web');
    }

    public function test_store_cree_la_ressource(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/roles', [
                'name' => 'inspecteur',
                'description' => 'Inspection des fonds',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'inspecteur')
            // `guard_name` a un défaut en base ('web') : pas besoin de le fournir.
            ->assertJsonPath('data.guard_name', 'web');

        $response->assertHeader('Location', "/api/v1/roles/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/roles', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        Role::create(['name' => 'archiviste']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/roles', ['name' => 'archiviste'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $role = Role::create(['name' => 'archiviste']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/roles/{$role->id}", ['description' => 'Description modifiée'])
            ->assertOk()
            ->assertJsonPath('data.description', 'Description modifiée');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $role = Role::create(['name' => 'archiviste']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/roles/{$role->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
