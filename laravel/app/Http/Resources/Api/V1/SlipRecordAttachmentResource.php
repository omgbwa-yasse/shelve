<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D04 — pièce jointe de document de bordereau, relue le 2026-08-04 contre le schéma.
 * Ressource pivot sans colonne `id` (clé `slip_record_id` + `attachment_id`).
 */
class SlipRecordAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slip_record_id' => $this->slip_record_id,
            'attachment_id' => $this->attachment_id,
            'attachment' => $this->whenLoaded('attachment', fn () => [
                'id' => $this->attachment->id,
                'name' => $this->attachment->name,
                'mime_type' => $this->attachment->mime_type,
                'size' => $this->attachment->size,
            ]),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
