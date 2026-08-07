<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\Organisation;
use App\Models\Retention;
use App\Models\RetentionActivity;
use App\Models\Sort;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D07 — liaison activité ↔ durée de conservation (pivot `retention_activity`).
 * La Policy réutilise les permissions `retention_*`. Portage finalisé le 2026-08-04.
 */
class RetentionActivityApiTest extends TestCase
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

    private function makeRetention(): Retention
    {
        $sort = Sort::create(['code' => 'C', 'name' => 'Conservation']);

        return Retention::create([
            'code' => 'R' . substr(uniqid(), -6),
            'name' => 'Durée',
            'duration' => 30,
            'sort_id' => $sort->id,
        ]);
    }

    private function makeActivity(): Activity
    {
        return Activity::create(['code' => 'ACT-' . substr(uniqid(), -6), 'name' => 'Activité ' . substr(uniqid(), -6)]);
    }

    private function makePivot(): RetentionActivity
    {
        $retention = $this->makeRetention();
        $activity = $this->makeActivity();

        return RetentionActivity::create([
            'retention_id' => $retention->id,
            'activity_id' => $activity->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/retention-activities')->assertStatus(401);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makePivot();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/retention-activities')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_cree_la_liaison(): void
    {
        $retention = $this->makeRetention();
        $activity = $this->makeActivity();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/retention-activities', [
                'retention_id' => $retention->id,
                'activity_id' => $activity->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.activity_id', $activity->id);

        $this->assertDatabaseHas('retention_activity', [
            'retention_id' => $retention->id,
            'activity_id' => $activity->id,
        ]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/retention-activities', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['retention_id', 'activity_id']);
    }

    public function test_destroy_retire_la_liaison(): void
    {
        $pivot = $this->makePivot();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/retention-activities/{$pivot->retention_id}/{$pivot->activity_id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('retention_activity', [
            'retention_id' => $pivot->retention_id,
            'activity_id' => $pivot->activity_id,
        ]);
    }

    public function test_destroy_dune_paire_inconnue_est_404(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/v1/retention-activities/99999/99999')
            ->assertStatus(404);
    }
}
