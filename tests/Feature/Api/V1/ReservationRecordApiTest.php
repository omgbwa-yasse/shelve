<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Reservation;
use App\Models\ReservationRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D05 — documents de réservation (pivot `reservation_record`, org-scopés par la
 * réservation parente, R03). Portage finalisé le 2026-08-04 : la ressource est
 * imbriquée sous `/reservations/{reservation}/records` (motif D03).
 */
class ReservationRecordApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['reservation_record'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeReservation(Organisation $org = null): Reservation
    {
        $org = $org ?? $this->organisation;

        return Reservation::create([
            'code' => 'R' . substr(uniqid(), -6),
            'name' => 'Réservation',
            'content' => null,
            'operator_id' => $this->user->id,
            'operator_organisation_id' => $org->id,
            'user_id' => $this->user->id,
            'user_organisation_id' => $org->id,
            'status' => 'pending',
            'return_date' => now()->addDays(14)->format('Y-m-d'),
            'return_effective' => null,
            'communication_id' => null,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/reservations/1/records')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $reservation = $this->makeReservation();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/reservations/{$reservation->id}/records")
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $reservation = $this->makeReservation();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/reservations/{$reservation->id}/records")
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $reservation = $this->makeReservation();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/reservations/{$reservation->id}/records", [])
            ->assertStatus(422);
    }
}
