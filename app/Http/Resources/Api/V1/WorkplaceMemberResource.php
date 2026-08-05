<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — membre d'espace de travail, relu le 2026-08-04 contre `WorkplaceMemberController`.
 */
class WorkplaceMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workplace_id' => $this->workplace_id,
            'user_id' => $this->user_id,
            'role' => $this->role,
            'can_create_folders' => (bool) $this->can_create_folders,
            'can_create_documents' => (bool) $this->can_create_documents,
            'can_delete' => (bool) $this->can_delete,
            'can_share' => (bool) $this->can_share,
            'can_invite' => (bool) $this->can_invite,
            'notify_on_new_content' => (bool) $this->notify_on_new_content,
            'notify_on_mentions' => (bool) $this->notify_on_mentions,
            'notify_on_updates' => (bool) $this->notify_on_updates,
            'invited_by' => $this->invited_by,
            'joined_at' => $this->joined_at?->toIso8601ZuluString(),
            'last_activity_at' => $this->last_activity_at?->toIso8601ZuluString(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
