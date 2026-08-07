<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D09 — rôle, relu le 2026-08-05 contre le schéma.
 *
 * Pas de `display_name` (la colonne n'existe pas dans `roles`). Les relations
 * `permissions`/`users` ne sont exposées que si incluses (`?include=`).
 */
class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),

            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->values()),

            'users' => $this->whenLoaded('users', fn () => $this->users
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                ])->values()),
        ];
    }
}
