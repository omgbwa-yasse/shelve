<?php

namespace Tests\Feature\Api\V1;

use App\Models\Activity;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — typologies de courrier (référentiel global). Portage finalisé le 2026-08-04.
 */
class MailTypologyApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['mail_typology'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeMailTypology(array $extra = []): MailTypology
    {
        $activity = Activity::create(['code' => 'A' . substr(uniqid(), -5), 'name' => 'Activité']);

        return MailTypology::create([
            'code' => substr(uniqid(), -5),
            'name' => 'Typologie ' . substr(uniqid(), -6),
            'description' => 'Description',
            'activity_id' => $activity->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/mail-typologies')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/mail-typologies')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeMailTypology();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mail-typologies')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $mailTypology = $this->makeMailTypology();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-typologies/{$mailTypology->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $mailTypology->name);
    }

    public function test_store_cree_la_ressource(): void
    {
        $activity = Activity::create(['code' => 'B' . substr(uniqid(), -5), 'name' => 'Activité B']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-typologies', [
                'code' => 'CONF',
                'name' => 'Confidentiel',
                'description' => 'Courrier confidentiel',
                'activity_id' => $activity->id,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'CONF');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-typologies', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'activity_id']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $mailTypology = $this->makeMailTypology();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mail-typologies/{$mailTypology->id}", ['description' => 'Modifié'])
            ->assertOk()
            ->assertJsonPath('data.description', 'Modifié');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $mailTypology = $this->makeMailTypology();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-typologies/{$mailTypology->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('mail_typologies', ['id' => $mailTypology->id]);
    }
}
