<?php

namespace Tests\Feature\Api\V1;

use App\Models\AiSkill;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D14 — compétences IA (référentiel global). Portage finalisé le 2026-08-04.
 */
class AiSkillApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['ai_skill'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeSkill(array $extra = []): AiSkill
    {
        return AiSkill::create([
            'slug' => 'skill-' . substr(uniqid(), -6),
            'name' => 'Skill de test',
            'description' => 'Description',
            'location' => 'custom',
            'enabled' => true,
            'installed_by' => $this->user->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/ai-skills')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/ai-skills')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeSkill();
        $this->makeSkill();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/ai-skills')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $skill = $this->makeSkill();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/ai-skills/{$skill->id}")
            ->assertOk()
            ->assertJsonPath('data.slug', $skill->slug)
            ->assertJsonPath('data.enabled', true);
    }

    public function test_store_cree_la_ressource_avec_l_installateur_authentifie(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai-skills', [
                'slug' => 'my-custom-skill',
                'name' => 'Mon skill',
                'location' => 'custom',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.slug', 'my-custom-skill')
            ->assertJsonPath('data.installed_by', $this->user->id);

        $response->assertHeader('Location', "/api/v1/ai-skills/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai-skills', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['slug', 'name']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/ai-skills', ['slug' => 'x', 'name' => 'x', 'location' => 'inconnu'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('location');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $skill = $this->makeSkill();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/ai-skills/{$skill->id}", ['enabled' => false])
            ->assertOk()
            ->assertJsonPath('data.enabled', false);
    }

    public function test_toggle_inverse_le_flag_enabled(): void
    {
        $skill = $this->makeSkill();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ai-skills/{$skill->id}/toggle")
            ->assertOk()
            ->assertJsonPath('data.enabled', false);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/ai-skills/{$skill->id}/toggle")
            ->assertOk()
            ->assertJsonPath('data.enabled', true);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $skill = $this->makeSkill();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/ai-skills/{$skill->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('ai_skills', ['id' => $skill->id]);
    }

    /**
     * Référentiel global : un agent d'une autre organisation lit les mêmes données.
     */
    public function test_referentiel_partage_entre_organisations(): void
    {
        $orgB = Organisation::factory()->create();
        $userB = User::factory()->forOrganisation($orgB)->create();
        $this->grantD01Permissions($userB, self::PERMISSIONS);

        $skill = $this->makeSkill();

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/v1/ai-skills/{$skill->id}")
            ->assertOk();
    }
}
