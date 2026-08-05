<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — document partagé dans un espace de travail, relu le 2026-08-04 contre
 * `WorkplaceContentController`.
 */
class WorkplaceDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workplace_id' => $this->workplace_id,
            'document_id' => $this->document_id,
            'shared_by' => $this->shared_by,
            'shared_at' => $this->shared_at?->toIso8601ZuluString(),
            'share_note' => $this->share_note,
            'access_level' => $this->access_level,
            'is_featured' => (bool) $this->is_featured,
            'views_count' => $this->views_count,
            'last_viewed_at' => $this->last_viewed_at?->toIso8601ZuluString(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
