<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Slip;
use App\Models\SlipStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D04 — bordereaux (org-scopés, double organisation émetteur/bénéficiaire, R03).
 * Portage finalisé le 2026-08-04.
 */
class SlipApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['slip'];

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
        $this->getJson('/api/v1/slips')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/slips')
            ->assertStatus(403);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $slip = $this->makeSlip();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/slips/{$slip->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $slip->code);
    }

    public function test_store_cree_la_ressource_avec_l_officier_authentifie(): void
    {
        // Le contrôleur attribue le statut par défaut « Projects » comme en Blade.
        SlipStatus::create(['name' => 'Projects']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/slips', [
                'code' => 'BORD-001',
                'name' => 'Bordereau test',
                'user_organisation_id' => $this->organisation->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'BORD-001')
            ->assertJsonPath('data.officer_id', $this->user->id)
            ->assertJsonPath('data.officer_organisation_id', $this->organisation->id);

        $this->assertDatabaseHas('slips', ['id' => $response->json('data.id')]);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $slip = $this->makeSlip();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/slips/{$slip->id}", ['name' => 'Bordereau renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Bordereau renommé');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $slip = $this->makeSlip();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/slips/{$slip->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('slips', ['id' => $slip->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : un bordereau d'une autre organisation doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $slip = $this->makeSlip($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/slips/{$slip->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/slips/{$slip->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/slips/{$slip->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('slips', ['id' => $slip->id]);
    }
}
