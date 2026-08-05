<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 10 — Module Workflow : assignation dynamique (créateur/précédent/fonction),
 * échéance en jours ouvrables, sécurité de démarrage, portes exclusives/parallèles.
 */
class WorkflowEngineAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $creator;
    protected User $otherUser;
    protected static int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        self::$counter++;

        $this->creator = User::create([
            'name' => 'Créateur ' . self::$counter,
            'email' => 'creator-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $this->otherUser = User::create([
            'name' => 'Autre ' . self::$counter,
            'email' => 'other-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $this->creator->roles()->attach($role->id);
    }

    protected function makeDefinition(string $code): WorkflowDefinition
    {
        return WorkflowDefinition::create([
            'name' => 'Définition ' . $code,
            'bpmn_xml' => '<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL">'
                . '<bpmn:process id="p"><bpmn:startEvent id="start"/>'
                . '<bpmn:task id="t1" name="T1"/><bpmn:task id="t2" name="T2"/><bpmn:endEvent id="end"/>'
                . '<bpmn:sequenceFlow id="sf0" sourceRef="start" targetRef="t1"/>'
                . '<bpmn:sequenceFlow id="sf1" sourceRef="t1" targetRef="t2"/>'
                . '<bpmn:sequenceFlow id="sf2" sourceRef="t2" targetRef="end"/>'
                . '</bpmn:process></bpmn:definitions>',
            'version' => 1,
            'status' => 'active',
            'visibility' => WorkflowDefinition::VISIBILITY_PUBLIC,
            'organisation_id' => 1,
            'created_by' => $this->creator->id,
        ]);
    }

    protected function startInstance(WorkflowDefinition $definition, ?int $startedBy = null): WorkflowInstance
    {
        $instance = WorkflowInstance::create([
            'definition_id' => $definition->id,
            'name' => 'Instance ' . self::$counter,
            'status' => 'running',
            'current_state' => [],
            'organisation_id' => 1,
            'started_by' => $startedBy ?? $this->creator->id,
        ]);

        app(WorkflowEngine::class)->startWorkflow($instance);

        return $instance;
    }

    public function test_assignment_creator_applies_started_by(): void
    {
        $definition = $this->makeDefinition('creator');
        app(WorkflowEngine::class)->parseAndStoreBPMN($definition);

        $transition = $definition->transitions()->where('to_task_key', 't1')->first();
        $transition->update(['assignment_type' => 'creator']);

        $instance = $this->startInstance($definition);

        $task = Task::where('workflow_instance_id', $instance->id)->where('task_key', 't1')->first();
        $this->assertEquals($this->creator->id, $task->assigned_to);
    }

    public function test_assignment_previous_uses_completer(): void
    {
        $definition = $this->makeDefinition('previous');
        app(WorkflowEngine::class)->parseAndStoreBPMN($definition);

        $transition = $definition->transitions()->where('to_task_key', 't2')->first();
        $transition->update(['assignment_type' => 'previous']);

        $instance = $this->startInstance($definition);

        $t1 = Task::where('workflow_instance_id', $instance->id)->where('task_key', 't1')->first();
        $t1->complete($this->otherUser->id);

        $engine = app(WorkflowEngine::class);
        $engine->executeTransition($instance, $t1);

        $t2 = Task::where('workflow_instance_id', $instance->id)->where('task_key', 't2')->first();
        $this->assertEquals($this->otherUser->id, $t2->assigned_to);
    }

    public function test_assignment_function_uses_role_holder(): void
    {
        $holder = User::create([
            'name' => 'Titulaire ' . self::$counter,
            'email' => 'holder-' . self::$counter . '@example.com',
            'password' => bcrypt('password'),
            'birthday' => '1990-01-01',
        ]);
        $managerRole = Role::firstOrCreate(['name' => 'archiviste']);
        $holder->roles()->attach($managerRole->id);

        $definition = $this->makeDefinition('function');
        app(WorkflowEngine::class)->parseAndStoreBPMN($definition);

        $transition = $definition->transitions()->where('to_task_key', 't1')->first();
        $transition->update(['assignment_type' => 'function', 'assignment_value' => 'archiviste']);

        $instance = $this->startInstance($definition);

        $task = Task::where('workflow_instance_id', $instance->id)->where('task_key', 't1')->first();
        $this->assertEquals($holder->id, $task->assigned_to);
    }

    public function test_due_date_computed_in_business_days(): void
    {
        $definition = $this->makeDefinition('due');
        app(WorkflowEngine::class)->parseAndStoreBPMN($definition);

        $transition = $definition->transitions()->where('to_task_key', 't1')->first();
        $transition->update(['assignment_type' => 'creator', 'due_days' => 1]);

        $instance = $this->startInstance($definition);

        $task = Task::where('workflow_instance_id', $instance->id)->where('task_key', 't1')->first();

        $this->assertNotNull($task->due_date);
        $this->assertFalse($task->due_date->isWeekend());
    }

    public function test_private_workflow_cannot_be_started_by_unauthorized_user(): void
    {
        $definition = $this->makeDefinition('private');
        $definition->update([
            'visibility' => WorkflowDefinition::VISIBILITY_PRIVATE,
            'allowed_user_ids' => [$this->creator->id],
        ]);

        $this->assertTrue($definition->canStart($this->creator));
        $this->assertFalse($definition->canStart($this->otherUser));
    }
}
