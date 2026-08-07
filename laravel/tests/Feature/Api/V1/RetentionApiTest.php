<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Retention;
use App\Models\Sort;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D07 — durées de conservation (référentiel global, motif D01). Portage finalisé le 2026-08-04.
 */
class RetentionApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['retention'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRetention(array $extra = []): Retention
    {
        $sort = Sort::create(['code' => 'C', 'name' => 'Conservation']);

        return Retention::create([
            'code' => 'R' . substr(uniqid(), -6),
            'name' => 'Durée',
            'duration' => 30,
            'sort_id' => $sort->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/retentions')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/retentions')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeRetention();
        $this->makeRetention();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/retentions')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $retention = $this->makeRetention();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/retentions/{$retention->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $retention->code)
            ->assertJsonPath('data.duration', 30);
    }

    public function test_store_cree_la_ressource(): void
    {
        $sort = Sort::create(['code' => 'E', 'name' => 'Élimination']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/retentions', [
                'code' => 'R-A',
                'name' => 'Durée A',
                'duration' => 10,
                'sort_id' => $sort->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'R-A')
            ->assertJsonPath('data.sort_id', $sort->id);

        $response->assertHeader('Location', "/api/v1/retentions/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/retentions', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'duration', 'sort_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $retention = $this->makeRetention();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/retentions/{$retention->id}", ['duration' => 50])
            ->assertOk()
            ->assertJsonPath('data.duration', 50);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $retention = $this->makeRetention();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/retentions/{$retention->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('retentions', ['id' => $retention->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes données.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $retention = $this->makeRetention();

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/retentions/{$retention->id}")
            ->assertOk();
    }
}
