<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D04 — association document↔contenant, relue le 2026-08-04 contre le schéma.
 * Ressource pivot sans colonne `id` (clé composite `slip_record_id` + `container_id`).
 */
class SlipRecordContainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slip_record_id' => $this->slip_record_id,
            'container_id' => $this->container_id,
            'creator_id' => $this->creator_id,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
