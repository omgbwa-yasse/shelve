<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D13 — instance de workflow, relue le 2026-08-04 contre
 * `WorkflowInstanceController` et le schéma. Dates en ISO-8601 UTC.
 */
class WorkflowInstanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'definition_id' => $this->definition_id,
            'name' => $this->name,
            'status' => $this->status,
            'is_running' => $this->status === 'running',
            'is_paused' => $this->status === 'paused',
            'is_completed' => $this->status === 'completed',
            'is_cancelled' => $this->status === 'cancelled',
            'current_state' => $this->current_state,
            'started_by' => $this->started_by,
            'started_at' => $this->started_at?->toIso8601ZuluString(),
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            'completed_by' => $this->completed_by,
            'completed_at' => $this->completed_at?->toIso8601ZuluString(),
        ];
    }
}
