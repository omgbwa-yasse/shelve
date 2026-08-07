<?php

namespace App\Http\Requests\Api\V1\RecordSupport;

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
class UpdateRecordSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:record_supports|max:100',  // reprise du contrôleur Blade
            'description' => 'nullable',  // reprise du contrôleur Blade
        ];
    }
}
