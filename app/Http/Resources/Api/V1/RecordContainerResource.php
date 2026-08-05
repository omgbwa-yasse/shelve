<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D02 — pivot notice ↔ contenant (table `record_physical_container`), relue le
 * 2026-08-04. Clé composite `(record_physical_id, container_id)` sans colonne `id`.
 *
 * Depuis l'unification, la colonne `record_physical_id` porte l'id de la notice
 * (`records.id`) : elle est exposée sous le nom unifié `record_id`. Le contenant
 * est un contenant D03 : incluable via `?include=container`.
 */
class RecordContainerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'record_id' => $this->record_physical_id,
            'container_id' => $this->container_id,
            'description' => $this->description,
            'creator_id' => $this->creator_id,
            'container' => $this->whenLoaded('container', fn () => new ContainerResource($this->container)),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
