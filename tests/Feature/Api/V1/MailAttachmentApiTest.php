<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\Mail;
use App\Models\MailAttachment;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — pièces jointes de courrier (org-scopées via les courriers rattachés).
 * Portage finalisé le 2026-08-04.
 */
class MailAttachmentApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['mail_attachment', 'mail'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeMail(Organisation $org = null): Mail
    {
        $org = $org ?? $this->organisation;
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
            'recipient_organisation_id' => $org->id,
            'recipient_user_id' => $this->user->id,
        ]);
    }

    private function makeAttachment(Organisation $org = null): MailAttachment
    {
        $mail = $this->makeMail($org);

        $attachment = MailAttachment::create([
            'path' => 'mail_attachments/fiche_' . substr(uniqid(), -6) . '.pdf',
            'name' => 'fiche.pdf',
            'crypt' => md5(uniqid()),
            'crypt_sha512' => hash('sha512', uniqid()),
            'size' => 1024,
            'type' => 'mail',
            'creator_id' => $this->user->id,
        ]);

        $mail->attachments()->attach($attachment->id, ['added_by' => $this->user->id]);

        return $attachment;
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/mail-attachments')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/mail-attachments')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_pieces_jointes_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeAttachment();                // courrier de mon org
        $this->makeAttachment($orgEtrangere);   // courrier d'une autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mail-attachments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_index_filtre_par_mail_id(): void
    {
        $mailA = $this->makeMail();
        $mailB = $this->makeMail();

        $attachment = MailAttachment::create([
            'path' => 'mail_attachments/a_' . substr(uniqid(), -6) . '.pdf',
            'name' => 'a.pdf',
            'crypt' => md5(uniqid()),
            'crypt_sha512' => hash('sha512', uniqid()),
            'size' => 100,
            'type' => 'mail',
            'creator_id' => $this->user->id,
        ]);
        $mailA->attachments()->attach($attachment->id, ['added_by' => $this->user->id]);
        $mailB->attachments()->attach($attachment->id, ['added_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-attachments?filter[mail_id]={$mailA->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $attachment->id);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $attachment = $this->makeAttachment();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-attachments/{$attachment->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $attachment->name);
    }

    public function test_store_n_est_pas_expose(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-attachments', ['name' => 'x'])
            ->assertStatus(501);
    }

    public function test_destroy_supprime_la_piece_jointe(): void
    {
        $mail = $this->makeMail();
        $attachment = $this->makeAttachment();
        $mail->attachments()->attach($attachment->id, ['added_by' => $this->user->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-attachments/{$attachment->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        $this->assertDatabaseMissing('mail_attachment', ['attachment_id' => $attachment->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une pièce jointe d'une autre organisation doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $attachment = $this->makeAttachment($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-attachments/{$attachment->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-attachments/{$attachment->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    }
}
