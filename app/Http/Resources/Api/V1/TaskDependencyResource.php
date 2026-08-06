<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDependencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'predecessor_id' => $this->predecessor_id,
            'successor_id' => $this->successor_id,
            'type' => $this->type,
            'lag_days' => $this->lag_days,
            'predecessor' => $this->whenLoaded('predecessor', fn () => [
                'id' => $this->predecessor->id,
                'title' => $this->predecessor->title,
            ]),
            'successor' => $this->whenLoaded('successor', fn () => [
                'id' => $this->successor->id,
                'title' => $this->successor->title,
            ]),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
