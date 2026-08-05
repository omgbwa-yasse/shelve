<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D13 — définitions de workflow (org-scopées via `organisation_id`, R03).
 * La Policy utilise le préfixe `workflow_template_*`. Portage finalisé le 2026-08-04.
 */
class WorkflowDefinitionApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workflow_template'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeDefinition(Organisation $org = null, array $extra = []): WorkflowDefinition
    {
        $org = $org ?? $this->organisation;

        return WorkflowDefinition::create([
            'name' => 'Workflow ' . substr(uniqid(), -6),
            'description' => null,
            'bpmn_xml' => '<?xml version="1.0"?><definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL"></definitions>',
            'version' => 1,
            'status' => 'draft',
            'organisation_id' => $org->id,
            'created_by' => $this->user->id,
            ...$extra,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/workflow-definitions')->assertStatus(401);
    }

    public function test_index_retourne_uniquement_les_definitions_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeDefinition();
        $this->makeDefinition($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workflow-definitions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $definition = $this->makeDefinition();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workflow-definitions/{$definition->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $definition->name)
            ->assertJsonPath('data.is_draft', true);
    }

    public function test_store_cree_la_ressource_avec_la_version_incremente(): void
    {
        $this->makeDefinition(null, ['name' => 'Workflow unique']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workflow-definitions', [
                'name' => 'Workflow unique',
                'description' => 'Description',
                'bpmn_xml' => '<?xml version="1.0"?><definitions></definitions>',
                'status' => 'active',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Workflow unique')
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.created_by', $this->user->id)
            ->assertJsonPath('data.organisation_id', $this->organisation->id);

        $response->assertHeader('Location', "/api/v1/workflow-definitions/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workflow-definitions', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'bpmn_xml', 'status']);
    }

    public function test_update_modifie_la_ressource(): void
    {
        $definition = $this->makeDefinition();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/workflow-definitions/{$definition->id}", ['status' => 'active'])
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.updated_by', $this->user->id);
    }

    public function test_destroy_supprime_la_ressource(): void
    {
        $definition = $this->makeDefinition();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workflow-definitions/{$definition->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('workflow_definitions', ['id' => $definition->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une définition d'une autre organisation répond 404 sur
     * show, update et destroy (jamais 403).
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $definition = $this->makeDefinition($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workflow-definitions/{$definition->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/workflow-definitions/{$definition->id}", ['name' => 'Intrusion'])
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workflow-definitions/{$definition->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('workflow_definitions', ['id' => $definition->id]);
    }
}
