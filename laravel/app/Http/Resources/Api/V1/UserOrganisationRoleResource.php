<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D09 — rattachement agent→organisation→rôle (`user_organisation_role`), relu
 * le 2026-08-05.
 *
 * Clé primaire composite (`user_id`, `organisation_id`) : pas de champ `id`.
 * `creator_id` est l'agent qui a posé le rattachement.
 */
class UserOrganisationRoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'organisation_id' => $this->organisation_id,
            'role_id' => $this->role_id,
            'creator_id' => $this->creator_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),

            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),

            'organisation' => $this->whenLoaded('organisation', fn () => [
                'id' => $this->organisation->id,
                'code' => $this->organisation->code,
                'name' => $this->organisation->name,
            ]),

            'role' => $this->whenLoaded('role', fn () => [
                'id' => $this->role->id,
                'name' => $this->role->name,
            ]),

            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
        ];
    }
}
