<?php

namespace Tests\Feature\Api\V1;

use App\Models\Communication;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D05 — communications (org-scopées, double organisation opérateur/bénéficiaire, R03).
 * Portage finalisé le 2026-08-04.
 */
class CommunicationApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['communication'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeCommunication(Organisation $org = null, array $extra = []): Communication
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
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/communications')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/communications')
            ->assertStatus(403);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $communication = $this->makeCommunication();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/communications/{$communication->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $communication->name)
            ->assertJsonPath('data.is_pending', true);
    }

    public function test_store_cree_la_ressource_avec_l_operateur_authentifie(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/communications', [
                'name' => 'Communication test',
                'user_id' => $this->user->id,
                'user_organisation_id' => $this->organisation->id,
                'return_date' => now()->addDays(14)->format('Y-m-d'),
                'status' => 'pending',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Communication test')
            ->assertJsonPath('data.operator_id', $this->user->id)
            ->assertJsonPath('data.operator_organisation_id', $this->organisation->id)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('communications', ['id' => $response->json('data.id')]);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $communication = $this->makeCommunication();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/communications/{$communication->id}", ['name' => 'Communication renommée'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Communication renommée');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $communication = $this->makeCommunication();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/communications/{$communication->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('communications', ['id' => $communication->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une communication d'une autre organisation doit renvoyer
     * 404 (jamais 403), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $communication = $this->makeCommunication($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/communications/{$communication->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/communications/{$communication->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/communications/{$communication->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('communications', ['id' => $communication->id]);
    }
}
