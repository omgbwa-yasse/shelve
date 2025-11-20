<?php

namespace App\Services;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowTransition;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class WorkflowEngine
{
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
                $flowId = (string)$flow['id'];
                $sourceRef = (string)$flow['sourceRef'];
                $targetRef = (string)$flow['targetRef'];
                $name = (string)($flow['name'] ?? $flowId);

                // Check if transition already exists
                $existingTransition = WorkflowTransition::where('definition_id', $definition->id)
                    ->where('from_task_key', $sourceRef)
                    ->where('to_task_key', $targetRef)
                    ->first();

                if (!$existingTransition) {
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
            Log::error('Error parsing BPMN XML: ' . $e->getMessage());
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
            $startEventId = (string)$startEvent['id'];

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
                $this->createTaskFromKey($instance, $transition->to_task_key);
            }
        } catch (\Exception $e) {
            Log::error('Error starting workflow: ' . $e->getMessage());
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

            // Execute transitions
            foreach ($transitions as $transition) {
                // Check conditions if any
                if ($this->evaluateCondition($transition, $completedTask)) {
                    $this->createTaskFromKey($instance, $transition->to_task_key);
                }
            }

            // Update workflow state
            $currentState = $instance->current_state;
            $currentState['current_task_key'] = $completedTask->task_key;
            $currentState['last_completed_at'] = now()->toISOString();

            $instance->update(['current_state' => $currentState]);

        } catch (\Exception $e) {
            Log::error('Error executing transition: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a task from BPMN task key
     */
    protected function createTaskFromKey(WorkflowInstance $instance, string $taskKey): ?Task
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
            $taskName = (string)($taskElement['name'] ?? $taskKey);

            // Check if task already exists for this workflow instance
            $existingTask = Task::where('workflow_instance_id', $instance->id)
                ->where('task_key', $taskKey)
                ->where('status', '!=', 'completed')
                ->first();

            if ($existingTask) {
                return $existingTask;
            }

            // Create new task
            $task = Task::create([
                'workflow_instance_id' => $instance->id,
                'task_key' => $taskKey,
                'title' => $taskName,
                'description' => "Workflow task: {$taskName}",
                'status' => 'pending',
                'priority' => 'normal',
                'created_by' => $instance->started_by,
            ]);

            return $task;

        } catch (\Exception $e) {
            Log::error('Error creating task from key: ' . $e->getMessage());
            throw $e;
        }
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

                return match($condition['operator']) {
                    'equals' => $fieldValue == $condition['value'],
                    'not_equals' => $fieldValue != $condition['value'],
                    'greater_than' => $fieldValue > $condition['value'],
                    'less_than' => $fieldValue < $condition['value'],
                    'contains' => str_contains($fieldValue, $condition['value']),
                    default => true,
                };
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error evaluating condition: ' . $e->getMessage());
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
