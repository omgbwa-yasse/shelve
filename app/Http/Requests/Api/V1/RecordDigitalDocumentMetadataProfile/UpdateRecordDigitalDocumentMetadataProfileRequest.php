<?php

namespace App\Http\Requests\Api\V1\RecordDigitalDocumentMetadataProfile;

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
class UpdateRecordDigitalDocumentMetadataProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type_id' => ['sometimes', 'integer'],  // déduit du schéma
            'metadata_definition_id' => ['sometimes', 'integer'],  // déduit du schéma
            'mandatory' => ['sometimes', 'integer'],  // déduit du schéma
            'visible' => ['sometimes', 'integer'],  // déduit du schéma
            'readonly' => ['sometimes', 'integer'],  // déduit du schéma
            'default_value' => ['sometimes', 'string'],  // déduit du schéma
            'validation_rules' => ['sometimes', 'array'],  // déduit du schéma
            'sort_order' => ['sometimes', 'integer'],  // déduit du schéma
            'created_by' => ['sometimes', 'integer'],  // déduit du schéma
            'updated_by' => ['sometimes', 'integer'],  // déduit du schéma
        ];
    }
}
