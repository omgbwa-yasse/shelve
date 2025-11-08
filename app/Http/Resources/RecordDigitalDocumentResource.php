<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordDigitalDocumentResource extends JsonResource
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
            'folder_id' => $this->folder_id,
            'version_number' => $this->version_number,
            'is_current_version' => $this->is_current_version,
            'status' => $this->status,
            'access_level' => $this->access_level,
            'attachment' => $this->when($this->attachment, [
                'id' => $this->attachment?->id,
                'name' => $this->attachment?->name,
                'size' => $this->attachment?->size,
                'mime_type' => $this->attachment?->mime_type,
            ]),
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'metadata' => $this->metadata,
            'download_count' => $this->download_count,
            'dates' => [
                'document_date' => $this->document_date?->toDateString(),
                'retention_until' => $this->retention_until?->toDateString(),
                'approved_at' => $this->approved_at?->toIso8601String(),
                'created_at' => $this->created_at?->toIso8601String(),
            ],
        ];
    }
}
