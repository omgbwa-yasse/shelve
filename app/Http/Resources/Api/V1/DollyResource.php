<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D11 — chariot, relu le 2026-08-04 contre `DollyController` et le schéma.
 * Booléens vrais, dates en ISO-8601 UTC.
 */
class DollyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'is_public' => (bool) $this->is_public,
            'created_by' => $this->created_by,
            'owner_organisation_id' => $this->owner_organisation_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
