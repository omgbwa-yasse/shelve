<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D05 — communication, relue le 2026-08-04 contre `CommunicationController` et le schéma.
 *
 * Champs calculés repris du modèle Blade : `is_returned`, `is_pending`, `is_approved`,
 * `is_rejected`, `is_in_consultation`, `can_be_edited`, `records_count`.
 */
class CommunicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'content' => $this->content,
            'operator_id' => $this->operator_id,
            'operator_organisation_id' => $this->operator_organisation_id,
            'user_id' => $this->user_id,
            'user_organisation_id' => $this->user_organisation_id,
            'return_date' => $this->return_date ? Carbon::parse($this->return_date)->toDateString() : null,
            'return_effective' => $this->return_effective ? Carbon::parse($this->return_effective)->toDateString() : null,
            'status' => $this->status,
            'is_pending' => $this->isPending(),
            'is_approved' => $this->isApproved(),
            'is_rejected' => $this->isRejected(),
            'is_in_consultation' => $this->isInConsultation(),
            'is_returned' => $this->isReturned(),
            'can_be_edited' => $this->canBeEdited(),
            'records_count' => $this->records_count ?? $this->records()->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
