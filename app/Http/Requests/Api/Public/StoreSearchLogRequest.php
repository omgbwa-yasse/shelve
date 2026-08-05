<?php

namespace App\Http\Requests\Api\Public;

use Illuminate\Foundation\Http\FormRequest;

/**
 * D15 — enregistrement d'une recherche du portail. `user_id` est déduit du
 * token. `results_count` est nullable à la saisie mais forcé à 0 côté serveur
 * (colonne NOT NULL en base).
 */
class StoreSearchLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search_term' => ['required', 'string', 'max:191'],
            'filters' => ['nullable', 'array'],
            'results_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
