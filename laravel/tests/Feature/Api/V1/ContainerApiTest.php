<?php

namespace Tests\Feature\Api\V1;

use App\Models\Building;
use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\ContainerStatus;
use App\Models\Floor;
use App\Models\Organisation;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D03 — conteneurs (org-scopés via rayonnage/salle et `creator_organisation_id`, R03).
 * Portage finalisé le 2026-08-04.
 */
class ContainerApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['container', 'container_status', 'container_property'];

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
            'face' => 1,
            'ear' => 1,
            'shelf' => 1,
            'shelf_length' => 50,
            'room_id' => ($room ?? $this->makeRoom())->id,
            'creator_id' => $this->user->id,
        ]);
    }

    private function makeStatus(): ContainerStatus
    {
        return ContainerStatus::create(['name' => 'Status ' . uniqid(), 'creator_id' => $this->user->id]);
    }

    private function makeProperty(): ContainerProperty
    {
        return ContainerProperty::create(['name' => 'Boîte ' . uniqid(), 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);
    }

    private function makeContainer(Shelf $shelf = null): Container
    {
        return Container::create([
            'code' => 'CONT-' . uniqid(),
            'shelve_id' => ($shelf ?? $this->makeShelf())->id,
            'status_id' => $this->makeStatus()->id,
            'property_id' => $this->makeProperty()->id,
            'creator_id' => $this->user->id,
            'creator_organisation_id' => $this->organisation->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/containers')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/containers')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_conteneurs_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeContainer();                                  // dans mon org
        $this->makeContainer($this->makeShelf($this->makeRoom($orgEtrangere))); // autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/containers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_filtre_par_rayonnage(): void
    {
        $shelf = $this->makeShelf();
        $this->makeContainer($shelf);
        $this->makeContainer($this->makeShelf());

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/containers?shelf_id={$shelf->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_show_retourne_la_ressource(): void
    {
        $container = $this->makeContainer();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/containers/{$container->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $container->code)
            ->assertJsonPath('data.is_archived', false);
    }

    public function test_store_cree_la_ressource_avec_les_champs_du_creator(): void
    {
        $shelf = $this->makeShelf();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/containers', [
                'code' => 'CONT-A',
                'shelve_id' => $shelf->id,
                'status_id' => $this->makeStatus()->id,
                'property_id' => $this->makeProperty()->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'CONT-A')
            ->assertJsonPath('data.creator_id', $this->user->id)
            ->assertJsonPath('data.creator_organisation_id', $this->organisation->id);
    }

    public function test_store_refuse_un_rayonnage_d_une_autre_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $shelf = $this->makeShelf($this->makeRoom($orgEtrangere));

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/containers', [
                'code' => 'CONT-X',
                'shelve_id' => $shelf->id,
                'status_id' => $this->makeStatus()->id,
                'property_id' => $this->makeProperty()->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('shelve_id');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/containers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'shelve_id', 'status_id', 'property_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $container = $this->makeContainer();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/containers/{$container->id}", ['is_archived' => true])
            ->assertOk()
            ->assertJsonPath('data.is_archived', true);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $container = $this->makeContainer();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/containers/{$container->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('containers', ['id' => $container->id]);
    }

    /**
     * ⚠️ R03 : un conteneur d'une autre organisation répond 404.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $container = $this->makeContainer($this->makeShelf($this->makeRoom($orgEtrangere)));

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/containers/{$container->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/containers/{$container->id}", ['code' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/containers/{$container->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('containers', ['id' => $container->id]);
    }
}

