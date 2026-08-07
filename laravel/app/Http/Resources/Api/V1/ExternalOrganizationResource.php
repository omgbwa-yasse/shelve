<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D01 — ExternalOrganizationResource, relu le 2026-08-04.
 */
class ExternalOrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'registration_number' => $this->registration_number,
            'tax_id' => $this->tax_id,
            'is_verified' => (bool) $this->is_verified,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            'legal_form' => $this->legal_form,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ];
    }
}

