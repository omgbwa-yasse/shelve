<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @generated par `php artisan make:api-resource-set` — domaine D02.
 *
 * CE FICHIER EST UN POINT DE DÉPART, PAS UN LIVRABLE.
 * Les règles ci-dessous sont déduites du schéma et des règles déjà présentes dans le
 * contrôleur Blade. Le schéma ne connaît ni les règles métier ni ce que la vue imposait
 * implicitement (risques R01 et R02) : relire le contrôleur ET ses vues avant de valider.
 *
 * Retirer ce bandeau une fois le fichier relu.
 */
class RecordTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'reference_list_id' => $this->reference_list_id,
            'is_container' => (bool) $this->is_container,
            'icon' => $this->icon,
            'color' => $this->color,
            'code_prefix' => $this->code_prefix,
            'code_pattern' => $this->code_pattern,
            'allowed_mime_types' => $this->allowed_mime_types,
            'allowed_extensions' => $this->allowed_extensions,
            'max_file_size' => $this->max_file_size,
            'requires_versioning' => (bool) $this->requires_versioning,
            'requires_approval' => (bool) $this->requires_approval,
            'requires_signature' => (bool) $this->requires_signature,
            'default_access_level' => $this->default_access_level,
            'is_active' => (bool) $this->is_active,
            'display_order' => $this->display_order,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'legacy_type' => $this->legacy_type,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
            'deleted_at' => $this->deleted_at?->toIso8601ZuluString(),
        ];
    }
}
