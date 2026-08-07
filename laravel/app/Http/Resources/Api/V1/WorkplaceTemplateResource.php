<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D12 — modèle d'espace de travail, relu le 2026-08-04 contre `WorkplaceTemplateController`.
 */
class WorkplaceTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'category' => $this->category,
            'default_settings' => $this->default_settings,
            'default_structure' => $this->default_structure,
            'default_permissions' => $this->default_permissions,
            'is_active' => (bool) $this->is_active,
            'is_system' => (bool) $this->is_system,
            'usage_count' => $this->usage_count,
            'display_order' => $this->display_order,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            'deleted_at' => $this->deleted_at?->toIso8601ZuluString(),
        ];
    }
}
