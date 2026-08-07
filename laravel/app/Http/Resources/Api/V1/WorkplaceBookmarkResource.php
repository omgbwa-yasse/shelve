<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — favori d'espace de travail, relu le 2026-08-04 contre `WorkplaceBookmarkController`.
 */
class WorkplaceBookmarkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workplace_id' => $this->workplace_id,
            'user_id' => $this->user_id,
            'bookmarkable_type' => $this->bookmarkable_type,
            'bookmarkable_id' => $this->bookmarkable_id,
            'note' => $this->note,
            'tags' => $this->tags,
            'tags_array' => $this->tags_array,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
