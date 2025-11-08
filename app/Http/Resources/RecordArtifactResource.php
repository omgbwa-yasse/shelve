<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordArtifactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'type' => [
                'id' => $this->type?->id,
                'code' => $this->type?->code,
                'name' => $this->type?->name,
            ],
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'organisation' => [
                'id' => $this->organisation?->id,
                'name' => $this->organisation?->name,
            ],
            'physical_details' => [
                'dimensions' => $this->dimensions,
                'materials' => $this->materials,
                'weight' => $this->weight,
            ],
            'conservation' => [
                'state' => $this->conservation_state,
                'storage_location' => $this->storage_location,
            ],
            'status' => [
                'is_on_display' => $this->is_on_display,
                'is_on_loan' => $this->is_on_loan,
                'status' => $this->status,
            ],
            'valuation' => [
                'estimated_value' => $this->estimated_value,
                'insurance_value' => $this->insurance_value,
                'valuation_date' => $this->valuation_date?->toDateString(),
            ],
            'metadata' => [
                'artist' => $this->artist,
                'period' => $this->period,
                'provenance' => $this->provenance,
                'bibliography' => $this->bibliography,
            ],
            'dates' => [
                'creation_date' => $this->creation_date?->toDateString(),
                'acquisition_date' => $this->acquisition_date?->toDateString(),
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
        ];
    }
}
