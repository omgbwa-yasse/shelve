<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectRiskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'task_id' => $this->task_id,
            'task_title' => $this->whenLoaded('task', fn () => $this->task?->title),
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'probability' => $this->probability,
            'impact' => $this->impact,
            'score' => $this->score,
            'criticality' => $this->criticality,
            'status' => $this->status,
            'mitigation_plan' => $this->mitigation_plan,
            'owner_id' => $this->owner_id,
            'owner_name' => $this->whenLoaded('owner', fn () => $this->owner?->name),
            'review_date' => $this->review_date?->toDateString(),
            'is_overdue' => $this->is_overdue,
            'resolved_at' => $this->resolved_at?->toIso8601ZuluString(),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
