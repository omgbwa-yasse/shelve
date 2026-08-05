<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D04 — bordereau, relu le 2026-08-04 contre `SlipController` et le schéma.
 *
 * Champs calculés repris du Blade : `records_count` (le destroy l'interdit tant que
 * le bordereau contient des documents), booléens en vrais booléens.
 */
class SlipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'officer_organisation_id' => $this->officer_organisation_id,
            'officer_id' => $this->officer_id,
            'user_organisation_id' => $this->user_organisation_id,
            'user_id' => $this->user_id,
            'slip_status_id' => $this->slip_status_id,
            'is_received' => (bool) $this->is_received,
            'received_date' => $this->received_date?->toIso8601ZuluString(),
            'received_by' => $this->received_by,
            'is_approved' => (bool) $this->is_approved,
            'approved_date' => $this->approved_date?->toIso8601ZuluString(),
            'approved_by' => $this->approved_by,
            'is_integrated' => (bool) $this->is_integrated,
            'integrated_date' => $this->integrated_date?->toIso8601ZuluString(),
            'integrated_by' => $this->integrated_by,
            'records_count' => $this->records_count ?? $this->records()->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
