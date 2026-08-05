<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D04 — documents de bordereau (org-scopés par le bordereau parent, R03).
 * Portage finalisé le 2026-08-04 : la ressource est imbriquée sous `/slips/{slip}/records`
 * (motif D03), le `Slip` parent est créé par le test.
 */
class SlipRecordApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['slip_record'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeSlip(Organisation $org = null, array $extra = []): Slip
    {
        $org = $org ?? $this->organisation;
        $status = SlipStatus::first() ?? SlipStatus::create(['name' => 'Projects']);

        return Slip::create([
            'code' => 'B' . substr(uniqid(), -6),
            'name' => 'Bordereau',
            'description' => null,
            'officer_organisation_id' => $org->id,
            'officer_id' => $this->user->id,
            'user_organisation_id' => $org->id,
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
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/slips/1/records')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $slip = $this->makeSlip();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/slips/{$slip->id}/records")
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $slip = $this->makeSlip();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/slips/{$slip->id}/records")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $slip = $this->makeSlip();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/slips/{$slip->id}/records", [])
            ->assertStatus(422);
    }
}
