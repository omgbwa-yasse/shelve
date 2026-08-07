<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D08 — concept de thésaurus, relu le 2026-08-04 contre `ThesaurusController`.
 *
 * Champs calculés repris des vues/concept du Blade : `preferred_label`,
 * `language`, `is_top_term`, `category`.
 */
class ThesaurusConceptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scheme_id' => $this->scheme_id,
            'uri' => $this->uri,
            'notation' => $this->notation,
            'status' => $this->status === null ? null : (int) $this->status,
            'preferred_label' => $this->preferred_label,
            'language' => $this->language,
            'is_top_term' => $this->is_top_term,
            'category' => $this->category,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
