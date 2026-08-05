<?php

namespace App\Http\Requests\Api\V1\ReferenceList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une liste de référence — D01.
 *
 * Règles reprises de `Settings\ReferenceListController::update()` (relu le 2026-08-04),
 * avec l'unicité qui s'exclut de la liste elle-même. `updated_by` est posé depuis
 * l'agent authentifié, jamais accepté du client.
 */
class UpdateReferenceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('reference_lists', 'name')->ignore($this->route('reference_list')),
            ],
            'code' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('reference_lists', 'code')->ignore($this->route('reference_list')),
            ],
            'description' => 'nullable|string',
            'active' => 'boolean',
        ];
    }
}
