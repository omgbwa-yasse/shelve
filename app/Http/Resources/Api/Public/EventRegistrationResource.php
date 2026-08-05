<?php

namespace App\Http\Resources\Api\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D15 — inscription d'un usager public à un événement. `user_id` est l'usager
 * courant (les registrations n'exposent que les données de leur propriétaire).
 * `registered_at` n'est pas casté par le modèle : conversion explicite ISO-8601.
 */
class EventRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'registered_at' => $this->registered_at ? Carbon::parse($this->registered_at)->toIso8601String() : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
