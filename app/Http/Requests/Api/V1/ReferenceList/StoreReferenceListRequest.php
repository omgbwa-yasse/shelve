<?php

namespace App\Http\Requests\Api\V1\ReferenceList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une liste de référence — D01.
 *
 * Règles reprises de `Settings\ReferenceListController::store()` (relu le 2026-08-04).
 * `created_by` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class StoreReferenceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:reference_lists,name',
            'code' => 'required|string|max:50|unique:reference_lists,code',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ];
    }
}
