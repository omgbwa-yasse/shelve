<?php

namespace App\Http\Requests\Api\V1\Law;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une loi — D01.
 *
 * Module jamais branché côté Blade (contrôleur vide, aucune vue) : pas de
 * comportement d'origine à reprendre. Règles dérivées du schéma, complétées le
 * 2026-08-04 (contrainte `exists` sur `law_type_id`).
 */
class UpdateLawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:10'],
            'name' => ['sometimes', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string'],
            'publish_date' => ['sometimes', 'date'],
            'law_type_id' => ['sometimes', 'integer', 'exists:law_types,id'],
        ];
    }
}
