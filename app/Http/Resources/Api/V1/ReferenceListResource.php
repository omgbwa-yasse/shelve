<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D01 — liste de référence, relue le 2026-08-04 contre `Settings\ReferenceListController`.
 */
class ReferenceListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'active' => (bool) $this->active,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            // `values_count` provient de `->withCount('values')` à l'index ; absent
            // sur un show, remplacé par la charge réelle de la relation.
            'values_count' => $this->values_count ?? $this->values()->count(),
            'values' => ReferenceValueResource::collection($this->whenLoaded('values')),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
