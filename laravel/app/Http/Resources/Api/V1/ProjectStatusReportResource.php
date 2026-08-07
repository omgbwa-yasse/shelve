<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectStatusReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'reported_at' => $this->reported_at?->toDateString(),
            'percent_complete' => $this->percent_complete,
            'summary' => $this->summary,
            'risks' => $this->risks,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
