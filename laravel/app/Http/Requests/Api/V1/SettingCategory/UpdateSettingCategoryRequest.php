<?php

namespace App\Http\Requests\Api\V1\SettingCategory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @generated par `php artisan make:api-resource-set` — domaine D01.
 *
 * CE FICHIER EST UN POINT DE DÉPART, PAS UN LIVRABLE.
 * Les règles ci-dessous sont déduites du schéma et des règles déjà présentes dans le
 * contrôleur Blade. Le schéma ne connaît ni les règles métier ni ce que la vue imposait
 * implicitement (risques R01 et R02) : relire le contrôleur ET ses vues avant de valider.
 *
 * Retirer ce bandeau une fois le fichier relu.
 *
 * ⚠️ Règles dynamiques à reconstituer : name, parent_id.
 */
class UpdateSettingCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO règle d'origine, à reconstituer : 'string|max:100|unique:setting_categories,name,\' . $id'
            'name' => ['sometimes', 'string', 'max:100'],  // ⚠️ déduit du schéma, INCOMPLET
            'description' => 'nullable|string',  // reprise du contrôleur Blade
            // TODO règle d'origine, à reconstituer : 'nullable|exists:setting_categories,id|not_in:\' . $id'
            'parent_id' => ['sometimes', 'integer'],  // ⚠️ déduit du schéma, INCOMPLET
        ];
    }
}
