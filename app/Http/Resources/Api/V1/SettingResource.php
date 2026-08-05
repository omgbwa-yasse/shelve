<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D01 — SettingResource, relu le 2026-08-04.
 */
class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'type' => $this->type,
            'default_value' => $this->default_value,
            'description' => $this->description,
            'is_system' => (bool) $this->is_system,
            'constraints' => $this->constraints,
            'user_id' => $this->user_id,
            'organisation_id' => $this->organisation_id,
            'value' => $this->value,
            // Champ calculé : valeur effective = valeur personnalisée sinon défaut
            // (voir `Setting::getEffectiveValue()`, repris tel quel).
            'effective_value' => $this->getEffectiveValue(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}

