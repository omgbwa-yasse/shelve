<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordReactivation;
use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D02 — demandes de réactivation (org-scopées par `organisation_id`, R03).
 * Portage finalisé le 2026-08-05 : création imbriquée sous la notice, approbation et
 * rejet sur `/record-reactivations/{id}/approve` / `/reject`.
 */
class RecordReactivationApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['record_reactivation'];
    private const ABILITIES = ['viewAny', 'view', 'create', 'update', 'delete', 'approve'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS, self::ABILITIES);
        $this->grantD01Permissions($this->user, ['records']);
    }

    private function makeRecord(Organisation $org = null, RecordStatus $status = null): Record
    {
        $org = $org ?? $this->organisation;
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $status = $status ?? (RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']));

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

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/record-reactivations')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/record-reactivations')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_demandes_de_l_organisation(): void
    {
        $record = $this->makeRecord();
        RecordReactivation::create([
            'record_id' => $record->id,
            'organisation_id' => $this->organisation->id,
            'reason' => 'Demande test',
            'requested_by' => $this->user->id,
            'requested_date' => now(),
        ]);

        $orgEtrangere = Organisation::factory()->create();
        $recordEtranger = $this->makeRecord($orgEtrangere);
        RecordReactivation::create([
            'record_id' => $recordEtranger->id,
            'organisation_id' => $orgEtrangere->id,
            'reason' => 'Demande étrangère',
            'requested_by' => $this->user->id,
            'requested_date' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/record-reactivations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_store_cree_la_demande_de_reactivation(): void
    {
        $record = $this->makeRecord();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/reactivations", [
                'reason' => 'Retour au statut antérieur',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.record_id', $record->id)
            ->assertJsonPath('data.previous_status_id', $record->status_id)
            ->assertJsonPath('data.requested_by', $this->user->id)
            ->assertJsonPath('data.is_approved', false);

        $this->assertDatabaseHas('record_reactivations', [
            'id' => $response->json('data.id'),
            'organisation_id' => $this->organisation->id,
        ]);
    }

    public function test_approve_valide_et_restaure_le_statut_anterieur(): void
    {
        $statusActuel = RecordStatus::first() ?? RecordStatus::create(['name' => 'Brouillon']);
        $statusAnterieur = RecordStatus::create(['name' => 'Statut antérieur ' . substr(uniqid(), -6)]);
        $record = $this->makeRecord(status: $statusActuel);

        $reactivation = RecordReactivation::create([
            'record_id' => $record->id,
            'organisation_id' => $this->organisation->id,
            'previous_status_id' => $statusAnterieur->id,
            'reason' => 'Demande test',
            'requested_by' => $this->user->id,
            'requested_date' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/record-reactivations/{$reactivation->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.is_approved', true)
            ->assertJsonPath('data.approved_by', $this->user->id);

        $this->assertDatabaseHas('records', ['id' => $record->id, 'status_id' => $statusAnterieur->id]);
    }

    public function test_reject_enregistre_le_motif(): void
    {
        $record = $this->makeRecord();

        $reactivation = RecordReactivation::create([
            'record_id' => $record->id,
            'organisation_id' => $this->organisation->id,
            'reason' => 'Demande test',
            'requested_by' => $this->user->id,
            'requested_date' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/record-reactivations/{$reactivation->id}/reject", [
                'reason' => 'Dossier incomplet',
            ])
            ->assertOk()
            ->assertJsonPath('data.rejection_reason', 'Dossier incomplet');
    }

    /**
     * ⚠️ R03 : une demande d'une autre organisation répond 404, jamais 403.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS, self::ABILITIES);
        $this->grantD01Permissions($userEtranger, ['records']);

        $record = $this->makeRecord($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/records/{$record->id}/reactivations", ['reason' => 'Intrusion'])
            ->assertStatus(404);

        $reactivation = RecordReactivation::create([
            'record_id' => $record->id,
            'organisation_id' => $orgEtrangere->id,
            'reason' => 'Demande étrangère',
            'requested_by' => $this->user->id,
            'requested_date' => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/record-reactivations/{$reactivation->id}/approve")
            ->assertStatus(404);

        $this->assertDatabaseHas('record_reactivations', ['id' => $reactivation->id]);
    }
}
