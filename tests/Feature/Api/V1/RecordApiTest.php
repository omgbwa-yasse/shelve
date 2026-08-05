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
 * D02 — notices (org-scopées par `organisation_id`, R03). Portage finalisé le 2026-08-05.
 */
class RecordApiTest extends TestCase
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
            'name' => 'Notice test',
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
        $this->getJson('/api/v1/records')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_notices_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeRecord();                 // dans mon org
        $this->makeRecord($orgEtrangere);    // dans une autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/records')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $record->code)
            ->assertJsonPath('data.is_container', false)
            ->assertJsonPath('data.is_current_version', true);
    }

    public function test_store_cree_la_notice_avec_org_et_creator_de_l_agent(): void
    {
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/records', [
                'code' => 'R-NEW-001',
                'name' => 'Notice créée',
                'level_id' => $level->id,
                'status_id' => $status->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Notice créée')
            ->assertJsonPath('data.organisation_id', $this->organisation->id)
            ->assertJsonPath('data.creator_id', $this->user->id)
            ->assertJsonPath('data.is_current_version', true);

        $response->assertHeader('Location', "/api/v1/records/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/records', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_modifie_la_notice(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/records/{$record->id}", ['name' => 'Notice renommée'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Notice renommée');
    }

    public function test_destroy_supprime_la_notice(): void
    {
        $record = $this->makeRecord();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('records', ['id' => $record->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une notice d'une autre organisation doit renvoyer 404
     * (jamais 403) sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $record = $this->makeRecord($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/records/{$record->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/records/{$record->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/records/{$record->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('records', ['id' => $record->id]);
    }
}
