<?php

namespace App\Http\Requests\Api\V1\ReferenceList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ajout d'une valeur à une liste de référence — D01.
 *
 * Règles reprises de `Settings\ReferenceListController::addValue()` (relu le 2026-08-04).
 */
class StoreReferenceValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|string|max:190',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ];
    }
}
