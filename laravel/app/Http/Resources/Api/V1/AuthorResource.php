<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D01 — auteur, relu le 2026-08-04 contre `AuthorController`.
 *
 * `type_name` reprend le champ calculé exposé par `indexApi()` côté Blade.
 */
class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type_id' => $this->type_id,
            'type_name' => $this->whenLoaded('authorType', fn () => $this->authorType->name ?? ''),
            'name' => $this->name,
            'parallel_name' => $this->parallel_name,
            'other_name' => $this->other_name,
            'lifespan' => $this->lifespan,
            'locations' => $this->locations,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
