<?php

namespace App\Http\Requests\Api\V1\Organisation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une organisation — D09.
 *
 * Règles reprises de `OrganisationController::store()` (relu le 2026-08-05).
 * L'organisation est un référentiel global : pas de champ géré serveur ici.
 */
class StoreOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', 'unique:organisations,code'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:organisations,id'],
        ];
    }
}
