<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D12 — espace de travail, relu le 2026-08-04 contre `WorkplaceController`.
 *
 * Champs calculés repris du modèle : `storage_used_mb`, `storage_percentage`,
 * `is_full`.
 */
class WorkplaceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'icon' => $this->icon,
            'color' => $this->color,
            'settings' => $this->settings,
            'is_public' => (bool) $this->is_public,
            'allow_external_sharing' => (bool) $this->allow_external_sharing,
            'max_members' => $this->max_members,
            'max_storage_mb' => $this->max_storage_mb,
            'members_count' => $this->members_count,
            'folders_count' => $this->folders_count,
            'documents_count' => $this->documents_count,
            'storage_used_bytes' => $this->storage_used_bytes,
            'storage_used_mb' => $this->storage_used_mb,
            'storage_percentage' => $this->storage_percentage,
            'is_full' => $this->is_full,
            'status' => $this->status,
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->toDateString() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->toDateString() : null,
            'archived_at' => $this->archived_at?->toIso8601ZuluString(),
            'organisation_id' => $this->organisation_id,
            'owner_id' => $this->owner_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            'deleted_at' => $this->deleted_at?->toIso8601ZuluString(),
        ];
    }
}
