<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — activité d'espace de travail, relue le 2026-08-04 contre `WorkplaceActivityController`.
 */
class WorkplaceActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workplace_id' => $this->workplace_id,
            'user_id' => $this->user_id,
            'activity_type' => $this->activity_type,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
