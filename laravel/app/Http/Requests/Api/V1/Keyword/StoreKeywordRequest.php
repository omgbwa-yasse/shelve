<?php

namespace App\Http\Requests\Api\V1\Keyword;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un mot-clé — D01.
 *
 * Règles reprises de `KeywordController::store()` (relu le 2026-08-04).
 */
class StoreKeywordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    /**
     * Le contrôleur Blade insérait `trim($request->name)` : cette normalisation fait
     * partie du comportement, pas de la présentation. Elle est appliquée AVANT la
     * validation, sans quoi le contrôle d'unicité porterait sur une valeur différente
     * de celle réellement insérée — « mot » et « mot » passeraient tous deux.
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
            'name' => 'required|string|max:100|unique:keywords,name',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
