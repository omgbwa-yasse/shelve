<?php

namespace App\Http\Requests\Api\V1\RecordType;

use Illuminate\Foundation\Http\FormRequest;

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
class UpdateRecordTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:50'],  // déduit du schéma
            'name' => ['sometimes', 'string', 'max:150'],  // déduit du schéma
            'description' => ['sometimes', 'string'],  // déduit du schéma
            'parent_id' => ['sometimes', 'integer', 'exists:record_types,id'],  // déduit du schéma
            'reference_list_id' => ['sometimes', 'integer', 'exists:reference_lists,id'],  // déduit du schéma
            'is_container' => ['sometimes', 'integer'],  // déduit du schéma
            'icon' => ['sometimes', 'string', 'max:191'],  // déduit du schéma
            'color' => ['sometimes', 'string', 'max:20'],  // déduit du schéma
            'code_prefix' => ['sometimes', 'string', 'max:191'],  // déduit du schéma
            'code_pattern' => ['sometimes', 'string', 'max:191'],  // déduit du schéma
            'allowed_mime_types' => ['sometimes', 'array'],  // déduit du schéma
            'allowed_extensions' => ['sometimes', 'array'],  // déduit du schéma
            'max_file_size' => ['sometimes', 'integer'],  // déduit du schéma
            'requires_versioning' => ['sometimes', 'integer'],  // déduit du schéma
            'requires_approval' => ['sometimes', 'integer'],  // déduit du schéma
            'requires_signature' => ['sometimes', 'integer'],  // déduit du schéma
            'default_access_level' => ['sometimes', 'string', 'max:20'],  // déduit du schéma
            'is_active' => ['sometimes', 'integer'],  // déduit du schéma
            'display_order' => ['sometimes', 'integer'],  // déduit du schéma
            'created_by' => ['sometimes', 'integer', 'exists:users,id'],  // déduit du schéma
            'updated_by' => ['sometimes', 'integer', 'exists:users,id'],  // déduit du schéma
            'legacy_type' => ['sometimes', 'string', 'max:50'],  // déduit du schéma
        ];
    }
}
