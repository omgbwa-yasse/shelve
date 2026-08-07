<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D03 — conteneur, relu le 2026-08-04 contre `ContainerController` et le schéma.
 */
class ContainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'shelve_id' => $this->shelve_id,
            'status_id' => $this->status_id,
            'property_id' => $this->property_id,
            'creator_id' => $this->creator_id,
            'creator_organisation_id' => $this->creator_organisation_id,
            'is_archived' => (bool) $this->is_archived,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
