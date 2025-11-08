<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordDigitalFolderResource extends JsonResource
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
            'type' => [
                'id' => $this->type?->id,
                'code' => $this->type?->code,
                'name' => $this->type?->name,
            ],
            'parent_id' => $this->parent_id,
            'metadata' => $this->metadata,
            'access_level' => $this->access_level,
            'status' => $this->status,
            'requires_approval' => $this->requires_approval,
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'organisation' => [
                'id' => $this->organisation?->id,
                'name' => $this->organisation?->name,
            ],
            'statistics' => [
                'documents_count' => $this->documents_count,
                'subfolders_count' => $this->subfolders_count,
                'total_size' => $this->total_size,
            ],
            'dates' => [
                'start_date' => $this->start_date?->toDateString(),
                'end_date' => $this->end_date?->toDateString(),
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
        ];
    }
}
