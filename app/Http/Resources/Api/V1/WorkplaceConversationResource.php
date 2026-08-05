<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — conversation (chat), relue le 2026-08-04 contre `ChatController` et
 * `WorkplaceMessageController`. Les relations `messages` / `participants`
 * apparaissent lorsqu'elles ont été chargées (index/show).
 */
class WorkplaceConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workplace_id' => $this->workplace_id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'messages' => WorkplaceMessageResource::collection($this->whenLoaded('messages')),
            'participants' => $this->whenLoaded('participants', $this->participants->map(fn ($p) => [
                'user_id' => $p->user_id,
                'role' => $p->role,
                'last_read_at' => $p->last_read_at?->toIso8601ZuluString(),
            ])),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            'deleted_at' => $this->deleted_at?->toIso8601ZuluString(),
        ];
    }
}
