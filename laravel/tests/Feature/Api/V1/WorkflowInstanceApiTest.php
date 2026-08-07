<?php

namespace Tests\Feature\Api\V1;

use App\Models\Organisation;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Api\V1\Concerns\WithPermissions;
use Tests\TestCase;

/**
 * D13 — instances de workflow (org-scopées via `organisation_id`, R03).
 * Portage finalisé le 2026-08-04.
 */
class WorkflowInstanceApiTest extends TestCase
{
    use DatabaseTransactions, WithPermissions;

    private const PERMISSIONS = ['workflow_instance'];

    private User $user;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = Organisation::factory()->create();
        $this->user = User::factory()->forOrganisation($this->organisation)->create();
        $this->grantD01Permissions($this->user, self::PERMISSIONS);
    }

    private function makeDefinition(Organisation $org = null): WorkflowDefinition
    {
        $org = $org ?? $this->organisation;

        return WorkflowDefinition::create([
            'name' => 'Workflow ' . substr(uniqid(), -6),
            'description' => null,
            'bpmn_xml' => '<?xml version="1.0"?><definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL"></definitions>',
            'version' => 1,
            'status' => 'active',
            'organisation_id' => $org->id,
            'created_by' => $this->user->id,
        ]);
    }

    private function makeInstance(Organisation $org = null): WorkflowInstance
    {
        $org = $org ?? $this->organisation;
        $definition = $this->makeDefinition($org);

        return WorkflowInstance::create([
            'definition_id' => $definition->id,
            'name' => 'Instance ' . substr(uniqid(), -6),
            'status' => 'running',
            'current_state' => [],
            'organisation_id' => $org->id,
            'started_by' => $this->user->id,
        ]);
    }

    public function test_index_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/workflow-instances')->assertStatus(401);
    }

    public function test_index_retourne_uniquement_les_instances_de_l_organisation(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $this->makeInstance();
        $this->makeInstance($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/workflow-instances')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_show_retourne_la_ressource(): void
    {
        $instance = $this->makeInstance();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workflow-instances/{$instance->id}")
            ->assertOk()
            ->assertJsonPath('data.name', $instance->name)
            ->assertJsonPath('data.is_running', true);
    }

    public function test_store_cree_l_instance_demarree(): void
    {
        $definition = $this->makeDefinition();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workflow-instances', [
                'definition_id' => $definition->id,
                'name' => 'Instance A',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Instance A')
            ->assertJsonPath('data.status', 'running')
            ->assertJsonPath('data.organisation_id', $this->organisation->id)
            ->assertJsonPath('data.started_by', $this->user->id);

        $response->assertHeader('Location', "/api/v1/workflow-instances/{$response->json('data.id')}");
    }

    public function test_store_valide_ses_entrees(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/workflow-instances', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['definition_id', 'name']);
    }

    public function test_pause_resume_cancel(): void
    {
        $instance = $this->makeInstance();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workflow-instances/{$instance->id}/pause")
            ->assertOk()
            ->assertJsonPath('data.is_paused', true);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workflow-instances/{$instance->id}/resume")
            ->assertOk()
            ->assertJsonPath('data.is_running', true);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workflow-instances/{$instance->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.is_cancelled', true);
    }

    public function test_destroy_supprime_l_instance(): void
    {
        $instance = $this->makeInstance();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workflow-instances/{$instance->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('workflow_instances', ['id' => $instance->id]);
    }

    /**
     * ⚠️ Cœur du risque R03 : une instance d'une autre organisation répond 404.
     */
    public function test_isolation_entre_organisations(): void
    {
        $orgEtrangere = Organisation::factory()->create();
        $userEtranger = User::factory()->forOrganisation($orgEtrangere)->create();
        $this->grantD01Permissions($userEtranger, self::PERMISSIONS);

        $instance = $this->makeInstance($orgEtrangere);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/workflow-instances/{$instance->id}")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/workflow-instances/{$instance->id}/pause")
            ->assertStatus(404);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/workflow-instances/{$instance->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('workflow_instances', ['id' => $instance->id]);
    }
}
