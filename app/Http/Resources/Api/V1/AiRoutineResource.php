<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiRoutineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'prompt_id' => $this->prompt_id,
            'skill_id' => $this->skill_id,
            'prompt' => $this->whenLoaded('prompt', fn () => ['id' => $this->prompt->id, 'title' => $this->prompt->title]),
            'skill' => $this->whenLoaded('skill', fn () => ['id' => $this->skill->id, 'name' => $this->skill->name]),
            'schedule_type' => $this->schedule_type,
            'run_time' => $this->run_time,
            'day_of_week' => $this->day_of_week,
            'is_enabled' => $this->is_enabled,
            'last_run_at' => $this->last_run_at?->toIso8601ZuluString(),
            'last_status' => $this->last_status,
            'last_output' => $this->last_output,
            'next_run_at' => $this->next_run_at?->toIso8601ZuluString(),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
