<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContainerProperty;
use App\Models\Mail;
use App\Models\MailContainer;
use App\Models\MailTypology;
use App\Models\Activity;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — contenants de courrier (org-scopés via `creator_organisation_id`).
 * Portage finalisé le 2026-08-04.
 */
class MailContainerApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['mail_container'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeContainer(Organisation $org = null, array $extra = []): MailContainer
    {
        $org = $org ?? $this->organisation;
        $property = ContainerProperty::create(['name' => 'Propriété ' . substr(uniqid(), -6), 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        return MailContainer::create([
            'code' => 'C' . substr(uniqid(), -6),
            'name' => 'Contenant',
            'property_id' => $property->id,
            'created_by' => $this->user->id,
            'creator_organisation_id' => $org->id,
            ...$extra,
        ]);
    }

    private function makeMail(): Mail
    {
        $activity = Activity::create(['code' => 'A' . substr(uniqid(), -5), 'name' => 'Activité']);
        $typology = MailTypology::create(['code' => 'T' . substr(uniqid(), -4), 'name' => 'Typologie ' . substr(uniqid(), -4), 'activity_id' => $activity->id]);

        return Mail::create([
            'code' => 'ML' . substr(uniqid(), -6),
            'name' => 'Courrier',
            'date' => now(),
            'document_type' => 'original',
            'status' => 'in_progress',
            'mail_type' => 'incoming',
            'typology_id' => $typology->id,
            'recipient_organisation_id' => $this->organisation->id,
            'recipient_user_id' => $this->user->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/mail-containers')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/mail-containers')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_contenants_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeContainer();                    // mon org
        $this->makeContainer($orgEtrangere);       // autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mail-containers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $container = $this->makeContainer();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-containers/{$container->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $container->code);
    }

    public function test_store_cree_le_contenant_pour_l_organisation(): void
    {
        $property = ContainerProperty::create(['name' => 'P2', 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-containers', [
                'code' => 'BOX-01',
                'name' => 'Boîte 1',
                'property_id' => $property->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'BOX-01')
            ->assertJsonPath('data.creator_organisation_id', $this->organisation->id)
            ->assertJsonPath('data.created_by', $this->user->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-containers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'property_id']);
    }

    public function test_update_modifie_le_contenant(): void
    {
        $container = $this->makeContainer();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mail-containers/{$container->id}", ['name' => 'Renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renommé');
    }

    public function test_destroy_supprime_le_contenant_vide(): void
    {
        $container = $this->makeContainer();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-containers/{$container->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('mail_containers', ['id' => $container->id]);
    }

    public function test_destroy_refuse_un_contenant_avec_des_courriers(): void
    {
        $container = $this->makeContainer();
        $mail = $this->makeMail();
        $container->mails()->attach($mail->id, ['archived_by' => $this->user->id, 'document_type' => 'original']);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-containers/{$container->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('mail_containers', ['id' => $container->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : un contenant d'une autre organisation doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $container = $this->makeContainer($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-containers/{$container->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mail-containers/{$container->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-containers/{$container->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('mail_containers', ['id' => $container->id]);
    }
}
