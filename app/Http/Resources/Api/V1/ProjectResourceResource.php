<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'type' => $this->type,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'role' => $this->role,
            'allocation_percent' => $this->allocation_percent !== null ? (float) $this->allocation_percent : null,
            'unit_cost' => $this->unit_cost !== null ? (float) $this->unit_cost : null,
            'quantity' => $this->quantity !== null ? (float) $this->quantity : null,
            'planned_amount' => $this->planned_amount !== null ? (float) $this->planned_amount : null,
            'actual_amount' => $this->actual_amount !== null ? (float) $this->actual_amount : null,
            'is_over_budget' => $this->is_over_budget,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
