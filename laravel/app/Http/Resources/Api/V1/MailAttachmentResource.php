<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D06 — pièce jointe de courrier, relue le 2026-08-04 contre `MailAttachmentController`
 * et le schéma de la table `attachments` (table partagée avec les enregistrements).
 */
class MailAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'description' => $this->description,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'type' => $this->type,
            'crypt' => $this->crypt,
            'crypt_sha512' => $this->crypt_sha512,
            'file_hash_md5' => $this->file_hash_md5,
            'thumbnail_path' => $this->thumbnail_path,
            'is_primary' => (bool) $this->is_primary,
            'display_order' => $this->display_order,
            'creator_id' => $this->creator_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
