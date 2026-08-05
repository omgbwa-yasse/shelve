<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D06 — contenant de courrier, relu le 2026-08-04 contre `MailContainerController` et le schéma.
 */
class MailContainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'property_id' => $this->property_id,
            'created_by' => $this->created_by,
            'creator_organisation_id' => $this->creator_organisation_id,
            'mails_count' => $this->mails_count ?? $this->mails()->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
