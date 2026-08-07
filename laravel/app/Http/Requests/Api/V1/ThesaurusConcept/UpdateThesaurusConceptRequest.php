<?php

namespace App\Http\Requests\Api\V1\ThesaurusConcept;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un concept de thésaurus — D08.
 */
class UpdateThesaurusConceptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheme_id' => 'sometimes|required|integer|exists:thesaurus_schemes,id',
            'uri' => 'nullable|string|max:191',
            'notation' => 'nullable|string|max:100',
            'status' => 'nullable|integer',
        ];
    }
}
