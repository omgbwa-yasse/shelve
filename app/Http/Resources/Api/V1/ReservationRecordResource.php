<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D05 — document de réservation, relu le 2026-08-04 contre le schéma.
 */
class ReservationRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_id,
            'record_id' => $this->record_id,
            'is_original' => (bool) $this->is_original,
            'reservation_date' => $this->reservation_date ? Carbon::parse($this->reservation_date)->toDateString() : null,
            'operator_id' => $this->operator_id,
            'communication_id' => $this->communication_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
