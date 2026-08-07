<?php

namespace App\Http\Requests\Api\V1\ThesaurusScheme;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un schéma de thésaurus — D08.
 *
 * Règles reprises de `ThesaurusSchemeController::store()` (relu le 2026-08-04).
 * `uri` et `namespace_id` sont posés côté serveur (génération d'URI + namespace),
 * jamais acceptés du client.
 */
class StoreThesaurusSchemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => 'required|string|max:50|unique:thesaurus_schemes',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'required|string|max:10',
            'namespace_uri' => 'nullable|url|max:255',
        ];
    }
}
