<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D07 — vue « cycle de vie » d'une notice (liste éliminables, etc.).
 *
 * Sous-ensemble minimal, propre au D07 : la ressource complète `Record` relève du
 * domaine D02 (non porté en phase 1). Les dates sont en ISO-8601 UTC (CONVENTIONS §5).
 */
class RecordLifecycleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'activity_id' => $this->activity_id,
            'status_id' => $this->status_id,
            'level_id' => $this->level_id,
            'organisation_id' => $this->organisation_id,
            'creator_id' => $this->creator_id,
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->toDateString() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->toDateString() : null,
            'date_exact' => $this->date_exact ? Carbon::parse($this->date_exact)->toDateString() : null,
            'date_format' => $this->date_format,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
