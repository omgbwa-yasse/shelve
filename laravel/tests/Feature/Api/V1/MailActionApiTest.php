<?php

namespace Tests\Feature\Api\V1;

use App\Models\MailAction;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — actions de courrier (référentiel global). Portage finalisé le 2026-08-04.
 */
class MailActionApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['mail_action'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeMailAction(array $extra = []): MailAction
    {
        return MailAction::create([
            'name' => 'Action ' . substr(uniqid(), -6),
            'duration' => 3,
            'to_return' => false,
            'description' => 'Description',
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/mail-actions')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/mail-actions')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeMailAction();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mail-actions')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $mailAction = $this->makeMailAction();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-actions/{$mailAction->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $mailAction->name);
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-actions', [
                'name' => 'Archivage',
                'duration' => 5,
                'to_return' => true,
                'description' => 'Archiver puis retourner',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Archivage')
            ->assertJsonPath('data.to_return', true);
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-actions', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'duration', 'description']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $mailAction = $this->makeMailAction();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mail-actions/{$mailAction->id}", ['duration' => 9])
            ->assertOk()
            ->assertJsonPath('data.duration', 9);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $mailAction = $this->makeMailAction();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-actions/{$mailAction->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('mail_actions', ['id' => $mailAction->id]);
    }
}
