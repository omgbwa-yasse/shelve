<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D14 — prompts (org-scopés : organisation courante, système, personnel — R03).
 * Portage finalisé le 2026-08-04.
 */
class PromptApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['prompt'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makePrompt(array $extra = []): Prompt
    {
        return Prompt::create([
            'title' => 'Prompt ' . substr(uniqid(), -6),
            'content' => 'Contenu du prompt',
            'is_system' => false,
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->user->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/prompts')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/prompts')
            ->assertStatus(403);
    }

    public function test_index_ne_renvoie_que_les_prompts_visibles(): void
    {
        $this->makePrompt();                              // mon organisation
        $this->makePrompt(['is_system' => true]);         // système
        $this->makePrompt(['organisation_id' => null]);   // personnel
        $this->makePrompt(['organisation_id' => Organisation::factory()->create()->id]); // autre org

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/prompts')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $prompt = $this->makePrompt();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/prompts/{$prompt->id}")
            ->assertOk()
            ->assertJsonPath('data.title', $prompt->title)
            ->assertJsonPath('data.is_system', false);
    }

    public function test_store_cree_le_prompt_dans_l_organisation_courante(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/prompts', [
                'title' => 'Nouveau prompt',
                'content' => 'Contenu',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Nouveau prompt')
            ->assertJsonPath('data.organisation_id', $this->organisation->id)
            ->assertJsonPath('data.user_id', $this->user->id);

        $response->assertHeader('Location', "/api/v1/prompts/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/prompts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('content');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $prompt = $this->makePrompt();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/prompts/{$prompt->id}", ['content' => 'Contenu modifié'])
            ->assertOk()
            ->assertJsonPath('data.content', 'Contenu modifié');
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $prompt = $this->makePrompt();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/prompts/{$prompt->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('prompts', ['id' => $prompt->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : un prompt d'une autre organisation (non système, non
     * personnel) doit renvoyer 404 — jamais 403 — sur show, update et destroy.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $prompt = Prompt::create([
            'title' => 'Prompt étranger',
            'content' => 'Contenu',
            'is_system' => false,
            'organisation_id' => $orgEtrangere->id,
            'user_id' => $userEtranger->id,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/prompts/{$prompt->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/prompts/{$prompt->id}", ['content' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/prompts/{$prompt->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('prompts', ['id' => $prompt->id]);
    }
}
