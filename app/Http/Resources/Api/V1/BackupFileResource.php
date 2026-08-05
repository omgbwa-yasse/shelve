<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D16 — fichier de sauvegarde, relu le 2026-08-04 contre `BackupFileController`.
 */
class BackupFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'backup_id' => $this->backup_id,
            'path_original' => $this->path_original,
            'path_storage' => $this->path_storage,
            'size' => $this->size,
            'hash' => $this->hash,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
