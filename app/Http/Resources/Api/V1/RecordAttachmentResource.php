<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D02 — pivot notice ↔ pièce jointe (table `record_physical_attachment`), relue le
 * 2026-08-04. Clé composite `(record_id, attachment_id)` sans colonne `id`. La pièce
 * jointe (table `attachments`, partagée avec D06) est incluable via `?include=attachment`.
 */
class RecordAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'record_id' => $this->record_id,
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
