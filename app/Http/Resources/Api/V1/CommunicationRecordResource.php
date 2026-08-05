<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D05 — document de communication, relu le 2026-08-04 contre le schéma.
 */
class CommunicationRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'communication_id' => $this->communication_id,
            'record_id' => $this->record_id,
            'content' => $this->content,
            'is_original' => (bool) $this->is_original,
            'return_date' => $this->return_date ? Carbon::parse($this->return_date)->toDateString() : null,
            'return_effective' => $this->return_effective ? Carbon::parse($this->return_effective)->toDateString() : null,
            'operator_id' => $this->operator_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
