<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\Mail;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — courriers (org-scopés via `sender_organisation_id` / `recipient_organisation_id`
 * / `assigned_organisation_id`). Portage finalisé le 2026-08-04.
 */
class MailApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['mail'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeMail(Organisation $org = null, array $extra = []): Mail
    {
        $org = $org ?? $this->organisation;
        $activity = Activity::create(['code' => 'A' . substr(uniqid(), -5), 'name' => 'Activité']);
        $typology = MailTypology::create(['code' => 'T' . substr(uniqid(), -4), 'name' => 'Typologie ' . substr(uniqid(), -4), 'activity_id' => $activity->id]);

        return Mail::create([
            'code' => 'ML' . substr(uniqid(), -6),
            'name' => 'Courrier test',
            'date' => now(),
            'document_type' => 'original',
            'status' => 'in_progress',
            'mail_type' => 'incoming',
            'typology_id' => $typology->id,
            'recipient_organisation_id' => $org->id,
            'recipient_user_id' => $this->user->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/mails')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/mails')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_courriers_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeMail();                          // reçu par mon org
        $this->makeMail($orgEtrangere);             // reçu par une autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mails')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $mail = $this->makeMail();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mails/{$mail->id}")
            ->assertOk()
            ->assertJsonPath('data.code', $mail->code);
    }

    public function test_store_cree_un_courrier_entrant(): void
    {
        $activity = Activity::create(['code' => 'A' . substr(uniqid(), -5), 'name' => 'Activité']);
        $typology = MailTypology::create(['code' => 'T' . substr(uniqid(), -4), 'name' => 'Typologie ' . substr(uniqid(), -4), 'activity_id' => $activity->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mails', [
                'name' => 'Courrier reçu',
                'date' => now()->toDateString(),
                'document_type' => 'original',
                'typology_id' => $typology->id,
                'mail_type' => 'incoming',
                'sender_type' => 'organisation',
                'sender_organisation_id' => $this->organisation->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.mail_type', 'incoming')
            ->assertJsonPath('data.status', 'transmitted')
            ->assertJsonPath('data.recipient_organisation_id', $this->organisation->id)
            ->assertJsonPath('data.sender_organisation_id', $this->organisation->id)
            ->assertJsonPath('data.code', now()->year . '/' . $typology->code . '/0001');

        $response->assertHeader('Location', "/api/v1/mails/{$response->json('data.id')}");
    }

    public function test_store_cree_un_courrier_sortant_avec_code_fourni(): void
    {
        $activity = Activity::create(['code' => 'A' . substr(uniqid(), -5), 'name' => 'Activité']);
        $typology = MailTypology::create(['code' => 'T' . substr(uniqid(), -4), 'name' => 'Typologie ' . substr(uniqid(), -4), 'activity_id' => $activity->id]);
        $externalOrg = \App\Models\ExternalOrganization::create(['name' => 'Partenaire ' . substr(uniqid(), -6)]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mails', [
                'code' => '2026-001',
                'name' => 'Courrier envoyé',
                'date' => now()->toDateString(),
                'document_type' => 'original',
                'typology_id' => $typology->id,
                'mail_type' => 'outgoing',
                'recipient_type' => 'external_organization',
                'external_recipient_organization_id' => $externalOrg->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.mail_type', 'outgoing')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.code', '2026-001')
            ->assertJsonPath('data.sender_organisation_id', $this->organisation->id)
            ->assertJsonPath('data.external_recipient_organization_id', $externalOrg->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mails', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'date', 'document_type', 'typology_id']);
    }

    public function test_update_modifie_le_courrier(): void
    {
        $mail = $this->makeMail();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mails/{$mail->id}", ['name' => 'Courrier renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Courrier renommé');
    }

    public function test_update_valide_ses_entrees(): void
    {
        $mail = $this->makeMail();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mails/{$mail->id}", ['status' => 'statut_inconnu'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_destroy_supprime_le_courrier(): void
    {
        $mail = $this->makeMail();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mails/{$mail->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('mails', ['id' => $mail->id]);
    }

    public function test_count_unread_compte_les_courriers_entrants_non_traites(): void
    {
        $this->makeMail(null, ['status' => 'transmitted', 'processed_at' => null]);
        $this->makeMail(null, ['status' => 'completed', 'processed_at' => now()]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mails/count-unread')
            ->assertOk()
            ->assertJsonPath('count', 1);
    }

    /**
     * ⚠️ Cœur du risque R03 : un courrier d'une autre organisation doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $mail = $this->makeMail($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mails/{$mail->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mails/{$mail->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mails/{$mail->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('mails', ['id' => $mail->id]);
    }
}
