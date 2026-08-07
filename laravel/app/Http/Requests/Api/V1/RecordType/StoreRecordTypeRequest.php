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
class StoreRecordTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],  // déduit du schéma
            'name' => ['required', 'string', 'max:150'],  // déduit du schéma
            'description' => ['nullable', 'string'],  // déduit du schéma
            'parent_id' => ['nullable', 'integer', 'exists:record_types,id'],  // déduit du schéma
            'reference_list_id' => ['nullable', 'integer', 'exists:reference_lists,id'],  // déduit du schéma
            'is_container' => ['nullable', 'integer'],  // déduit du schéma
            'icon' => ['nullable', 'string', 'max:191'],  // déduit du schéma
            'color' => ['nullable', 'string', 'max:20'],  // déduit du schéma
            'code_prefix' => ['nullable', 'string', 'max:191'],  // déduit du schéma
            'code_pattern' => ['nullable', 'string', 'max:191'],  // déduit du schéma
            'allowed_mime_types' => ['nullable', 'array'],  // déduit du schéma
            'allowed_extensions' => ['nullable', 'array'],  // déduit du schéma
            'max_file_size' => ['nullable', 'integer'],  // déduit du schéma
            'requires_versioning' => ['nullable', 'integer'],  // déduit du schéma
            'requires_approval' => ['nullable', 'integer'],  // déduit du schéma
            'requires_signature' => ['nullable', 'integer'],  // déduit du schéma
            'default_access_level' => ['nullable', 'string', 'max:20'],  // déduit du schéma
            'is_active' => ['nullable', 'integer'],  // déduit du schéma
            'display_order' => ['nullable', 'integer'],  // déduit du schéma
            'created_by' => ['nullable', 'integer', 'exists:users,id'],  // déduit du schéma
            'updated_by' => ['nullable', 'integer', 'exists:users,id'],  // déduit du schéma
            'legacy_type' => ['nullable', 'string', 'max:50'],  // déduit du schéma
        ];
    }
}
