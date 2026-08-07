<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D04 — document de bordereau, relu le 2026-08-04 contre `SlipRecordController` et le schéma.
 *
 * Champs calculés repris du modèle Blade : `keywords_string` (mots-clés séparés par `;`),
 * `containers_count`.
 */
class SlipRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slip_id' => $this->slip_id,
            'code' => $this->code,
            'name' => $this->name,
            'date_format' => $this->date_format,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'date_exact' => $this->date_exact ? Carbon::parse($this->date_exact)->toDateString() : null,
            'content' => $this->content,
            'level_id' => $this->level_id,
            'width' => $this->width === null ? null : (float) $this->width,
            'width_description' => $this->width_description,
            'support_id' => $this->support_id,
            'activity_id' => $this->activity_id,
            'creator_id' => $this->creator_id,
            'keywords_string' => $this->whenLoaded('keywords', $this->keywords_string),
            'containers_count' => $this->containers_count ?? $this->containers()->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
