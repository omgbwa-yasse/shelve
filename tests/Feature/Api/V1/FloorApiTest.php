<?php

namespace Tests\Feature\Api\V1;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D03 — étages (référentiel global). Portage finalisé le 2026-08-04.
 */
class FloorApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['floor'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeBuilding(): Building
    {
        return Building::create(['name' => 'Site', 'visibility' => 'public', 'creator_id' => $this->user->id]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/floors')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/floors')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Floor::create(['name' => 'RDC', 'building_id' => $this->makeBuilding()->id, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/floors')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $floor = Floor::create(['name' => 'RDC', 'building_id' => $this->makeBuilding()->id, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/floors/{$floor->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'RDC');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/floors', ['name' => '1er étage', 'building_id' => $this->makeBuilding()->id])
            ->assertStatus(201)
            ->assertJsonPath('data.name', '1er étage')
            ->assertJsonPath('data.creator_id', $this->user->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/floors', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'building_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $floor = Floor::create(['name' => 'RDC', 'building_id' => $this->makeBuilding()->id, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/floors/{$floor->id}", ['name' => 'Niveau 0'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Niveau 0');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $floor = Floor::create(['name' => 'RDC', 'building_id' => $this->makeBuilding()->id, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/floors/{$floor->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('floors', ['id' => $floor->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes données.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $floor = Floor::create(['name' => 'RDC', 'building_id' => $this->makeBuilding()->id, 'creator_id' => $this->user->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/floors/{$floor->id}")
            ->assertOk();
    }
}
