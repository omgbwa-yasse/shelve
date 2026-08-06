<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiMeasurementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kpi_id' => $this->kpi_id,
            'value' => (float) $this->value,
            'measured_at' => $this->measured_at?->toDateString(),
            'recorded_by' => $this->recorded_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
