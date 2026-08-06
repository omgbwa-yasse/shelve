<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'owner_id' => $this->owner_id,
            'budget_planned' => $this->budget_planned !== null ? (float) $this->budget_planned : null,
            'budget_actual' => $this->budget_actual,
            'is_over_budget' => $this->is_over_budget,
            'attachable_type' => Project::attachableAliasFor($this->attachable_type),
            'attachable_id' => $this->attachable_id,
            'attachable' => $this->whenLoaded('attachable', fn () => [
                'type' => Project::attachableAliasFor($this->attachable_type),
                'id' => $this->attachable_id,
                'label' => $this->attachable->name ?? null,
            ]),
            'objectives_count' => $this->whenCounted('objectives'),
            'tasks_count' => $this->whenCounted('tasks'),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
