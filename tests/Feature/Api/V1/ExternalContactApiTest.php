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
 * D01 — contacts externes (référentiel). Portage finalisé le 2026-08-04.
 */
class ExternalContactApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['external_contact'];

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
        $this->getJson('/api/v1/external-contacts')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/external-contacts')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        ExternalContact::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);
        ExternalContact::create(['first_name' => 'Marie', 'last_name' => 'Martin']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/external-contacts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $contact = ExternalContact::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/external-contacts/{$contact->id}")
            ->assertOk()
            ->assertJsonPath('data.full_name', 'Jean Dupont');
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/external-contacts', ['first_name' => 'Jean', 'last_name' => 'Dupont'])
            ->assertStatus(201)
            ->assertJsonPath('data.last_name', 'Dupont');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/external-contacts', ['email' => 'pas-un-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $contact = ExternalContact::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/external-contacts/{$contact->id}", ['position' => 'Directeur'])
            ->assertOk()
            ->assertJsonPath('data.position', 'Directeur');
    }

    public function test_un_contact_principal_retire_le_statut_aux_autres(): void
    {
        $org = ExternalOrganization::create(['name' => 'Entreprise X']);
        $a = ExternalContact::create(['first_name' => 'A', 'last_name' => 'Un', 'external_organization_id' => $org->id, 'is_primary_contact' => true]);
        $b = ExternalContact::create(['first_name' => 'B', 'last_name' => 'Deux', 'external_organization_id' => $org->id, 'is_primary_contact' => true]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/external-contacts/{$a->id}", ['is_primary_contact' => true])
            ->assertOk();

        $this->assertTrue((bool) $a->fresh()->is_primary_contact);
        $this->assertFalse((bool) $b->fresh()->is_primary_contact);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $contact = ExternalContact::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/external-contacts/{$contact->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('external_contacts', ['id' => $contact->id]);
    }

    /**
     * Référentiel global : accessible par un agent d'une autre organisation.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $contact = ExternalContact::create(['first_name' => 'Jean', 'last_name' => 'Dupont']);

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/external-contacts/{$contact->id}")
            ->assertOk();
    }
}
