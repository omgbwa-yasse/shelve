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
 * D10 — Recherche de courriers (domaine d'actions, org-scopée via
 * `Mail::inOrganisation` : sender / recipient / assigned, R03). Portage finalisé le 2026-08-05.
 */
class SearchMailApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, ['mail']);
    }

    private function makeMail(Organisation $org = null, array $extra = []): Mail
    {
        $org = $org ?? $this->organisation;
        $activity = Activity::create(['code' => 'A' . substr(uniqid(), -5), 'name' => 'Activité']);
        $typology = MailTypology::create([
            'code' => 'T' . substr(uniqid(), -4),
            'name' => 'Typologie ' . substr(uniqid(), -4),
            'activity_id' => $activity->id,
        ]);

        return Mail::create([
            'code' => 'ML' . substr(uniqid(), -6),
            'name' => 'Courrier de recherche',
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

    public function test_search_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/search/mails?q=courrier')->assertStatus(401);
    }

    public function test_search_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/search/mails?q=courrier')
            ->assertStatus(403);
    }

    public function test_search_avec_q_retourne_l_enveloppe_paginee(): void
    {
        $mail = $this->makeMail($this->organisation, ['name' => 'Contrat de location unique']);
        $this->makeMail($this->organisation, ['name' => 'Autre courrier']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/search/mails?q=location')
            ->assertOk();

        $response->assertJsonStructure([
            'data' => ['*' => ['id', 'code', 'name', 'mail_type', 'recipient_organisation_id']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            'links' => ['first', 'prev', 'next', 'last'],
        ]);

        $this->assertContains(
            $mail->id,
            collect($response->json('data'))->pluck('id')->all()
        );
    }

    public function test_search_respecte_l_organisation_courante(): void
    {
        $orgB = Organisation::factory()->create();
        $this->makeMail($orgB, ['name' => 'Courrier xyzzy-secret autre organisation']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/search/mails?q=xyzzy+secret')
            ->assertOk()
            ->assertJsonPath('meta.total', 0)
            ->assertJsonCount(0, 'data');
    }
}
