<?php

namespace Tests\Feature\Api\V1;

use App\Models\Building;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D03 — bâtiments (référentiel global). Portage finalisé le 2026-08-04.
 */
class BuildingApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['building'];

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
        $this->getJson('/api/v1/buildings')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/buildings')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Building::create(['name' => 'Site principal', 'visibility' => 'public', 'creator_id' => $this->user->id]);
        Building::create(['name' => 'Annexe', 'visibility' => 'private', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/buildings')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $building = Building::create(['name' => 'Site principal', 'visibility' => 'public', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/buildings/{$building->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Site principal')
            ->assertJsonPath('data.is_public', true);
    }

    public function test_store_cree_la_ressource_avec_le_creator_authentifie(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/buildings', ['name' => 'Nouveau site', 'visibility' => 'public'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Nouveau site')
            ->assertJsonPath('data.creator_id', $this->user->id);

        $response->assertHeader('Location', "/api/v1/buildings/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/buildings', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'visibility']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/buildings', ['name' => 'X', 'visibility' => 'inconnu'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('visibility');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $building = Building::create(['name' => 'Site', 'visibility' => 'public', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/buildings/{$building->id}", ['visibility' => 'private'])
            ->assertOk()
            ->assertJsonPath('data.visibility', 'private');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $building = Building::create(['name' => 'Site', 'visibility' => 'public', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/buildings/{$building->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('buildings', ['id' => $building->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes données.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $building = Building::create(['name' => 'Site partagé', 'visibility' => 'public', 'creator_id' => $this->user->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/buildings/{$building->id}")
            ->assertOk();
    }
}
