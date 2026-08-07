<?php

namespace App\Http\Requests\Api\V1\ThesaurusScheme;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un schéma de thésaurus — D08.
 *
 * Règles reprises de `ThesaurusSchemeController::update()` (relu le 2026-08-04),
 * en `sometimes` avec unicité ignorée sur la ressource courante.
 */
class UpdateThesaurusSchemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('thesaurus_schemes', 'identifier')->ignore($this->route('thesaurus_scheme')),
            ],
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'language' => 'sometimes|required|string|max:10',
            'uri' => [
                'sometimes',
                'required',
                'url',
                'max:255',
                Rule::unique('thesaurus_schemes', 'uri')->ignore($this->route('thesaurus_scheme')),
            ],
            'namespace_uri' => 'nullable|url|max:255',
        ];
    }
}
