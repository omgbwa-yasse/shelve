<?php

namespace App\Http\Requests\Api\V1\ReferenceList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une valeur de liste de référence — D01.
 *
 * Règles reprises de `Settings\ReferenceListController::updateValue()` (relu le 2026-08-04).
 */
class UpdateReferenceValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'sometimes|required|string|max:190',
            'code' => 'sometimes|required|string|max:50',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ];
    }
}
