<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * Représentation d'un agent (guard `web`) dans l'API v1.
 *
 * Contrat : contracts/CONVENTIONS.md §2 et §5.
 * Les dates sont en ISO-8601 UTC, jamais au format local (risque R13).
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            // `birthday` est de type `date` en base mais n'est pas casté dans le modèle
            // (l'ajouter changerait le rendu des vues Blade existantes) : Eloquent le
            // renvoie donc en chaîne. On normalise ici sans toucher au modèle.
            'birthday' => $this->birthday
                ? Carbon::parse($this->birthday)->toDateString()
                : null,
            'current_organisation_id' => $this->current_organisation_id,
            'is_superadmin' => $this->isSuperAdmin(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),

            'current_organisation' => $this->whenLoaded(
                'organisation',
                fn () => [
                    'id' => $this->organisation->id,
                    'name' => $this->organisation->name,
                    'code' => $this->organisation->code,
                ]
            ),

            'organisations' => $this->whenLoaded(
                'organisations',
                fn () => $this->organisations->map(fn ($o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'code' => $o->code,
                ])->values()
            ),

            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles->pluck('name')->values()
            ),
        ];
    }
}
