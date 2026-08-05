<?php

namespace App\Http\Requests\Api\V1\Sort;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un sort final — D01.
 *
 * Règles reconstituées depuis `SortController::update()` (relu le 2026-08-04).
 * `in:E,T,C` (Éliminable / Transférable / Conservation) était présent dans le
 * contrôleur Blade mais absent du fallback généré : le schéma ne le voit pas, c'est
 * une contrainte purement métier.
 */
class UpdateSortRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes', 'required', 'in:E,T,C',
                Rule::unique('sorts', 'code')->ignore($this->route('sort')),
            ],
            'name' => ['sometimes', 'required', 'max:45'],
            'description' => ['nullable', 'max:100'],
        ];
    }
}
