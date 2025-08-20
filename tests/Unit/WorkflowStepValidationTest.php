<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkflowStepValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rejects_invalid_assignments()
    {
        $template = WorkflowTemplate::factory()->create();
        $data = [
            'name' => 'Étape test',
            'order_index' => 0,
            'type' => 'manual',
            'assignments' => [
                [ 'assignee_type' => 'user', 'assignee_id' => null ], // user id manquant
                [ 'assignee_type' => 'organisation', 'organisation_id' => null ], // org id manquant
                [ 'assignee_type' => 'invalid_type' ], // type invalide
            ],
        ];
        $response = $this->actingAs($template->creator)->post(route('workflows.templates.steps.store', $template), $data);
        $response->assertSessionHasErrors([
            'assignments.0.assignee_id',
            'assignments.1.organisation_id',
            'assignments.2.assignee_type',
        ]);
    }

    /** @test */
    public function it_blocks_deletion_of_unique_step()
    {
        $template = WorkflowTemplate::factory()->create();
        $step = WorkflowStep::factory()->create(['workflow_template_id' => $template->id]);
        $response = $this->actingAs($template->creator)->delete(route('workflows.steps.destroy', [$template, $step]));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('workflow_steps', ['id' => $step->id]);
    }
}
