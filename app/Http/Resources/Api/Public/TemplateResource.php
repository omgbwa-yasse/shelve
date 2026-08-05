<?php

namespace App\Http\Resources\Api\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D15 — template du portail public. DONNÉES uniquement : `content` est exposé
 * brut, aucun rendu n'est effectué par l'API (R05 — voir routes/api/D15.php).
 */
class TemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'content' => $this->content,
            'variables' => $this->variables,
            'parameters' => $this->parameters,
            'values' => $this->values,
            'status' => $this->status,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
