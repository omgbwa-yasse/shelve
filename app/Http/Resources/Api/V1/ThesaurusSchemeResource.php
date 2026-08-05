<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D08 — schéma de thésaurus, relu le 2026-08-04 contre `ThesaurusSchemeController`.
 */
class ThesaurusSchemeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uri' => $this->uri,
            'identifier' => $this->identifier,
            'title' => $this->title,
            'description' => $this->description,
            'language' => $this->language,
            'namespace_id' => $this->namespace_id,
            'formatted_title' => $this->formatted_title,
            'concepts_count' => $this->concepts_count ?? $this->concepts()->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
