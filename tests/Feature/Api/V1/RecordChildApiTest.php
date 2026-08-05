<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D02 — notices filles (org-scopées par la notice parente, motif D03).
 * Portage finalisé le 2026-08-05 : ressource imbriquée sous `/records/{record}/children`.
 */
class RecordChildApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['records'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeRecord(Organisation $org = null, array $extra = []): Record
    {
        $org = $org ?? $this->organisation;
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);

        return Record::create([
            'code' => 'R' . substr(uniqid(), -8),
            'name' => 'Notice parente',
            'level_id' => $level->id,
            'status_id' => $status->id,
            'access_level' => 'internal',
            'organisation_id' => $org->id,
            'creator_id' => $this->user->id,
            'version_number' => 1,
            'is_current_version' => true,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/records/1/children')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $record = $this->makeRecord();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/children")
            ->assertStatus(403);
    }

    public function test_index_retourne_les_enfants_de_la_notice(): void
    {
        $record = $this->makeRecord();
        $this->makeRecord(extra: ['parent_id' => $record->id, 'name' => 'Enfant 1']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/children")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.parent_id', $record->id);
    }

    public function test_store_cree_l_enfant_rattache_a_la_notice_parente(): void
    {
        $record = $this->makeRecord();
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/children", [
                'code' => 'CH-' . substr(uniqid(), -6),
                'name' => 'Enfant créé',
                'level_id' => $level->id,
                'status_id' => $status->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.parent_id', $record->id)
            ->assertJsonPath('data.organisation_id', $this->organisation->id)
            ->assertJsonPath('data.creator_id', $this->user->id);

        $response->assertHeader('Location', "/api/v1/records/{$record->id}/children/{$response->json('data.id')}");
    }

    public function test_update_modifie_l_enfant(): void
    {
        $record = $this->makeRecord();
        $child = $this->makeRecord(extra: ['parent_id' => $record->id, 'name' => 'Enfant']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/records/{$record->id}/children/{$child->id}", ['name' => 'Enfant renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Enfant renommé');
    }

    public function test_destroy_supprime_l_enfant(): void
    {
        $record = $this->makeRecord();
        $child = $this->makeRecord(extra: ['parent_id' => $record->id, 'name' => 'Enfant']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}/children/{$child->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('records', ['id' => $child->id]);
    }

    /**
     * ⚠️ R03 : une notice d'une autre organisation ne doit exposer ni ses enfants
     * (404 sur l'index), ni un enfant hors périmètre.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $record = $this->makeRecord($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}/children")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/children", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->assertDatabaseHas('records', ['id' => $record->id]);
    }
}
