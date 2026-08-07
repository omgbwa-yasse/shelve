<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D13 — définition de workflow, relue le 2026-08-04 contre
 * `WorkflowDefinitionController` et le schéma. Dates en ISO-8601 UTC.
 */
class WorkflowDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'name' => $this->name,
            'description' => $this->description,
            'bpmn_xml' => $this->bpmn_xml,
            'version' => $this->version,
            'status' => $this->status,
            'is_active' => $this->status === 'active',
            'is_draft' => $this->status === 'draft',
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
