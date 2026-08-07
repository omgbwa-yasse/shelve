<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\Organisation;
use App\Models\RecordLevel;
use App\Models\RecordSupport;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipRecordAttachment;
use App\Models\SlipStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D04 — pièces jointes d'un document de bordereau (pivot `slip_record_attachments`,
 * org-scopées par héritage du slip parent, R03). Portage finalisé le 2026-08-04 : la
 * ressource est imbriquée sous `/slips/{slip}/records/{record}/attachments`, la création
 * passe exclusivement par l'upload.
 */
class SlipRecordAttachmentApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['slip_record_attachment'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeSlip(): Slip
    {
        $status = SlipStatus::first() ?? SlipStatus::create(['name' => 'Projects']);

        return Slip::create([
            'code' => 'B' . substr(uniqid(), -6),
            'name' => 'Bordereau',
            'description' => null,
            'officer_organisation_id' => $this->organisation->id,
            'officer_id' => $this->user->id,
            'user_organisation_id' => $this->organisation->id,
            'user_id' => null,
            'slip_status_id' => $status->id,
            'is_received' => false,
            'received_date' => null,
            'received_by' => null,
            'is_approved' => false,
            'approved_date' => null,
            'approved_by' => null,
            'is_integrated' => false,
            'integrated_date' => null,
            'integrated_by' => null,
        ]);
    }

    private function makeSlipRecord(Slip $slip): SlipRecord
    {
        $level = RecordLevel::first() ?? RecordLevel::create(['name' => 'Niveau test']);
        $support = RecordSupport::first() ?? RecordSupport::create(['name' => 'Support test']);
        $activity = Activity::first() ?? Activity::create([
            'code' => 'A' . substr(uniqid(), -6),
            'name' => 'Activité test',
        ]);

        return SlipRecord::create([
            'slip_id' => $slip->id,
            'code' => 'D' . substr(uniqid(), -6),
            'name' => 'Document',
            'date_format' => 'D',
            'date_start' => null,
            'date_end' => null,
            'date_exact' => null,
            'content' => null,
            'level_id' => $level->id,
            'width' => null,
            'width_description' => null,
            'support_id' => $support->id,
            'activity_id' => $activity->id,
            'creator_id' => $this->user->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/slips/1/records/1/attachments')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $slipRecord = $this->makeSlipRecord($this->makeSlip());

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/slips/{$slipRecord->slip_id}/records/{$slipRecord->id}/attachments")
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $slipRecord = $this->makeSlipRecord($this->makeSlip());

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/slips/{$slipRecord->slip_id}/records/{$slipRecord->id}/attachments")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $slipRecord = $this->makeSlipRecord($this->makeSlip());

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/slips/{$slipRecord->slip_id}/records/{$slipRecord->id}/attachments/upload", [])
            ->assertStatus(422);
    }
}
