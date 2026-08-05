<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D07 — liaison activité ↔ durée de conservation (pivot `retention_activity`).
 */
class RetentionActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'retention_id' => $this->retention_id,
            'activity_id' => $this->activity_id,
        ];
    }
}
