<?php

namespace Tests\Feature\Api\V1;

use App\Models\Communicability;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — communicabilités (référentiel). Portage finalisé le 2026-08-04.
 */
class CommunicabilityApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['communicability'];

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
        $this->getJson('/api/v1/communicabilities')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/communicabilities')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Communicability::create(['code' => 'C1', 'name' => 'Libre', 'duration' => 0]);
        Communicability::create(['code' => 'C2', 'name' => 'Différé', 'duration' => 25]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/communicabilities')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_refuse_un_filtre_inconnu(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/communicabilities?filter[nom]=Libre')
            ->assertStatus(400);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $item = Communicability::create(['code' => 'C1', 'name' => 'Libre', 'duration' => 0]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/communicabilities/{$item->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'C1');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/communicabilities', ['code' => 'C3', 'name' => 'Restreinte', 'duration' => 50])
            ->assertStatus(201)
            ->assertJsonPath('data.duration', 50);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/communicabilities', ['code' => 'X'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'duration']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $item = Communicability::create(['code' => 'C1', 'name' => 'Libre', 'duration' => 0]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/communicabilities/{$item->id}", ['duration' => 30])
            ->assertOk()
            ->assertJsonPath('data.duration', 30);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $item = Communicability::create(['code' => 'C1', 'name' => 'Libre', 'duration' => 0]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/communicabilities/{$item->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('communicabilities', ['id' => $item->id]);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $item = Communicability::create(['code' => 'C1', 'name' => 'Libre', 'duration' => 0]);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/communicabilities/{$item->id}")
            ->assertOk();
    }
}
