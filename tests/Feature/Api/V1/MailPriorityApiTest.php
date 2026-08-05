<?php

namespace Tests\Feature\Api\V1;

use App\Models\MailPriority;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D06 — priorités de courrier (référentiel global). Portage finalisé le 2026-08-04.
 */
class MailPriorityApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['mail_priority'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->forOrganisation(Organisation::factory()->create())->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeMailPriority(array $extra = []): MailPriority
    {
        return MailPriority::create([
            'name' => 'Priorité ' . substr(uniqid(), -6),
            'duration' => 2,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/mail-priorities')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/mail-priorities')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeMailPriority();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/mail-priorities')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'per_page', 'total']]);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $mailPriority = $this->makeMailPriority();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/mail-priorities/{$mailPriority->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $mailPriority->name);
    }

    public function test_store_cree_la_ressource(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-priorities', ['name' => 'Urgent', 'duration' => 1])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Urgent');
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/mail-priorities', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'duration']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $mailPriority = $this->makeMailPriority();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/mail-priorities/{$mailPriority->id}", ['duration' => 7])
            ->assertOk()
            ->assertJsonPath('data.duration', 7);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $mailPriority = $this->makeMailPriority();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/mail-priorities/{$mailPriority->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('mail_priorities', ['id' => $mailPriority->id]);
    }
}
