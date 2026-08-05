<?php

namespace App\Http\Resources\Api\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D15 — événement du portail public. `registration_count` n'est présent que
 * si chargé (withCount) : aucun détail nominatif des participants n'est exposé.
 */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'location' => $this->location,
            'is_online' => (bool) $this->is_online,
            'online_link' => $this->online_link,
            'registration_count' => $this->whenNotNull($this->registrations_count),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
