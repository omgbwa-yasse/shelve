<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 10 — Tableau de bord Workflow : échéances, retards, taux de respect
 * calculés sur données réelles.
 */
class WorkflowDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->user = User::create([
            'name' => 'Dash ' . self::$counter,
            'email' => 'dash-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->user->roles()->attach(Role::firstOrCreate(['name' => 'superadmin'])->id);
    }

    public function test_dashboard_renders_with_real_data(): void
    {
        $definition = WorkflowDefinition::create([
            'name' => 'Définition dash',
            'bpmn_xml' => '<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL"></bpmn:definitions>',
            'version' => 1,
            'status' => 'active',
            'organisation_id' => 1,
            'created_by' => $this->user->id,
        ]);

        $instance = WorkflowInstance::create([
            'definition_id' => $definition->id,
            'name' => 'Instance en retard',
            'status' => 'running',
            'current_state' => [],
            'organisation_id' => 1,
            'started_by' => $this->user->id,
        ]);

        // Une tâche en retard et une tâche terminée dans les temps.
        Task::create([
            'workflow_instance_id' => $instance->id,
            'task_key' => 'late',
            'title' => 'Tâche en retard',
            'status' => 'in_progress',
            'priority' => 'normal',
            'assigned_to' => $this->user->id,
            'due_date' => now()->subDays(3),
            'organisation_id' => 1,
            'created_by' => $this->user->id,
        ]);

        Task::create([
            'workflow_instance_id' => $instance->id,
            'task_key' => 'on_time',
            'title' => 'Tâche dans les temps',
            'status' => 'completed',
            'priority' => 'normal',
            'assigned_to' => $this->user->id,
            'due_date' => now()->subDays(2),
            'completed_at' => now()->subDays(3),
            'organisation_id' => 1,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('workflows.dashboard'));

        $response->assertOk();
        $response->assertSee('Tâche en retard');
        $response->assertSee('100');
        $response->assertSee('%');
    }
}
