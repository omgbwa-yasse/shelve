<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContainerProperty;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D03 — types de conteneurs (référentiel global). Portage finalisé le 2026-08-04.
 */
class ContainerPropertyApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['container_property'];

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
        $this->getJson('/api/v1/container-properties')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/container-properties')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        ContainerProperty::create(['name' => 'Boîte', 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/container-properties')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $property = ContainerProperty::create(['name' => 'Boîte', 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/container-properties/{$property->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Boîte');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/container-properties', ['name' => 'Carton', 'width' => 5, 'length' => 10, 'depth' => 15])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Carton')
            ->assertJsonPath('data.creator_id', $this->user->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/container-properties', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'width', 'length', 'depth']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $property = ContainerProperty::create(['name' => 'Boîte', 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/container-properties/{$property->id}", ['width' => 12])
            ->assertOk()
            ->assertJsonPath('data.width', 12);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $property = ContainerProperty::create(['name' => 'Boîte', 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/container-properties/{$property->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('container_properties', ['id' => $property->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes données.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $property = ContainerProperty::create(['name' => 'Boîte', 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/container-properties/{$property->id}")
            ->assertOk();
    }
}
