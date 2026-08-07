<?php

namespace Tests\Feature\Api\V1;

use App\Models\ExternalContact;
use App\Models\ExternalOrganization;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D01 — organisations externes (référentiel). Portage finalisé le 2026-08-04.
 */
class ExternalOrganizationApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['external_organization'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/external-organizations')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/external-organizations')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        ExternalOrganization::create(['name' => 'Entreprise A']);
        ExternalOrganization::create(['name' => 'Entreprise B']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/external-organizations')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $org = ExternalOrganization::create(['name' => 'Entreprise A']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/external-organizations/{$org->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Entreprise A');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/external-organizations', ['name' => 'Entreprise C'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Entreprise C');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/external-organizations', ['email' => 'pas-un-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $org = ExternalOrganization::create(['name' => 'Entreprise A']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/external-organizations/{$org->id}", ['city' => 'Lyon'])
            ->assertOk()
            ->assertJsonPath('data.city', 'Lyon');
    }

    public function test_destroy_dissocie_les_contacts_avant_suppression(): void
    {
        $org = ExternalOrganization::create(['name' => 'Entreprise A']);
        $contact = ExternalContact::create(['first_name' => 'Jean', 'last_name' => 'Dupont', 'external_organization_id' => $org->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/external-organizations/{$org->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('external_organizations', ['id' => $org->id]);
        $this->assertNull($contact->fresh()->external_organization_id);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $org = ExternalOrganization::create(['name' => 'Entreprise A']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/external-organizations/{$org->id}")
            ->assertOk();
    }
}
