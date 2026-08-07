<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D01 — valeur d'une liste de référence.
 */
class ReferenceValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'list_id' => $this->list_id,
            'value' => $this->value,
            'code' => $this->code,
            'description' => $this->description,
            'active' => (bool) $this->active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
