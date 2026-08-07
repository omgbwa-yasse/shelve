<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D03 — salle, relue le 2026-08-04 contre `RoomController` et le schéma.
 *
 * Champs calculés repris de l'index Blade : `shelves_count`, `is_visible`,
 * `effective_visibility` (héritage de la visibilité du bâtiment).
 */
class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'visibility' => $this->visibility,
            'effective_visibility' => $this->getEffectiveVisibility(),
            'is_visible' => $this->visibility === 'public',
            'type' => $this->type,
            'floor_id' => $this->floor_id,
            'shelves_count' => $this->shelves_count ?? $this->shelves()->count(),
            'creator_id' => $this->creator_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
