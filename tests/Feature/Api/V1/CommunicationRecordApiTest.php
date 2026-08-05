<?php

namespace Tests\Feature\Api\V1;

use App\Models\Communication;
use App\Models\CommunicationRecord;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D05 — documents de communication (pivot `communication_record`, org-scopés par la
 * communication parente, R03). Portage finalisé le 2026-08-04 : la ressource est
 * imbriquée sous `/communications/{communication}/records` (motif D03).
 */
class CommunicationRecordApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['communication_record'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeCommunication(Organisation $org = null): Communication
    {
        $org = $org ?? $this->organisation;

        return Communication::create([
            'code' => 'C' . substr(uniqid(), -6),
            'name' => 'Communication',
            'content' => null,
            'operator_id' => $this->user->id,
            'operator_organisation_id' => $org->id,
            'user_id' => $this->user->id,
            'user_organisation_id' => $org->id,
            'return_date' => now()->addDays(14)->format('Y-m-d'),
            'return_effective' => null,
            'status' => 'pending',
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/communications/1/records')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $communication = $this->makeCommunication();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/communications/{$communication->id}/records")
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $communication = $this->makeCommunication();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/communications/{$communication->id}/records")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $communication = $this->makeCommunication();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/communications/{$communication->id}/records", [])
            ->assertStatus(422);
    }
}
