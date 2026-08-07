<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KeyResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'objective_id' => $this->objective_id,
            'title' => $this->title,
            'metric_type' => $this->metric_type,
            'start_value' => (float) $this->start_value,
            'target_value' => (float) $this->target_value,
            'current_value' => (float) $this->current_value,
            'unit' => $this->unit,
            'status' => $this->status,
            'progress' => $this->progress,
            'due_date' => $this->due_date?->toDateString(),
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
