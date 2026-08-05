<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\Batch;
use App\Models\Mail;
use App\Models\MailAction;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — parapheurs (org-scopés via `organisation_holder_id`). Portage finalisé le 2026-08-04.
 */
class BatchApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['batch'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeBatch(Organisation $org = null, array $extra = []): Batch
    {
        $org = $org ?? $this->organisation;

        return Batch::create([
            'code' => 'B' . substr(uniqid(), -6),
            'name' => 'Parapheur',
            'organisation_holder_id' => $org->id,
            ...$extra,
        ]);
    }

    private function makeMail(array $extra = []): Mail
    {
        $activity = Activity::create(['code' => 'A' . substr(uniqid(), -5), 'name' => 'Activité']);
        $priority = MailPriority::create(['name' => 'P', 'duration' => 1]);
        $action = MailAction::create(['name' => 'A', 'duration' => 1, 'to_return' => false, 'description' => 'd']);
        $typology = MailTypology::create(['code' => 'T1', 'name' => 'T', 'activity_id' => $activity->id]);

        return Mail::create([
            'code' => 'ML' . substr(uniqid(), -6),
            'name' => 'Courrier',
            'date' => now(),
            'document_type' => 'original',
            'status' => 'draft',
            'mail_type' => 'incoming',
            'recipient_organisation_id' => $this->organisation->id,
            'recipient_user_id' => $this->user->id,
            'typology_id' => $typology->id,
            'priority_id' => $priority->id,
            'action_id' => $action->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/batches')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/batches')
            ->assertStatus(403);
    }

    public function test_index_retourne_uniquement_les_parapheurs_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeBatch();                 // mon org
        $this->makeBatch($orgEtrangere);    // autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/batches')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $batch = $this->makeBatch();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/batches/{$batch->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $batch->name);
    }

    public function test_store_cree_le_parapheur_pour_l_organisation(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/batches', ['code' => 'LOT-A', 'name' => 'Lot A'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Lot A')
            ->assertJsonPath('data.organisation_holder_id', $this->organisation->id);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/batches', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_modifie_le_parapheur(): void
    {
        $batch = $this->makeBatch();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/batches/{$batch->id}", ['name' => 'Renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renommé');
    }

    public function test_destroy_supprime_le_parapheur_sans_courrier(): void
    {
        $batch = $this->makeBatch();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/batches/{$batch->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
    }

    public function test_destroy_refuse_un_parapheur_contenant_des_courriers(): void
    {
        $batch = $this->makeBatch();
        $mail = $this->makeMail();
        $batch->mails()->attach($mail->id, ['insert_date' => now()]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/batches/{$batch->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('batches', ['id' => $batch->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : un parapheur d'une autre organisation doit renvoyer 404
     * (jamais 403 — un 403 confirmerait son existence), sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $batch = $this->makeBatch($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/batches/{$batch->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/batches/{$batch->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/batches/{$batch->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('batches', ['id' => $batch->id]);
    }
}
