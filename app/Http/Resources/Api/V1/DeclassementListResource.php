<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D07 — liste de déclassement, relue le 2026-08-04 contre `DeclassementListController`
 * et le schéma. Booléens vrais, dates ISO-8601 UTC.
 */
class DeclassementListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'organisation_id' => $this->organisation_id,
            'declassement_status_id' => $this->declassement_status_id,
            'query_criteria' => $this->query_criteria,
            'digital_support' => (bool) $this->digital_support,
            'analog_support' => (bool) $this->analog_support,
            'include_subrecords' => (bool) $this->include_subrecords,
            'creator_id' => $this->creator_id,
            'is_approval_requested' => (bool) $this->is_approval_requested,
            'approval_requested_date' => $this->approval_requested_date?->toIso8601ZuluString(),
            'approval_requested_by' => $this->approval_requested_by,
            'is_approved' => (bool) $this->is_approved,
            'approved_date' => $this->approved_date?->toIso8601ZuluString(),
            'approved_by' => $this->approved_by,
            'is_validated' => (bool) $this->is_validated,
            'validated_date' => $this->validated_date?->toIso8601ZuluString(),
            'validated_by' => $this->validated_by,
            'is_treated' => (bool) $this->is_treated,
            'treated_date' => $this->treated_date?->toIso8601ZuluString(),
            'treated_by' => $this->treated_by,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
