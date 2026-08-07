<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D09 — organisation, relue le 2026-08-05 contre le schéma.
 *
 * Référentiel global (hierarchie `parent_id`). Dates en ISO-8601 UTC.
 */
class OrganisationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),

            'parent' => $this->whenLoaded('parent', fn () => [
                'id' => $this->parent->id,
                'code' => $this->parent->code,
                'name' => $this->parent->name,
            ]),

            'children' => $this->whenLoaded('children', fn () => $this->children
                ->map(fn ($child) => [
                    'id' => $child->id,
                    'code' => $child->code,
                    'name' => $child->name,
                ])->values()),
        ];
    }
}
