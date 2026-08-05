<?php

namespace App\Http\Requests\Api\V1\Keyword;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un mot-clé — D01.
 *
 * Règles reprises de `KeywordController::update()` (relu le 2026-08-04).
 *
 * `name` est en `max:100` alors que la colonne autorise `varchar(250)` : c'est une
 * règle métier volontaire, appliquée à l'identique en création et en modification
 * dans le code Blade — pas une erreur, donc pas alignée sur la borne du schéma.
 */
class UpdateKeywordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Même normalisation qu'à la création : le contrôleur Blade insère
     * `trim($request->name)`, ce qui doit se produire avant le contrôle d'unicité.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => trim((string) $this->input('name'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('keywords', 'name')->ignore($this->route('keyword')),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
