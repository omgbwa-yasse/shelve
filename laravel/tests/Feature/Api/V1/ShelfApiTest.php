<?php

namespace Tests\Feature\Api\V1;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Organisation;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D03 — rayonnages (org-scopés via leur salle, R03). Portage finalisé le 2026-08-04.
 */
class ShelfApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['shelf'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRoom(Organisation $org = null): Room
    {
        $org = $org ?? $this->organisation;
        $building = Building::create(['name' => 'Site', 'visibility' => 'public', 'creator_id' => $this->user->id]);
        $floor = Floor::create(['name' => 'RDC', 'building_id' => $building->id, 'creator_id' => $this->user->id]);

        $room = Room::create([
            'code' => 'S' . substr(uniqid(), -6),
            'name' => 'Salle',
            'visibility' => 'public',
            'type' => 'archives',
            'floor_id' => $floor->id,
            'creator_id' => $this->user->id,
        ]);
        $room->organisations()->attach($org->id);

        return $room;
    }

    private function makeShelf(Room $room = null): Shelf
    {
        return Shelf::create([
            'code' => 'RAY-' . uniqid(),
            'face' => 2,
            'ear' => 2,
            'shelf' => 3,
            'shelf_length' => 100,
            'room_id' => ($room ?? $this->makeRoom())->id,
            'creator_id' => $this->user->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/shelves')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/shelves')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_rayonnages_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeShelf();                       // dans mon org
        $this->makeShelf($this->makeRoom($orgEtrangere)); // autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/shelves')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_les_champs_calcules(): void
    {
        $shelf = $this->makeShelf();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/shelves/{$shelf->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $shelf->code)
            ->assertJsonPath('data.total_capacity', 12)
            ->assertJsonStructure(['data' => ['occupied_spots', 'available_spots', 'occupancy_percentage', 'volumetry_ml']]);
    }

    public function test_store_cree_la_ressource(): void
    {
        $room = $this->makeRoom();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/shelves', [
                'code' => 'RAY-A',
                'face' => 1,
                'ear' => 1,
                'shelf' => 1,
                'shelf_length' => 50,
                'room_id' => $room->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'RAY-A')
            ->assertJsonPath('data.creator_id', $this->user->id);
    }

    public function test_store_refuse_une_salle_d_une_autre_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $room = $this->makeRoom($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/shelves', [
                'code' => 'RAY-X',
                'face' => 1,
                'ear' => 1,
                'shelf' => 1,
                'shelf_length' => 50,
                'room_id' => $room->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('room_id');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/shelves', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'face', 'ear', 'shelf', 'shelf_length', 'room_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $shelf = $this->makeShelf();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/shelves/{$shelf->id}", ['face' => 3])
            ->assertOk()
            ->assertJsonPath('data.face', 3);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $shelf = $this->makeShelf();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/shelves/{$shelf->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('shelves', ['id' => $shelf->id]);
    }

    /**
     * ⚠️ R03 : un rayonnage d'une autre organisation répond 404.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $shelf = $this->makeShelf($this->makeRoom($orgEtrangere));

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/shelves/{$shelf->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/shelves/{$shelf->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/shelves/{$shelf->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('shelves', ['id' => $shelf->id]);
    }
}

