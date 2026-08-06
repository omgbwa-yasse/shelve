<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Kpi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KpiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'unit' => $this->unit,
            'target_value' => $this->target_value !== null ? (float) $this->target_value : null,
            'direction' => $this->direction,
            'frequency' => $this->frequency,
            'task_id' => $this->task_id,
            'owner_id' => $this->owner_id,
            'attachable_type' => Kpi::attachableAliasFor($this->attachable_type),
            'attachable_id' => $this->attachable_id,
            'attachable' => $this->whenLoaded('attachable', fn () => [
                'type' => Kpi::attachableAliasFor($this->attachable_type),
                'id' => $this->attachable_id,
                'label' => $this->attachable->name ?? null,
            ]),
            'latest_measurement' => $this->whenLoaded(
                'measurements',
                fn () => $this->measurements->isNotEmpty() ? new KpiMeasurementResource($this->measurements->first()) : null,
            ),
            'trend' => $this->whenLoaded('measurements', fn () => $this->trend),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
