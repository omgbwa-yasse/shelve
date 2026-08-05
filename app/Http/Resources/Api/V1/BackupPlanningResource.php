<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D16 — planification de sauvegarde, relue le 2026-08-04 contre `BackupPlanningController`.
 */
class BackupPlanningResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'backup_id' => $this->backup_id,
            'frequence' => $this->frequence,
            'week_day' => $this->week_day,
            'month_day' => $this->month_day,
            'hour' => $this->hour,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
