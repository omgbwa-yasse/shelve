<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — tâche, relue le 2026-08-04 contre `TaskController`.
 *
 * Champs calculés repris du modèle : `is_overdue`, `is_completed`,
 * `is_general_task`, `is_workflow_task`.
 */
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'workflow_instance_id' => $this->workflow_instance_id,
            'task_key' => $this->task_key,
            'form_data' => $this->form_data,
            'sequence_order' => $this->sequence_order,
            'parent_task_id' => $this->parent_task_id,
            'taskable_type' => $this->taskable_type,
            'taskable_id' => $this->taskable_id,
            'is_overdue' => (bool) $this->is_overdue,
            'is_completed' => $this->is_completed,
            'is_general_task' => $this->is_general_task,
            'is_workflow_task' => $this->is_workflow_task,
            'due_date' => $this->due_date?->toIso8601ZuluString(),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'completed_by' => $this->completed_by,
            'completed_at' => $this->completed_at?->toIso8601ZuluString(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
