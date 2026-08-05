<?php

namespace App\Http\Requests\Api\V1\MetadataDefinition;

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
 *
 * ⚠️ Règles dynamiques à reconstituer : code.
 */
class UpdateMetadataDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',  // reprise du contrôleur Blade
            // TODO règle d'origine, à reconstituer : 'required|string|max:50|unique:metadata_definitions,code,\' . $metadataDefinition->id'
            'code' => ['sometimes', 'string', 'max:50'],  // ⚠️ déduit du schéma, INCOMPLET
            'description' => 'nullable|string',  // reprise du contrôleur Blade
            'data_type' => 'required|in:text,textarea,number,date,datetime,boolean,select,multi_select,reference_list,email,url',  // reprise du contrôleur Blade
            'validation_rules' => 'nullable|json',  // reprise du contrôleur Blade
            'options' => 'nullable|json',  // reprise du contrôleur Blade
            'reference_list_id' => 'nullable|exists:reference_lists,id',  // reprise du contrôleur Blade
            'searchable' => 'boolean',  // reprise du contrôleur Blade
            'active' => 'boolean',  // reprise du contrôleur Blade
            'is_system' => ['sometimes', 'integer'],  // déduit du schéma
            'sort_order' => 'nullable|integer',  // reprise du contrôleur Blade
            'created_by' => ['sometimes', 'integer'],  // déduit du schéma
            'updated_by' => ['sometimes', 'integer'],  // déduit du schéma
        ];
    }
}
