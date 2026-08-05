<?php

namespace App\Http\Requests\Api\V1\ThesaurusConcept;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un concept de thésaurus — D08.
 *
 * Le CRUD concept n'existe qu'en ébauche côté Blade (`ThesaurusController::update`
 * et `destroy` sont vides) : règles reprises du modèle ThesaurusConcept et des
 * scopes SKOS (scheme_id requis, statut optionnel).
 */
class StoreThesaurusConceptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheme_id' => 'required|integer|exists:thesaurus_schemes,id',
            'uri' => 'nullable|string|max:191',
            'notation' => 'nullable|string|max:100',
            'status' => 'nullable|integer',
        ];
    }
}
