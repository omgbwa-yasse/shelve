<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\ContainerProperty;
use App\Models\Mail;
use App\Models\MailArchive;
use App\Models\MailContainer;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — archives de courrier (org-scopées via le contenant `creator_organisation_id`).
 * Portage finalisé le 2026-08-04.
 */
class MailArchiveApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['mail_archive', 'mail_container', 'mail'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeContainer(Organisation $org = null): MailContainer
    {
        $org = $org ?? $this->organisation;
        $property = ContainerProperty::create(['name' => 'Propriété ' . substr(uniqid(), -6), 'width' => 10, 'length' => 20, 'depth' => 30, 'creator_id' => $this->user->id]);

        return MailContainer::create([
            'code' => 'C' . substr(uniqid(), -6),
            'name' => 'Contenant',
            'property_id' => $property->id,
            'created_by' => $this->user->id,
            'creator_organisation_id' => $org->id,
        ]);
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

    private function makeArchive(Organisation $org = null, array $extra = []): MailArchive
    {
        $container = $this->makeContainer($org);
        $mail = $this->makeMail($org);

        return MailArchive::create([
            'container_id' => $container->id,
            'mail_id' => $mail->id,
            'archived_by' => $this->user->id,
            'document_type' => 'original',
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/mail-archives')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/mail-archives')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_archives_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeArchive();                       // contenant de mon org
        $this->makeArchive($orgEtrangere);          // contenant d'une autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mail-archives')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $archive = $this->makeArchive();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-archives/{$archive->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $archive->id);
    }

    public function test_store_cree_l_archive(): void
    {
        $container = $this->makeContainer();
        $mail = $this->makeMail();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-archives', [
                'container_id' => $container->id,
                'mail_id' => $mail->id,
                'document_type' => 'copy',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.archived_by', $this->user->id)
            ->assertJsonPath('data.document_type', 'copy');
    }

    public function test_store_refuse_un_contenant_d_une_autre_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $containerEtranger = $this->makeContainer($orgEtrangere);
        $mail = $this->makeMail();

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-archives', [
                'container_id' => $containerEtranger->id,
                'mail_id' => $mail->id,
                'document_type' => 'original',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['container_id']);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-archives', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['container_id', 'mail_id', 'document_type']);
    }

    public function test_update_modifie_l_archive(): void
    {
        $archive = $this->makeArchive();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mail-archives/{$archive->id}", ['document_type' => 'duplicate'])
            ->assertOk()
            ->assertJsonPath('data.document_type', 'duplicate');
    }

    public function test_destroy_supprime_l_archive(): void
    {
        $archive = $this->makeArchive();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-archives/{$archive->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('mail_archives', ['id' => $archive->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une archive d'une autre organisation doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $archive = $this->makeArchive($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-archives/{$archive->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mail-archives/{$archive->id}", ['document_type' => 'duplicate'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-archives/{$archive->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('mail_archives', ['id' => $archive->id]);
    }
}
