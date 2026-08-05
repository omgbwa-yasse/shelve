<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — dossier partagé dans un espace de travail, relu le 2026-08-04 contre
 * `WorkplaceContentController`.
 */
class WorkplaceFolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workplace_id' => $this->workplace_id,
            'folder_id' => $this->folder_id,
            'shared_by' => $this->shared_by,
            'shared_at' => $this->shared_at?->toIso8601ZuluString(),
            'share_note' => $this->share_note,
            'access_level' => $this->access_level,
            'is_pinned' => (bool) $this->is_pinned,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
