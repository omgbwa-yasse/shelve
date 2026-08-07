<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D02 — demande de réactivation, relue le 2026-08-04 contre le schéma
 * (table `record_reactivations`) et `RecordReactivationController`.
 */
class RecordReactivationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'record_id' => $this->record_id,
            'organisation_id' => $this->organisation_id,
            'previous_status_id' => $this->previous_status_id,
            'previous_transfer_date' => $this->previous_transfer_date
                ? Carbon::parse($this->previous_transfer_date)->toDateString()
                : null,
            'new_transfer_date' => $this->new_transfer_date
                ? Carbon::parse($this->new_transfer_date)->toDateString()
                : null,
            'reason' => $this->reason,
            'rejection_reason' => $this->rejection_reason,
            'is_approved' => (bool) $this->is_approved,
            'requested_by' => $this->requested_by,
            'requested_date' => $this->requested_date ? Carbon::parse($this->requested_date)->toIso8601ZuluString() : null,
            'approved_by' => $this->approved_by,
            'approved_date' => $this->approved_date ? Carbon::parse($this->approved_date)->toIso8601ZuluString() : null,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->toIso8601ZuluString() : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->toIso8601ZuluString() : null,
        ];
    }
}
