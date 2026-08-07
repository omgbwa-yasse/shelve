<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D03 — rayonnage, relu le 2026-08-04 contre `ShelfController` et le schéma.
 *
 * Champs calculés repris de l'index/show Blade : capacité, occupation, volumétrie.
 * `occupied_spots` s'appuie sur `containers_count` (via `withCount('containers')`
 * à l'index) ou charge la relation à la demande.
 */
class ShelfResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalCapacity = (float) $this->face * (float) $this->ear * (float) $this->shelf;
        $occupied = $this->containers_count ?? $this->containers()->count();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'observation' => $this->observation,
            'face' => $this->face,
            'ear' => $this->ear,
            'shelf' => $this->shelf,
            'shelf_length' => $this->shelf_length,
            'room_id' => $this->room_id,
            'creator_id' => $this->creator_id,
            'total_capacity' => $totalCapacity,
            'occupied_spots' => $occupied,
            'available_spots' => max(0, $totalCapacity - $occupied),
            'occupancy_percentage' => $totalCapacity > 0 ? round(($occupied / $totalCapacity) * 100, 1) : 0,
            'volumetry_ml' => ($this->face * $this->ear * $this->shelf * $this->shelf_length) / 100,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
