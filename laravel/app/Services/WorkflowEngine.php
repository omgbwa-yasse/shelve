<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Task;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowTransition;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class WorkflowEngine
{
    public const ASSIGNMENT_CREATOR = 'creator';

    public const ASSIGNMENT_PREVIOUS = 'previous';

    public const ASSIGNMENT_MANAGER = 'manager';

    public const ASSIGNMENT_FUNCTION = 'function';

    /**
     * Parse BPMN XML and extract transitions
     */
    public function parseAndStoreBPMN(WorkflowDefinition $definition): void
    {
        try {
            $xml = new SimpleXMLElement($definition->bpmn_xml);
            $xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

            // Extract sequence flows (transitions)
            $sequenceFlows = $xml->xpath('//bpmn:sequenceFlow');

            $sequenceOrder = 0;
            foreach ($sequenceFlows as $flow) {
                $flowId = (string) $flow['id'];
                $sourceRef = (string) $flow['sourceRef'];
                $targetRef = (string) $flow['targetRef'];
                $name = (string) ($flow['name'] ?? $flowId);

                // Check if transition already exists
                $existingTransition = WorkflowTransition::where('definition_id', $definition->id)
                    ->where('from_task_key', $sourceRef)
                    ->where('to_task_key', $targetRef)
                    ->first();

                if (! $existingTransition) {
                    WorkflowTransition::create([
                        'definition_id' => $definition->id,
                        'from_task_key' => $sourceRef,
                        'to_task_key' => $targetRef,
                        'name' => $name,
                        'condition' => null,
                        'sequence_order' => $sequenceOrder++,
                        'is_default' => false,
                        'created_by' => $definition->created_by,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error parsing BPMN XML: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Start a workflow instance and create the first task
     */
    public function startWorkflow(WorkflowInstance $instance): void
    {
        try {
            $definition = $instance->definition;
            $xml = new SimpleXMLElement($definition->bpmn_xml);
            $xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

            // Find start event
            $startEvents = $xml->xpath('//bpmn:startEvent');
            if (empty($startEvents)) {
                throw new \Exception('No start event found in BPMN');
            }

            $startEvent = $startEvents[0];
            $startEventId = (string) $startEvent['id'];

            // Find the first task after start event
            $transitions = WorkflowTransition::where('definition_id', $definition->id)
                ->where('from_task_key', $startEventId)
                ->orderBy('sequence_order')
                ->get();

            if ($transitions->isEmpty()) {
                throw new \Exception('No transitions found from start event');
            }

            // Update instance state
            $instance->update([
                'current_state' => [
                    'current_task_key' => $startEventId,
                    'started_at' => now()->toISOString(),
                ],
            ]);

            // Create first task(s)
            foreach ($transitions as $transition) {
                $this->createTaskFromKey($instance, $transition->to_task_key, $transition);
            }
        } catch (\Exception $e) {
            Log::error('Error starting workflow: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute transition when a task is completed
     */
    public function executeTransition(WorkflowInstance $instance, Task $completedTask): void
    {
        try {
            $definition = $instance->definition;

            // Find possible transitions from this task
            $transitions = WorkflowTransition::where('definition_id', $definition->id)
                ->where('from_task_key', $completedTask->task_key)
                ->orderBy('sequence_order')
                ->get();

            if ($transitions->isEmpty()) {
                // No more transitions, workflow might be complete
                $this->checkWorkflowCompletion($instance);

                return;
            }

            // La tâche cible est-elle une porte de convergence (join) ?
            // Si plusieurs flux arrivent sur la même tâche et que tous ne sont pas
            // terminés, on attend — sinon la tâche serait créée en double.
            foreach ($transitions as $transition) {
                $targetKey = $transition->to_task_key;
                $incoming = WorkflowTransition::where('definition_id', $definition->id)
                    ->where('to_task_key', $targetKey)
                    ->pluck('from_task_key');

                $allIncomingCompleted = $incoming->every(function ($fromKey) use ($instance, $completedTask) {
                    return $fromKey === $completedTask->task_key
                        || Task::where('workflow_instance_id', $instance->id)
                            ->where('task_key', $fromKey)
                            ->where('status', 'completed')
                            ->exists();
                });

                if (! $allIncomingCompleted) {
                    continue;
                }

                // Check conditions if any (portes exclusives) — sinon chemin par défaut
                if ($this->evaluateCondition($transition, $completedTask)) {
                    $this->createTaskFromKey($instance, $targetKey, $transition);
                }
            }

            // Update workflow state
            $currentState = $instance->current_state ?? [];
            $currentState['current_task_key'] = $completedTask->task_key;
            $currentState['last_completed_at'] = now()->toISOString();

            $instance->update(['current_state' => $currentState]);

            $this->checkWorkflowCompletion($instance);

        } catch (\Exception $e) {
            Log::error('Error executing transition: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a task from BPMN task key, with dynamic assignment (étape 10).
     */
    protected function createTaskFromKey(WorkflowInstance $instance, string $taskKey, ?WorkflowTransition $transition = null): ?Task
    {
        try {
            $definition = $instance->definition;
            $xml = new SimpleXMLElement($definition->bpmn_xml);
            $xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');

            // Find the task element
            $taskElements = $xml->xpath("//bpmn:*[@id='{$taskKey}']");
            if (empty($taskElements)) {
                Log::warning("Task element not found for key: {$taskKey}");

                return null;
            }

            $taskElement = $taskElements[0];
            $taskName = (string) ($taskElement['name'] ?? $taskKey);

            // Check if task already exists for this workflow instance
            $existingTask = Task::where('workflow_instance_id', $instance->id)
                ->where('task_key', $taskKey)
                ->where('status', '!=', 'completed')
                ->first();

            if ($existingTask) {
                return $existingTask;
            }

            $assignee = $this->resolveAssignee($instance, $transition, $taskKey);

            $record = $this->resolveRecord($instance);

            // Create new task
            $task = Task::create([
                'workflow_instance_id' => $instance->id,
                'task_key' => $taskKey,
                'title' => $taskName,
                'description' => "Workflow task: {$taskName}",
                'status' => 'pending',
                'priority' => $this->resolvePriority($taskElement),
                'assigned_to' => $assignee,
                'organisation_id' => $instance->organisation_id,
                'due_date' => $this->computeDueDate($transition),
                'taskable_type' => $record ? \App\Models\Record::class : null,
                'taskable_id' => $record?->id,
                'created_by' => $instance->started_by,
            ]);

            return $task;

        } catch (\Exception $e) {
            Log::error('Error creating task from key: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Résout l'utilisateur assigné selon la règle de la transition (étape 10) :
     * - creator  : le créateur du flux (celui qui a démarré l'instance).
     * - previous : l'utilisateur ayant terminé la tâche précédente.
     * - manager  : le responsable de la notice liée (ou du créateur).
     * - function : le titulaire d'une fonction (rôle) nommée dans assignment_value.
     */
    protected function resolveAssignee(WorkflowInstance $instance, ?WorkflowTransition $transition, string $taskKey): ?int
    {
        $type = $transition?->assignment_type ?? self::ASSIGNMENT_CREATOR;

        switch ($type) {
            case self::ASSIGNMENT_CREATOR:
                return $instance->started_by;

            case self::ASSIGNMENT_PREVIOUS:
                $previous = Task::where('workflow_instance_id', $instance->id)
                    ->where('task_key', $transition?->from_task_key)
                    ->where('status', 'completed')
                    ->latest('completed_at')
                    ->first();

                return $previous?->completed_by ?? $instance->started_by;

            case self::ASSIGNMENT_MANAGER:
                $record = $this->resolveRecord($instance);
                $managerId = $record?->assigned_to;

                return $managerId ?? $instance->started_by;

            case self::ASSIGNMENT_FUNCTION:
                $roleName = $transition?->assignment_value;

                if (! $roleName) {
                    return $instance->started_by;
                }

                $role = Role::where('name', $roleName)->first();

                if (! $role) {
                    return $instance->started_by;
                }

                return $role->users()->select('users.id')->value('id') ?? $instance->started_by;

            default:
                return $instance->started_by;
        }
    }

    /**
     * La notice liée à l'instance (stockée dans `current_state.record_id`).
     */
    protected function resolveRecord(WorkflowInstance $instance): ?\App\Models\Record
    {
        $recordId = $instance->current_state['record_id'] ?? null;

        if (! $recordId) {
            return null;
        }

        return \App\Models\Record::find($recordId);
    }

    /**
     * Échéance calculée : date de fin = date d'assignation + N jours ouvrables.
     */
    protected function computeDueDate(?WorkflowTransition $transition): ?\Carbon\Carbon
    {
        if (! $transition || ! $transition->due_days) {
            return null;
        }

        $date = now();
        $remaining = (int) $transition->due_days;

        while ($remaining > 0) {
            $date = $date->addDay();

            if ($date->isWeekend()) {
                continue;
            }

            $remaining--;
        }

        return $date->startOfDay();
    }

    /**
     * Priorité dérivée de l'élément BPMN (attribut custom `shelve:priority`).
     */
    protected function resolvePriority(SimpleXMLElement $taskElement): string
    {
        $priority = (string) ($taskElement['priority'] ?? $taskElement->attributes('shelve', true)->priority ?? '');

        return in_array($priority, ['low', 'normal', 'high', 'urgent'], true) ? $priority : 'normal';
    }

    /**
     * Evaluate transition condition
     */
    protected function evaluateCondition(WorkflowTransition $transition, Task $task): bool
    {
        // If no condition, always return true
        if (empty($transition->condition)) {
            return true;
        }

        try {
            $condition = json_decode($transition->condition, true);

            // Simple condition evaluation based on task data
            // Example: {"field": "approval_status", "operator": "equals", "value": "approved"}
            if (isset($condition['field']) && isset($condition['operator']) && isset($condition['value'])) {
                $formData = $task->form_data ?? [];
                $fieldValue = $formData[$condition['field']] ?? null;

                return match ($condition['operator']) {
                    'equals' => $fieldValue == $condition['value'],
                    'not_equals' => $fieldValue != $condition['value'],
                    'greater_than' => $fieldValue > $condition['value'],
                    'less_than' => $fieldValue < $condition['value'],
                    'contains' => str_contains((string) $fieldValue, (string) $condition['value']),
                    default => true,
                };
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error evaluating condition: '.$e->getMessage());

            return true; // On error, continue
        }
    }

    /**
     * Check if workflow is complete
     */
    protected function checkWorkflowCompletion(WorkflowInstance $instance): void
    {
        // Check if all tasks are completed
        $pendingTasks = Task::where('workflow_instance_id', $instance->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        if ($pendingTasks === 0) {
            $instance->complete();
        }
    }

    /**
     * Pause a workflow instance
     */
    public function pauseWorkflow(WorkflowInstance $instance): void
    {
        $instance->pause();
    }

    /**
     * Resume a workflow instance
     */
    public function resumeWorkflow(WorkflowInstance $instance): void
    {
        $instance->resume();
    }

    /**
     * Cancel a workflow instance
     */
    public function cancelWorkflow(WorkflowInstance $instance): void
    {
        // Cancel all pending tasks
        Task::where('workflow_instance_id', $instance->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->update(['status' => 'cancelled']);

        $instance->cancel();
    }
}
