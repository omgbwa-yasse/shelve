<?php

namespace Tests\Feature\Api\V1;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Organisation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D03 — salles (org-scopées via `organisation_room`, R03). Portage finalisé le 2026-08-04.
 */
class RoomApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['room'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRoom(Organisation $org = null, array $extra = []): Room
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
            ...$extra,
        ]);

        $room->organisations()->attach($org->id);

        return $room;
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/rooms')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rooms')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_salles_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeRoom();                       // dans mon org
        $this->makeRoom($orgEtrangere);          // dans une autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/rooms')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $room = $this->makeRoom();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/rooms/{$room->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $room->code);
    }

    public function test_store_cree_la_ressource_et_la_rattache_a_l_organisation(): void
    {
        $building = Building::create(['name' => 'Site', 'visibility' => 'public', 'creator_id' => $this->user->id]);
        $floor = Floor::create(['name' => 'RDC', 'building_id' => $building->id, 'creator_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/rooms', [
                'code' => 'SAL-A',
                'name' => 'Salle A',
                'visibility' => 'public',
                'type' => 'archives',
                'floor_id' => $floor->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'SAL-A')
            ->assertJsonPath('data.creator_id', $this->user->id);

        $roomId = $response->json('data.id');
        $this->assertDatabaseHas('organisation_room', [
            'room_id' => $roomId,
            'organisation_id' => $this->organisation->id,
        ]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/rooms', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'visibility', 'type', 'floor_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $room = $this->makeRoom();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/rooms/{$room->id}", ['name' => 'Salle renommée'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Salle renommée');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $room = $this->makeRoom();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/rooms/{$room->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une salle d'une autre organisation doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $room = $this->makeRoom($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/rooms/{$room->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/rooms/{$room->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/rooms/{$room->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }
}

