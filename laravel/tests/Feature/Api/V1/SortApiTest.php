<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Sort;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — sorts finaux (référentiel). Portage finalisé le 2026-08-04.
 */
class SortApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['sort'];

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
        $this->getJson('/api/v1/sorts')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/sorts')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        Sort::create(['code' => 'E', 'name' => 'Élimination']);
        Sort::create(['code' => 'C', 'name' => 'Conservation']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/sorts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $sort = Sort::create(['code' => 'E', 'name' => 'Élimination']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/sorts/{$sort->id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'E');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/sorts', ['code' => 'T', 'name' => 'Tri'])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'T');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/sorts', ['code' => 'X', 'name' => 'Invalide'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $sort = Sort::create(['code' => 'E', 'name' => 'Élimination']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/sorts/{$sort->id}", ['name' => 'Élimination différée'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Élimination différée');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $sort = Sort::create(['code' => 'E', 'name' => 'Élimination']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/sorts/{$sort->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('sorts', ['id' => $sort->id]);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $sort = Sort::create(['code' => 'E', 'name' => 'Élimination']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/sorts/{$sort->id}")
            ->assertOk();
    }
}
