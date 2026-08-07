<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D09 — organisations, référentiel global (hiérarchie `parent_id`).
 * Portage finalisé le 2026-08-05.
 *
 * Préfixe des permissions : `organisations_*` (voir OrganisationPolicy). La Policy
 * réserve create/delete aux superadmins : les tests correspondants utilisent un
 * agent superadmin.
 */
class OrganisationApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['organisations'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeSuperAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $admin = User::factory()->forOrganisation($this->organisation)->create();
        $admin->roles()->attach($role->id);

        return $admin;
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/organisations')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/organisations')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Organisation::factory()->create(['code' => 'ORG-B']);
        Organisation::factory()->create(['code' => 'ORG-A']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/organisations')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/organisations/{$this->organisation->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $this->organisation->name)
            ->assertJsonPath('data.code', $this->organisation->code);
    }

    public function test_store_cree_la_ressource(): void
    {
        $admin = $this->makeSuperAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/organisations', [
                'code' => 'DIR-A',
                'name' => 'Direction A',
                'description' => 'Une direction',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'DIR-A')
            ->assertJsonPath('data.name', 'Direction A')
            ->assertJsonPath('data.description', 'Une direction');

        $response->assertHeader('Location', "/api/v1/organisations/{$response->json('data.id')}");
    }

    public function test_store_est_reserve_aux_superadmins(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/organisations', ['code' => 'DIR-A', 'name' => 'Direction A'])
            ->assertStatus(403);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $admin = $this->makeSuperAdmin();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/organisations', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);

        Organisation::factory()->create(['code' => 'DUP']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/organisations', ['code' => 'DUP', 'name' => 'Doublon'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/organisations/{$this->organisation->id}", ['name' => 'Direction renommée'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Direction renommée');
    }

    public function test_update_interdit_de_se_designer_comme_parent(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/organisations/{$this->organisation->id}", ['parent_id' => $this->organisation->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $admin = $this->makeSuperAdmin();
        $org = Organisation::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/organisations/{$org->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('organisations', ['id' => $org->id]);
    }

    public function test_destroy_est_reserve_aux_superadmins(): void
    {
        $org = Organisation::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/organisations/{$org->id}")
            ->assertStatus(403);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes
     * organisations (les organisations définissent le périmètre, pas l'inverse).
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/organisations/{$this->organisation->id}")
            ->assertOk();
    }
}
