<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Objective;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObjectiveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'project_id' => $this->project_id,
            'task_id' => $this->task_id,
            'title' => $this->title,
            'description' => $this->description,
            'period_start' => $this->period_start?->toDateString(),
            'period_end' => $this->period_end?->toDateString(),
            'status' => $this->status,
            'progress' => $this->progress,
            'owner_id' => $this->owner_id,
            'attachable_type' => Objective::attachableAliasFor($this->attachable_type),
            'attachable_id' => $this->attachable_id,
            'attachable' => $this->whenLoaded('attachable', fn () => [
                'type' => Objective::attachableAliasFor($this->attachable_type),
                'id' => $this->attachable_id,
                'label' => $this->attachable->name ?? null,
            ]),
            'key_results' => KeyResultResource::collection($this->whenLoaded('keyResults')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
