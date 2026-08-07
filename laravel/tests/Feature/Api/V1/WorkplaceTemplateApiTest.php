<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use App\Models\WorkplaceTemplate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D12 — modèles d'espace de travail (référentiel global). Portage finalisé le 2026-08-04.
 */
class WorkplaceTemplateApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workplace_template'];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeTemplate(array $extra = []): WorkplaceTemplate
    {
        return WorkplaceTemplate::create([
            'code' => 'TPL-' . strtoupper(uniqid()),
            'name' => 'Modèle',
            'is_active' => true,
            'is_system' => false,
            'created_by' => $this->user->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/workplace-templates')->assertStatus(401);
    }

    public function test_index_sans_permission_est_refuse(): void
    {
        $user = User::factory()->forOrganisation(Organisation::factory()->create())->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/workplace-templates')
            ->assertStatus(403);
    }

    public function test_index_retourne_une_collection_paginee(): void
    {
        $this->makeTemplate();
        $this->makeTemplate();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workplace-templates')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $template = $this->makeTemplate();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workplace-templates/{$template->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Modèle');
    }

    public function test_store_cree_la_ressource(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplace-templates', [
                'name' => 'Nouveau modèle',
                'default_structure' => json_encode(['folders' => ['Archives', 'Courrier']]),
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Nouveau modèle')
            ->assertJsonPath('data.is_system', false);

        $response->assertHeader('Location', "/api/v1/workplace-templates/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workplace-templates', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_update_modifie_la_ressource(): void
    {
        $template = $this->makeTemplate();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/workplace-templates/{$template->id}", ['name' => 'Modèle renommé'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Modèle renommé');
    }

    public function test_update_d_un_modele_systeme_est_refuse(): void
    {
        $template = $this->makeTemplate(['is_system' => true]);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/workplace-templates/{$template->id}", ['name' => 'Intrusion'])
            ->assertStatus(403);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $template = $this->makeTemplate();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workplace-templates/{$template->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('workplace_templates', ['id' => $template->id]);
    }
}
