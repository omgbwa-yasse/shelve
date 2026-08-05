<?php

namespace Tests\Feature\Api\V1;

use App\Models\Building;
use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\ContainerStatus;
use App\Models\Floor;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D02 — pivot notice ↔ contenant (`record_physical_container`, org-scopée par la
 * notice parente, motif D03). Portage finalisé le 2026-08-05 : ressource imbriquée
 * sous `/records/{record}/containers`.
 */
class RecordContainerApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['record_container'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRecord(Organisation $org = null): Record
    {
        $org = $org ?? $this->organisation;
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);

        return Record::create([
            'code' => 'R' . substr(uniqid(), -8),
            'name' => 'Notice test',
            'level_id' => $level->id,
            'status_id' => $status->id,
            'access_level' => 'internal',
            'organisation_id' => $org->id,
            'creator_id' => $this->user->id,
            'version_number' => 1,
            'is_current_version' => true,
        ]);
    }

    private function makeContainer(Organisation $org): Container
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

        $shelf = Shelf::create([
            'code' => 'R' . substr(uniqid(), -6),
            'observation' => null,
            'face' => 1,
            'ear' => 1,
            'shelf' => 1,
            'shelf_length' => 1,
            'room_id' => $room->id,
            'creator_id' => $this->user->id,
        ]);

        $property = ContainerProperty::create([
            'name' => 'Propriété ' . substr(uniqid(), -6),
            'width' => 1,
            'length' => 1,
            'depth' => 1,
            'creator_id' => $this->user->id,
        ]);
        $status = ContainerStatus::create([
            'name' => 'Statut ' . substr(uniqid(), -6),
            'creator_id' => $this->user->id,
        ]);

        return Container::create([
            'code' => 'C' . substr(uniqid(), -6),
            'shelve_id' => $shelf->id,
            'status_id' => $status->id,
            'property_id' => $property->id,
            'creator_id' => $this->user->id,
            'creator_organisation_id' => $org->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/records/1/containers')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $record = $this->makeRecord();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/containers")
            ->assertStatus(403);
    }

    public function test_store_associe_un_contenant_de_l_organisation(): void
    {
        $record = $this->makeRecord();
        $container = $this->makeContainer($this->organisation);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/containers", [
                'container_id' => $container->id,
                'description' => 'Boîte test',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.record_id', $record->id)
            ->assertJsonPath('data.container_id', $container->id)
            ->assertJsonPath('data.creator_id', $this->user->id);

        $this->assertDatabaseHas('record_physical_container', [
            'record_physical_id' => $record->id,
            'container_id' => $container->id,
        ]);
    }

    public function test_store_refuse_un_contenant_hors_organisation(): void
    {
        $record = $this->makeRecord();
        $orgEtrangere = Organisation::factory()->create();
        $container = $this->makeContainer($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/containers", ['container_id' => $container->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors('container_id');
    }

    public function test_destroy_detache_le_contenant(): void
    {
        $record = $this->makeRecord();
        $container = $this->makeContainer($this->organisation);
        $record->containers()->attach($container->id, ['creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}/containers/{$container->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('record_physical_container', [
            'record_physical_id' => $record->id,
            'container_id' => $container->id,
        ]);
    }

    /**
     * ⚠️ R03 : une notice d'une autre organisation ne doit exposer ni ses contenants
     * (404 sur l'index), ni accepter une association.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $record = $this->makeRecord($orgEtrangere);
        $container = $this->makeContainer($this->organisation);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/containers")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/containers", ['container_id' => $container->id])
            ->assertStatus(404);

        $this->assertDatabaseMissing('record_physical_container', ['record_physical_id' => $record->id]);
    }
}
