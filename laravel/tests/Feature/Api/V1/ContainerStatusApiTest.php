<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContainerStatus;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D03 — statuts de conteneurs (référentiel global). Portage finalisé le 2026-08-04.
 */
class ContainerStatusApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['container_status'];

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
        $this->getJson('/api/v1/container-statuses')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/container-statuses')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        ContainerStatus::create(['name' => 'Occupé', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/container-statuses')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $status = ContainerStatus::create(['name' => 'Occupé', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/container-statuses/{$status->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Occupé');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/container-statuses', ['name' => 'Réservé'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Réservé')
            ->assertJsonPath('data.creator_id', $this->user->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/container-statuses', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $status = ContainerStatus::create(['name' => 'Occupé', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/container-statuses/{$status->id}", ['description' => 'En cours de consultation'])
            ->assertOk()
            ->assertJsonPath('data.description', 'En cours de consultation');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $status = ContainerStatus::create(['name' => 'Occupé', 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/container-statuses/{$status->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('container_statuses', ['id' => $status->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes données.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $status = ContainerStatus::create(['name' => 'Occupé', 'creator_id' => $this->user->id]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/container-statuses/{$status->id}")
            ->assertOk();
    }
}
