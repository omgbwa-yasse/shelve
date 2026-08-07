<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D16 — sauvegarde, relue le 2026-08-04 contre `BackupController` et le schéma.
 */
class BackupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_time' => $this->date_time?->toIso8601ZuluString(),
            'type' => $this->type,
            'description' => $this->description,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'size' => $this->size,
            'backup_file' => $this->backup_file,
            'path' => $this->path,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
